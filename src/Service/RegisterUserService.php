<?php


namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterUserService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string $plainPassword
     * @param User $user
     */
    public function create($plainPassword, $user){
        try{
            $user->setPassword(
                $this->passwordEncoder->encodePassword($user, $plainPassword)
            );
            $user->setCreated(new \DateTime());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e)
        {
            return false;
        }
    }
}