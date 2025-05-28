<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\BannerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        BannerRepository $bannerRepository = null
    ): Response {
        // Get featured products (limit to 8)
        $featuredProducts = $productRepository->findFeaturedProducts(8);
        
        // Get latest products (limit to 8)
        $latestProducts = $productRepository->findLatestProducts(8);
        
        // Get all categories for sidebar and category section
        $categories = $categoryRepository->findAll();
        
        // Get best selling products (for now, get random products)
        $bestSellingProducts = $productRepository->findBy([], ['id' => 'DESC'], 4);
        
        // Get active banner if BannerRepository exists
        $activeBanner = null;
        if ($bannerRepository) {
            $activeBanner = $bannerRepository->findActiveBanner();
        }

        return $this->render('home/index.html.twig', [
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'bestSellingProducts' => $bestSellingProducts,
            'categories' => $categories,
            'activeBanner' => $activeBanner,
        ]);
    }
}
