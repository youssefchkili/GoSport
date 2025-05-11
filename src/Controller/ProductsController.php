<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function RawProducts(ManagerRegistry $manager): Response
    {   
        $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');
        $categoryRepository = $doctrine->getRepository('App\Entity\Category');
        $products = $productRepository->findAll();
        $priceRange = $productRepository->getPriceRange();
        $categories = $categoryRepository->findAll();
        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $products,
            'categories' => $categories,
            'minPrice' => $priceRange[0]['minPrice'],
            'maxPrice' => $priceRange[0]['maxPrice'],
        ]);
    }

    #[Route('/products/byKeyWord', name: 'app_products_byKeyWord')]
    public function ProductsByKeyWord(ManagerRegistry $manager, Request $request): Response
    {   
        $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');
        $categoryRepository = $doctrine->getRepository('App\Entity\Category');
        $products = $productRepository->findByKeyWord($request->get('keyWord'));
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
