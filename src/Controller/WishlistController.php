<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Entity\WishList;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'app_wishlist')]
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


