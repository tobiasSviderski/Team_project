<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Profile;
use App\Entity\Subscription;
use App\Form\ProfileEditType;
use App\Form\ProfileNewType;
use App\Form\ProfileUploadType;
use App\Repository\ProfileRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Security\FileNotExistsException;
use App\Security\FileNotMoveableException;
use App\Service\ProfileCreateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/", name="profile_index", methods={"GET"})
     */
    public function index(ProfileRepository $profileRepository): Response
    {
        return $this->render('profile/index.html.twig', [
            'profiles' => $profileRepository->findAll(),
        ]);
    }

    /**
     * @Route("/profile/new", name="profile_new", methods={"GET","POST"})
     */
    public function new_profile(
        Request $request,
        ProfileCreateService $service
    )
    {
        $profile = new Profile();
        $form = $this->createForm(ProfileNewType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $service->create(
                    $profile,
                    $form['file']->getData(),
                    $form['forAll']->getData(),
                    $form['subscriptions']->getData(),
                    $this->getUser()
                );
            }
            catch (FileNotExistsException $ex){
                $this->addFlash("error", $ex);
            } catch (FileNotMoveableException $ex) {
                $this->addFlash("error", $ex);
            } catch (\Exception $ex) {
                $this->addFlash("error", $ex);
            }

            $this->addFlash("success", "Profile created");

            return $this->redirectToRoute('profile_index');
        }

        // Display the form, if profile from last try exists sent it too
        return $this->render('profile/new.html.twig', [
            'profile' => $profile,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{id}", name="profile_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Profile $profile, SubscriptionRepository $subscriptionRepository): Response
    {
        $form = $this->createForm(ProfileUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($form->getData());
            die();


            if($form->getClickedButton() && 'attemptToSave' === $form->getClickedButton()->getName()) {
                // Get the file from the profile object
                $file = $profile->getFile();

                // Check if somebody else could alreay have dibs on the file
                if ($file->getAttemptToChangeBy() === null) {
                    $this->addFlash("Error", "Somebody else made their changes and presaved this file");
                } else {
                    $file->setAttemptToChangeBy($this->getUser());
                    $form->add('attemptToSave', SubmitType::class, [
                        'label' => 'Attempt to upload a file',
                        'disabled' => true
                    ]);
                    $form->add('save', SubmitType::class, [
                        'label' => 'Attempt to upload a file'
                    ]);
                }

            }

            if($form->getClickedButton() && 'save' === $form->getClickedButton()->getName()) {

            }

//            $profileFile = $form['file']->getData();
//
//            // If file exists
//            if ($profileFile) {
//
//                // Get file name and titile if any
//                $originalFileName = pathinfo($profileFile->getClientOriginalName(), PATHINFO_FILENAME);
//                $inputedTitle = $form->get('title')->getData();
//
//                // Moving the file
//                try {
//                    // Unlink the old file
//                    unlink($this->getParameter('profile_directory') . $profile->getFile());
//
//                    // Move the new one
//                    $profileFile->move(
//                        $this->getParameter('profile_directory'),
//                        $profile->getFile()
//                    );
//                } catch (FileException $e) {
//                    #TODO handle file excption
//                }
//            }
        }

        return $this->render('profile/show.html.twig', [
            'profile' => $profile,
            'subscriptions' => $subscriptionRepository->findBy(['profile' => $profile]),
            'upload' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{id}/edit", name="profile_edit", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        Profile $profile,
        UserRepository $userRepository,
        SubscriptionRepository $subscriptionRepository
    ): Response
    {
        $form = $this->createForm(ProfileEditType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save into database
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($profile);
                // Reset subscriber list
                $editedSubscribers =  $request->request->get('subscribers');
                if (empty($editedSubscribers)){
                    $profile->setForAll(true);
                    $profile->eraseSubscription();
                }
                else {
                    $profile->eraseSubscription();
                    foreach ($editedSubscribers as $newSub){
                        $sub = new Subscription();
                        $sub->setProfile($profile);
                        $sub->setUser($userRepository->find($newSub));
                        $entityManager->persist($sub);
                    }
                }

                $log = new Log();
                $log->setProfile($profile);
                $log->setAuthor($this->getUser());
                $log->setType(Log::TYPE_EDIT);
                $log->setCreated(new \DateTime());
                $entityManager->persist($log);
                $this->addFlash("success", "Profile information were changed");

                $entityManager->persist($profile);
                $entityManager->flush();
                return $this->redirectToRoute('profile_show', ['id' => $profile->getId()]);
            }

            return $this->render('profile/edit.html.twig', [
                'profile' => $profile,
                'users' => $userRepository->findAll(),
                'subscriptions' => $subscriptionRepository->findBy(['profile' => $profile]),
                'form' => $form->createView(),
            ]);
        }


    /**
     * @Route("/profile/{id}/download", name="profile_download")
     */
    public function download(Profile $profile){
        $title = $profile->getTitle();
        $file = $profile->getFile();

        $log = new Log();
        $log->setProfile($profile);
        $log->setAuthor($this->getUser());
        $log->setCreated(new \DateTime());
        $log->setType(Log::TYPE_DOWNLOAD);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($log);
        $entityManager->flush();

        return $this->file( $this->getParameter('profile_directory') . $file->getFilename()  , $title . '.' . $file->getExtention());
        //return $this->file($pdfPath, 'sample-of-my-book.pdf');
    }

    /**
     * @Route("/profile/{id}", name="profile_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Profile $profile): Response
    {
        if ($this->isCsrfTokenValid('delete'.$profile->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            // Removing action
            try {
                // Remove the file in the folder system
                $file = $profile->getFile();
                unlink($this->getParameter('profile_directory') . $file->getFilename());

                // Remove subscription objects
                foreach ($profile->getSubscriptions() as $subscription)
                {
                    $entityManager->remove($subscription);
                }

                // Remove log objects
                foreach($profile->getLogs() as $log){
                    $entityManager->remove($log);
                }

                // Remove file object
                $entityManager->remove($profile->getFile());

                // Remove the main profile
                $entityManager->remove($profile);

                // Finish everyting
                $entityManager->flush();
                $this->addFlash("success", "Profile was successfully removed");
            } catch (\Exception $e)
            {
                $this->addFlash("alert", $e);
            } finally {
                return $this->redirectToRoute('profile_index');
            }
        }
    }
}
