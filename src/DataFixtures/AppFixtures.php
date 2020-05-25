<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    /**
     * @var PasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setPassword(
            $this->passwordEncoder->encodePassword($admin, '1234')
        );
        $admin->setCreated(new \DateTime());
        $admin->setRoles([User::ROLE_ADMIN]);
        $manager->persist($admin);

        $user = new User();
        $user->setUsername('user');
        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, '1234')
        );
        $user->setCreated(new \DateTime());
        $user->setRoles([User::ROLE_ADMIN]);
        $manager->persist($user);

        $manager->flush();
    }
}
