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
    {   $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');
        $products = $productRepository->findAll();
        return $this->render('wishlist/index.html.twig', [
            'controller_name' => 'WishlistController',
            'products' => $products,
        ]);
        
    }
    #[Route('/wishlist/add/{id}', name: 'app_wishlist_add')]
    public function addToWishlist(Product $product, ManagerRegistry $manager,SessionInterface $session): JsonResponse
    {   
        $doctrine = $manager->getManager();
        $user = $this->getUser();
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
            return new JsonResponse(['success' => true, 'message' => $message]);
        }
    }
    #[Route('/wishlist/check/{id}/{quantity}', name: 'app_wishlist_check')]
    public function checkToWishlist(Product $product, $quantity , ManagerRegistry $manager,SessionInterface $session): JsonResponse
    {   
        return new JsonResponse(['success' => true, 'message' => $quantity . ' ' . $product->getName() . ' checked to wishlist']);
    }
}
