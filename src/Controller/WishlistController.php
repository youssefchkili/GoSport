<?php

namespace App\Controller;

use App\Entity\Product;
<<<<<<< HEAD
use App\Entity\WishList;
use App\Service\MailerService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
=======
use App\Entity\User;
use App\Entity\WishList;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
>>>>>>> 9f8c9a82076a240ba93e5e16e0f72038325ab273
use Symfony\Component\Routing\Attribute\Route;

final class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'app_wishlist')]
<<<<<<< HEAD
    public function wishlist(): Response
    {
        $user = $this->getUser();
        $products = [];

        // Check if user exists and has a wishlist
        if ($user && $user->getWishList()) {
            $products = $user->getWishList()->getProducts();
        }

        return $this->render('wishlist/index.html.twig', [
            'controller_name' => 'WishlistController',
            'products' => $products,
        ]);
        return $this->render('wishlist/index.html.twig', [
            'controller_name' => 'WishlistController',
            'products' => $this->getUser()->getWishList()->getProducts(),
        ]);

    }
    #[Route('/wishlist/toggle/{id}', name: 'app_wishlist_add')]
    public function addToWishlist(Product $product, ManagerRegistry $manager, MailerService $mailer): JsonResponse
    {   
        $doctrine = $manager->getManager();
        $user = $this->getUser();
        $message = '';
        if ($user){
            $wishlist = $user->getWishList();
            if (!$wishlist) {
                $wishlist = new WishList();
                $wishlist->setUserId($user);
                $doctrine->persist($wishlist);
            }
            if ($wishlist->getProducts()->contains($product)) {
                $wishlist->removeProduct($product);
                $message = 'Product removed from wishlist';
            } else {
                $wishlist->addProduct($product);
                $message = 'Product added to wishlist';
            }
            $doctrine->persist($wishlist);
            $doctrine->flush();
            // Send email notification just for testing mailer service, should not sent notification
            $mailer->sendEmail(
                $user->getEmail(),
                'Wishlist Update',
                "" . $message . " for product: " . $product->getName()
            );
        }
        return new JsonResponse(['success' => true, 'message' => $message]);
    }
}
=======
    public function wishlist(ManagerRegistry $manager): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté

        if (!$user) {
            // Si l'utilisateur n'est pas connecté, redirigez ou affichez une erreur
            return $this->redirectToRoute('app_login'); // Redirige vers la page de connexion
        }

        // Récupérer ou créer une liste de souhaits pour l'utilisateur
        $wishlist = $user->getWishList();

        if (!$wishlist) {
            // Si aucune wishlist n'existe, en créer une
            $wishlist = new WishList();
            $wishlist->setUserId($user);

            $manager->getManager()->persist($wishlist);
            $manager->getManager()->flush();
        }

        return $this->render('wishlist/index.html.twig', [
            'controller_name' => 'WishlistController',
            'products' => $wishlist->getProducts(), // On transmet les produits de la wishlist
        ]);
    }

    #[Route('/wishlist/toggle/{id}', name: 'app_wishlist_add')]
    public function toggleWishlist(Product $product, ManagerRegistry $manager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            // Si l'utilisateur n'est pas connecté, on retourne une erreur
            return new JsonResponse([
                'success' => false,
                'message' => 'User not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $doctrine = $manager->getManager();
        $wishlist = $user->getWishList();

        // Si aucune wishlist n'existe pour l'utilisateur, la créer
        if (!$wishlist) {
            $wishlist = new WishList();
            $wishlist->setUserId($user);
            $doctrine->persist($wishlist);
        }

        if ($wishlist->getProducts()->contains($product)) {
            // Si le produit existe déjà dans la wishlist, le supprimer
            $wishlist->removeProduct($product);
            $message = 'Product removed from wishlist';
        } else {
            // Sinon, ajouter le produit à la wishlist
            $wishlist->addProduct($product);
            $message = 'Product added to wishlist';
        }

        $doctrine->persist($wishlist);
        $doctrine->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $message,
        ]);
    }
}


>>>>>>> 9f8c9a82076a240ba93e5e16e0f72038325ab273
