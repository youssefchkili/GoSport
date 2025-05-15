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
                // Email already exists
                $form->get('email')->addError(new FormError('There is already an account with this email'));
                
                // Add this return statement to show the form with errors
                return $this->render('registration/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
            
            try {
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

                // Create address using form data
                $address = new Adress();
                $address->setStreet($form->get('street')->getData() ?: 'Please update your address');
                $address->setCity($form->get('city')->getData() ?: 'Default City');
                $address->setState($form->get('state')->getData() ?: 'Default State');
                $address->setCountry($form->get('country')->getData() ?: 'Default Country');
                $address->setPostalCode($form->get('postalCode')->getData() ?: '00000');
                $address->setIsDefault(true);
                $address->setCreatedAt(new \DateTimeImmutable());
                $address->setUpdatedAt(new \DateTimeImmutable());

                // Persist entities
                $entityManager->persist($address);
                $user->setAdress($address);
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Account created successfully!');
                
                // Authenticate user
                return $userAuthenticator->authenticateUser(
                    $user,
                    $loginAuthenticator,
                    $request
                );
            } catch (\Exception $e) {
                // Log the error
                $this->addFlash('error', 'An error occurred during registration: ' . $e->getMessage());
                
                return $this->render('registration/index.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}