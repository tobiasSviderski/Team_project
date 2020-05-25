<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserEditType;
use App\Form\UserNewType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
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
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET", "POST"})
     */
    public function show(User $user, Request $request,
    UserPasswordEncoderInterface $passwordEncoder
): Response
    {
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
                if ($result === true) {
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
            'passwordform' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
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
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('default');
    }
}
