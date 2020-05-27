<?php

namespace App\Controller;

use App\Entity\Log;
use App\Entity\Profile;
use App\Entity\Subscription;
use App\Entity\User;
use App\Form\ProfileEditType;
use App\Form\ProfileNewType;
use App\Form\ProfileUploadType;
use App\Repository\ProfileRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use App\Security\FileNotExistsException;
use App\Security\FileNotMoveableException;
use App\Service\ProfileCreateService;
use Exception;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/", name="profile_index", methods={"GET"})
     * @param ProfileRepository $profileRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @return Response
     */
    public function index(ProfileRepository $profileRepository, SubscriptionRepository $subscriptionRepository): Response
    {

       $profiles = array();

        if (in_array(User::ROLE_USER, $this->getUser()->getRoles())) {
            // Get all profiles regardless
            $publicProfiles = $profileRepository->findBy(['forAll' => true]);
            $allSubscription = $subscriptionRepository->findBy(['user' => $this->getUser()]);
//            dump($allSubscription);
//            dump('-----');
//            dump($publicProfiles);
//            die();
            // Get the ones that user is subscribed to
            $subscribedProfiles = [];
            foreach ($allSubscription as $oneSub) {
                $profile = $oneSub->getProfile();
                if (!in_array($profile, $subscribedProfiles))
                    $subscribedProfiles[] = $oneSub->getProfile();
            }

            $profiles = array_merge(
                $publicProfiles, $subscribedProfiles
            );
        }

        if (in_array(User::ROLE_ADMIN, $this->getUser()->getRoles())) {
            $profiles = $profileRepository->findAll();
        }

//        dump($profiles);
//        die();

        return $this->render('profile/index.html.twig', [
            'profiles' => $profiles
        ]);
    }


    /**
     * @Route("/profile/new", name="profile_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param ProfileCreateService $service
     * @return RedirectResponse|Response
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
            } catch (Exception $ex) {
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
     * @param Request $request
     * @param Profile $profile
     * @param SubscriptionRepository $subscriptionRepository
     * @return Response
     */
    public function show(Request $request, Profile $profile, SubscriptionRepository $subscriptionRepository): Response
    {
        if (in_array(User::ROLE_USER, $this->getUser()->getRoles()))
        {
            // If its a user
            // profile has to be for all
            // profile has to be subscribed to the user
            if (!$profile->isforAll()) {
                $subscription = $subscriptionRepository->findSubscribeProfile($this->getUser(), $profile);
                if (!$subscription) {
                    $this->addFlash('error', 'You do not have access to see this profile');
                    return $this->redirectToRoute('profile_index');
                }
            }
        }

        $form = $this->createForm(ProfileUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Get the file from the profile object
            $file = $profile->getFile();

            if($form->getClickedButton() && 'attemptToSave' === $form->getClickedButton()->getName()) {
                // Check if somebody else could already have dibs on the file
                if (!is_null($file->getAttemptToChangeBy())) {
                    $this->addFlash("error", "Somebody else made their changes and pre-saved this file");
                } else {
                    $file->setAttemptToChangeBy($this->getUser());
                    $this->getDoctrine()->getManager()->persist($file);
                    $this->getDoctrine()->getManager()->flush();
                    $this->addFlash("success", "First part went successfully");
                }
            }
            if($form->getClickedButton() && 'save' === $form->getClickedButton()->getName()) {
                if (is_null($file->getAttemptToChangeBy()))
                    $this->addFlash("error", "Nobody yet to attempt to upload for this profile.");
                else {
                    if ($this->getUser() !== $file->getAttemptToChangeBy())
                        $this->addFlash("error", "This profile is being updated by " . $file->getAttemptToChangeBy()->getUsername() . ' .');
                    else {
                        // Replace

                        // Get the new profile from the user
                        /** @var UploadedFile $profileFile */
                        $profileFile = $form['file']->getData();

                        // Does file exists if not do nothing, display error
                        if (!$profileFile){
                            $this->addFlash("error", "No file was found to upload.");
                        }
                        else {
                            // Unlink the old profile
                            unlink($this->getParameter('profile_directory') . $file->getFilename());

                            try {
                                $profileFile->move(
                                    $this->getParameter('profile_directory'),
                                    $file->getFilename()
                                );
                                $file->setVersion(Uuid::uuid4());
                                $file->setAttemptToChangeBy(null);
                                $file->setExtention($profileFile->getClientOriginalExtension());
                                $this->getDoctrine()->getManager()->persist($file);
                                $this->getDoctrine()->getManager()->flush();

                                // Return success message
                                $this->addFlash('success', 'File was uploaded');
                            }
                            catch (Exception $e)
                            {
                                $this->addFlash('error', $e);
                            }
                        }
                    }
                }
            }
        }

        return $this->render('profile/show.html.twig', [
            'profile' => $profile,
            'subscriptions' => $subscriptionRepository->findBy(['profile' => $profile]),
            'upload' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/{id}/edit", name="profile_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Profile $profile
     * @param UserRepository $userRepository
     * @param SubscriptionRepository $subscriptionRepository
     * @return Response
     * @throws Exception
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
                'users' => $userRepository->findBy(['enabled' => true]),
                'subscriptions' => $subscriptionRepository->findBy(['profile' => $profile]),
                'form' => $form->createView(),
            ]);
        }


    /**
     * @Route("/profile/{id}/download", name="profile_download")
     * @param Profile $profile
     * @param SubscriptionRepository $subscriptionRepository
     * @return BinaryFileResponse|RedirectResponse
     * @throws Exception
     */
    public function download(Profile $profile, SubscriptionRepository $subscriptionRepository)
    {
        if (in_array(User::ROLE_USER, $this->getUser()->getRoles())) {
            // If its a user
            // profile has to be for all
            // profile has to be subscribed to the user
            if (!$profile->isforAll()) {
                $subscription = $subscriptionRepository->findSubscribeProfile($this->getUser(), $profile);
                if (!$subscription) {
                    $this->addFlash('error', 'You do not have access to see this profile');
                    return $this->redirectToRoute('profile_index');
                }
            }
        }

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
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Profile $profile
     * @return Response
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

                // Finish everything
                $entityManager->flush();
                $this->addFlash("success", "Profile was successfully removed");
            } catch (Exception $e)
            {
                $this->addFlash("error", $e);
            }
        }
        return $this->redirectToRoute('profile_index');
    }
}
