<?php


namespace App\Controller;


use App\Entity\User;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index(){
        $role = $this->getUser()->getRoles();
        if (in_array(User::ROLE_ADMIN, $role))
            $this->redirectToRoute('admin_panel');
        else
            $this->redirectToRoute('user_panel');
    }

    /**
     * @Route("/profiles", name="user_panel")
     */
    public function user_default(
        ProfileRepository $profileRepository
    ){
        // TODO write find my profiles function
        return $this->render('user_panel/index.html.twig', [
            'profiles' => $profileRepository->findAll()
        ]);
    }
}