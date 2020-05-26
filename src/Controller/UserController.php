<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserEditType;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        if (in_array(User::ROLE_USER, $this->getUser()->getRoles())) {
            return $this->redirectToRoute('user_show', ['id' => $this->getUser()->getId()]);
        }
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->getAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET", "POST"})
     */
    public function show(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response
    {
        if (in_array(User::ROLE_USER, $this->getUser()->getRoles())) {
            if ($this->getUser() !== $user)
            {
                $this->addFlash('error', 'You do not have the permission to see other profiles');
                return $this->redirectToRoute('profile_index');
            }
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get password from request variable
            $passwords = $request->request->get('change_password');
            $old_password = $passwords['oldPassword'];
            $new_password = $passwords['newPassword'];

            // Validate the old password ( is it correct? )
            if ($passwordEncoder->isPasswordValid($user, $old_password))
            {
                // Change password
                $result = $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user, $new_password
                    )
                );

                // If the change was made
                if ($result) {
                   $entityManager = $this->getDoctrine()->getManager();
                   $entityManager->persist($user);
                   $entityManager->flush();
                   $this->addFlash('success', "Password was changed.");
                } else {
                    $this->addFlash('error', "The new password could not been set.");
                }
            } else {
                $this->addFlash('error', "The old password is not correct.");
            }


        }
        return $this->render('user/show.html.twig', [
            'user' => $user,
            'passwordform' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        if (in_array(User::ROLE_USER, $this->getUser()->getRoles())) {

            if ($this->getUser() !== $user)
            {
                $this->addFlash('error', 'You do not have the permission to see other profiles');
                return $this->redirectToRoute('profile_index');
            }
        }

        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user, ProfileRepository $profileRepository): Response
    {
        if (in_array(User::ROLE_USER, $this->getUser()->getRoles())) {
            if ($this->getUser() !== $user)
            {
                $this->addFlash('error', 'You do not have the permission to delete other profiles');
                return $this->redirectToRoute('profile_index');
            }
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();

            // Removing action
            try {
                foreach ($user->getSubscriptions() as $subscription)
                    $entityManager->remove($subscription);

                $user->setEnabled(false);
                $user->setUsername(Uuid::uuid4());

                // Remove the user
                $entityManager->persist($user);

                // Flush data
                $entityManager->flush();
                $this->addFlash("success", "Profile was successfully removed");

            }
            catch (\Exception $e)
            {
                $this->addFlash("error", $e);
            } finally {
                return $this->redirectToRoute('user_index');
            }
        }

        return $this->redirectToRoute('default');
    }
}
