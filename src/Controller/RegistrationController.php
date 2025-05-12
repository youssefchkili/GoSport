<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Adress;
use App\Form\RegistrationForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginAuthenticator;
use Symfony\Component\Uid\Uuid;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserAuthenticatorInterface $userAuthenticator,
        LoginAuthenticator $loginAuthenticator,
        UserRepository $userRepository,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $existingUser = $userRepository->findOneBy(['email' => $form->get('email')->getData()]);


            if ($existingUser) {
                //email already exists
                $form->get('email')->addError(new FormError('There is already an account with this email'));
            }else{
                $user->setUuid(Uuid::v4()->toRfc4122());
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );

                // Set default role
                $user->setRoles(['ROLE_USER']);

                // Set other required fields
                $user->setIsActive(true);
                $user->setCreatedAt(new \DateTimeImmutable());

                // Create a default address for the user
                $address = new Adress();
                $address->setStreet('Please update your address');
                $address->setCity('Default City');
                $address->setState('Default State');
                $address->setCountry('Default Country');
                $address->setPostalCode('00000');
                $address->setIsDefault(true);
                $address->setCreatedAt(new \DateTimeImmutable());
                $address->setUpdatedAt(new \DateTimeImmutable());

                // Persist the address first
                $entityManager->persist($address);

                // Now set the address on the user
                $user->setAdress($address);

                // Save the user
                $entityManager->persist($user);
                $entityManager->flush();

                // Authenticate the user automatically after registration
                return $userAuthenticator->authenticateUser(
                    $user,
                    $loginAuthenticator,
                    $request
                );
            }



        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}