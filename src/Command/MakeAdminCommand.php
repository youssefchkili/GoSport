<?php

namespace App\Command;

use App\Entity\Adress;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:make-admin',
    description: 'Add a short description for your command',
)]
class MakeAdminCommand extends Command
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;
    
    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user->setEmail('admin@gmail.com');
        $user->setFirstName('Admin');
        $user->setLastName('User');
        $user->setPhone('0000000000');
        $user->setUuid(Uuid::v4()->toRfc4122());
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                'password'
            )
        );

        $user->setRoles(['ROLE_ADMIN']);

        $user->setIsActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());

        $address = new Adress();
        $address->setStreet('Please update your address');
        $address->setCity('Default City');
        $address->setState('Default State');
        $address->setCountry('Default Country');
        $address->setPostalCode('00000');
        $address->setIsDefault(true);
        $address->setCreatedAt(new \DateTimeImmutable());
        $address->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($address);
        $user->setAdress($address);
        $this->em->persist($user);
        $this->em->flush();
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('Admin user created.');

        return Command::SUCCESS;
    }
}
