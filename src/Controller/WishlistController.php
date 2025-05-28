<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\WishList;
use App\Service\MailerService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WishlistController extends AbstractController
{
    #[Route('/wishlist', name: 'app_wishlist')]
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