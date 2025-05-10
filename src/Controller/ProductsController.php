<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function rawProducts(ManagerRegistry $manager): Response
    {   
        $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');
        $categoryRepository = $doctrine->getRepository('App\Entity\Category');
        $products = $productRepository->findAll();
        $categories = $categoryRepository->findAll();
        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $products,
            'categories' => $categories,
            'minPrice' => 21,
            'maxPrice' => 896,
        ]);
    }
}
