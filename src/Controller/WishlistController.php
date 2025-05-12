<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
}
