<?php


namespace App\Service;


use App\Entity\File;
use App\Entity\Log;
use App\Entity\Profile;
use App\Entity\Subscription;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\FileNotExistsException;
use App\Security\FileNotMoveableException;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfileCreateService
{

    /**
     * @var SluggerInterface
     */
    private $slugger;
    /**
     * @var ParameterBagInterface
     */
    private $bag;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(
        SluggerInterface $slugger,
        ParameterBagInterface $bag,
        EntityManagerInterface $entityManager,
        UserRepository $repository
)
    {

        $this->slugger = $slugger;
        $this->bag = $bag;
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     * @param Profile $profile
     * @param UploadedFile $uploadedFile
     * @param bool $forAll
     * @param $subscriptions
     * @param UserInterface $user
     */
    public function create(
        Profile $profile,
        UploadedFile $uploadedFile,
        bool $forAll,
        $subscriptions,
        UserInterface $user)
    {
        // If the file exists
        if($uploadedFile)
        {
            $filename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $extention = $uploadedFile->guessClientExtension();

            // Title
            $title = "";
            if(!empty($profile->getTitle())){
                $title = $profile->getTitle();
            } else {
                $title = $this->slugger->slug($filename);
            }

            $file = new File();
            $file->setFilename(Uuid::uuid4());
            $file->setExtention($extention);
            $file->setVersion(Uuid::uuid4());

            // Moving the file
            try {
                $uploadedFile->move(
                    $this->bag->get('profile_directory'),
                    $file->getFilename()
                );
            }
            catch (FileException $e) {
                throw new FileNotMoveableException();
            }

            // Save the file object
            $this->entityManager->persist($file);

            // Creating a profile
            $profile->setFile($file);
            $profile->setCreated(new \DateTime());
            $profile->setAuthor($user);

            // Save the profile object
            $this->entityManager->persist($profile);

            if (!$forAll) {
                // Subscriptions
                foreach ($subscriptions as $subscriptionID) {
                    $subs = new Subscription();
                    $subs->setProfile($profile);
                    $subs->setUser($this->repository->find($subscriptionID));

                    // Pre save the subscription
                    $this->entityManager->persist($subs);
                }
            }

            // Creating log
            /** @var Log $log */
            $log = new Log();
            $log->setProfile($profile);
            $log->setAuthor($user);
            $log->setCreated(new \DateTime());

            // Pre save the log
            $this->entityManager->persist($log);

            // Save pre savings and continue
            $this->entityManager->flush();
        } else {
            throw new FileNotExistsException();
        }
    }
}