<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\RegisterUserService;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccessRestrictionController extends AbstractController
{
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser())
            return $this->redirectToRoute('profile_index');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @param Request $request
     * @param RegisterUserService $service
     * @Route("/register", name="app_register", methods={"GET", "POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function register(Request $request, RegisterUserService $service){
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Call the service and create a user
                if ($service->create($form['plainPassword']->getData(), $user))
                    $this->addFlash('success', 'Account was created.');
                else {
                    $this->addFlash('alert', 'The account was not created.');
                }
            } catch (Exception $ex){
                $this->addFlash('alert', 'The account was not created.');
            } finally {
                return $this->redirectToRoute('user_index');
            }
        }


        return $this->render('register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}