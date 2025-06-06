<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function RawProducts(ManagerRegistry $manager, Request $request): Response
    {   
        //dd($this->getUser()->getCart()->getCartItems()->get(0));

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
            'totalPages' => is_countable($products) ? ceil(count($products) / 20) : 0,
            'currentPage' => $request->get('page') == null ? 1 : $request->get('page'),
        ]);
    }

    #[Route('/products/byFilters', name: 'app_products_byFilters')]
    public function ProductsByFilters(ManagerRegistry $manager, Request $request): Response
    {   
        if ($request->get('min-price') == null) {
            return $this->ProductsByKeyWord($manager, $request);
        }
        $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');
        $categoryRepository = $doctrine->getRepository('App\Entity\Category');

        $categories = $categoryRepository->findAll();
        $categoriesAllowed = [];
        foreach ($categories as $category) {
            if ($request->get('min-price') == null || $request->get('categoryCheckbox'.$category->getId()) != null) {
                $categoriesAllowed[] = $category->getId();
            }
        }
        $products = $productRepository->findAll();
        if ($request->get('min-price') != null) {
            $products = $productRepository->findByFilters($request->get('keyWord'), $request->get('min-price'), $request->get('max-price'), $categoriesAllowed);
        }
        $priceRange = $productRepository->getPriceRange();

        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $products,
            'categories' => $categories,
            'minPrice' => $priceRange[0]['minPrice'],
            'maxPrice' => $priceRange[0]['maxPrice'],
            'keyWord' => $request->get('keyWord'),
            'minPriceInput' => $request->get('min-price') == null ? $priceRange[0]['minPrice'] : $request->get('min-price'),
            'maxPriceInput' => $request->get('max-price') == null ? $priceRange[0]['maxPrice'] : $request->get('max-price'),
            'categoriesAllowed' => $categoriesAllowed,
            'totalPages' => is_countable($products) ? ceil(count($products) / 20) : 0,
            'currentPage' => $request->get('page') == null ? 1 : $request->get('page'),
        ]);
        
    }

    #[Route('/products/byKeyWord', name: 'app_products_byKeyWord')]
    public function ProductsByKeyWord(ManagerRegistry $manager, Request $request): Response
    {   
        $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');
        $categoryRepository = $doctrine->getRepository('App\Entity\Category');

        $categories = $categoryRepository->findAll();
        $categoriesAllowed = [];
        foreach ($categories as $category) {
            $categoriesAllowed[] = $category->getId();
        }

        $priceRange = $productRepository->getPriceRange();
        $products = $productRepository->findByFilters($request->get('keyWord'), $priceRange[0]['minPrice'], $priceRange[0]['maxPrice'], $categoriesAllowed);

        return $this->render('products/index.html.twig', [
            'controller_name' => 'ProductsController',
            'products' => $products,
            'categories' => $categories,
            'minPrice' => $priceRange[0]['minPrice'],
            'maxPrice' => $priceRange[0]['maxPrice'],
            'keyWord' => $request->get('keyWord'),
            'minPriceInput' => $priceRange[0]['minPrice'],
            'maxPriceInput' => $priceRange[0]['maxPrice'],
            'categoriesAllowed' => $categoriesAllowed,
            'totalPages' => is_countable($products) ? ceil(count($products) / 20) : 0,
            'currentPage' => $request->get('page') == null ? 1 : $request->get('page'),
        ]);
    }

    #[Route('/products/{product}', name: 'app_product_single')]
    public function singleProduct(Product $product, ManagerRegistry $manager): Response
    {   
        $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');

        $products = $productRepository->findByCategory($product->getCategoryId()->getId());

        return $this->render('products/single.html.twig', [
            'controller_name' => 'ProductsController',
            'product' => $product,
            'products' => $products,
        ]);
    }
}
