<?php

namespace App\Controller;

use App\Entity\Profile;
use App\Form\ProfileEditType;
use App\Form\ProfileNewType;
use App\Form\ProfileUploadType;
use App\Form\ProfileCreateType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use App\Security\FileNotExistsException;
use App\Security\FileNotMoveableException;
use App\Service\ProfileCreateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
    public function show(Request $request, Profile $profile): Response
    {
        $form = $this->createForm(ProfileUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $profileFile = $form['file']->getData();

            // If file exists
            if ($profileFile) {
                // Get file name and titile if any
                $originalFileName = pathinfo($profileFile->getClientOriginalName(), PATHINFO_FILENAME);
                $inputedTitle = $form->get('title')->getData();

                // Moving the file
                try {
                    // Unlink the old file
                    unlink($this->getParameter('profile_directory') . $profile->getFile());

                    // Move the new one
                    $profileFile->move(
                        $this->getParameter('profile_directory'),
                        $profile->getFile()
                    );
                } catch (FileException $e) {
                    #TODO handle file excption
                }
            }
        }

        return $this->render('profile/show.html.twig', [
            'profile' => $profile,
            'upload' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/{id}/edit", name="profile_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Profile $profile, UserRepository $userRepository): Response
    {
        $form = $this->createForm(ProfileEditType::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            if ($profile->getForAll())
            {
                $profile->resetSubscriber();
            }else {
                // Reset subscriber list
                $editedSubscribers =  $request->request->get('subscribers');
                if (empty($editedSubscribers)){
                    $profile->setForAll(true);
                    $profile->resetSubscriber();
                } else {
                    $profile->resetSubscriber();
                    foreach ($editedSubscribers as $newSub){
                        $profile->addSubscriber($userRepository->find($newSub));
                    }
                }
            }

            // Save into database
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($profile);
            $entityManager->flush();

            return $this->redirectToRoute('profile_show', ['id' => $profile->getId()]);
        }

        return $this->render('profile/edit.html.twig', [
            'profile' => $profile,
            'users' => $userRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/profile/{id}/download", name="profile_download")
     */
    public function download(Profile $profile){
        $title = $profile->getTitle();
        $path = $profile->getFile();
        return $this->file( $this->getParameter('profile_directory') . $path, $title);
        //return $this->file($pdfPath, 'sample-of-my-book.pdf');
    }

    /**
     * @Route("/profile/{id}", name="profile_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Profile $profile): Response
    {
        if ($this->isCsrfTokenValid('delete'.$profile->getId(), $request->request->get('_token'))) {

            unlink($this->getParameter('profile_directory') . $profile->getFile());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($profile);
            $entityManager->flush();
        }

        return $this->redirectToRoute('profile_index');
    }
}
