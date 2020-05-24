<?php

namespace App\Controller;

use App\Repository\LogRepository;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminPanelController
 * @package App\Controller
 */
class AdminPanelController extends AbstractController
{
    /**
     * @Route("/", name="admin_panel")
     */
    public function index(
        ProfileRepository $profileRepository,
        UserRepository $userRepository,
        LogRepository $logRepository
    ){
        return $this->render('admin_panel/index.html.twig', [
            'profiles' => $profileRepository->findAll(),
            'users' => $userRepository->findAll(),
            'logs' => $logRepository->findAll()
        ]);
    }

    /**
     * @Route("/profiles", name="profiles")
     */
    public function profiles(ProfileRepository $repository)
    {
        return $this->render('profile/index.html.twig', [
            'profiles' => $repository->findAll(),
        ]);
//        <a href="{{ path('entity.user.canonical', {'user': user.id}) }}">{{ 'View user profile'|t }}</a>
////        {% if not is_granted('view', post) %}
////        Sorry you don't have permissions to access this!
////          {% endif %}
        // {{ app.user.username }}
    }

    /**
     * @Route("/users", name="admin_users")
     */
    public function users(UserRepository $repository)
    {
        return $this->render('user/index.html.twig', [
            'users' => $repository->findAll(),
        ]);
    }
}
