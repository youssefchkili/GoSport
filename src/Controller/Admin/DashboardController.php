<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Coupon;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    private $productRepository;
    private $userRepository;
    private $orderRepository;
    private $chartBuilder;

    public function __construct(
        ProductRepository $productRepository,
        UserRepository $userRepository,
        OrderRepository $orderRepository,
        ChartBuilderInterface $chartBuilder = null
    ) {
        $this->productRepository = $productRepository;
        $this->userRepository = $userRepository;
        $this->orderRepository = $orderRepository;
        $this->chartBuilder = $chartBuilder;
    }

    public function index(): Response
    {
        // Get dashboard data
        $totalProducts = $this->productRepository->count([]);
        $totalUsers = $this->userRepository->count([]);
        $totalOrders = $this->orderRepository->count([]);
        
        // Get low stock products (less than 5 items)
        $lowStockProducts = $this->productRepository->findBy(
            ['stock_quantity' => 0], 
            ['name' => 'ASC'],
            5
        );
        
        // Get recent orders
        $recentOrders = $this->orderRepository->findBy(
            [], 
            ['created_at' => 'DESC'], 
            5
        );
        
        // Create sales chart if Chart builder is available
        $salesChart = null;
        if ($this->chartBuilder) {
            $salesChart = $this->createSalesChart();
        }
        
        return $this->render('admin/dashboard.html.twig', [
            'totalProducts' => $totalProducts,
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'lowStockProducts' => $lowStockProducts,
            'recentOrders' => $recentOrders,
            'salesChart' => $salesChart,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('GoSport Admin')
             ->setFaviconPath('images/logo.png') ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        
        yield MenuItem::section('Products');
        yield MenuItem::linkToRoute('Products List', 'fas fa-box', 'app_product_index');
        yield MenuItem::linkToRoute('Add Product', 'fas fa-plus', 'app_product_new');
        
        yield MenuItem::section('Categories');
        yield MenuItem::linkToRoute('Categories List', 'fas fa-list', 'app_category_index');
        yield MenuItem::linkToRoute('Add Category', 'fas fa-plus', 'app_category_new');

        yield MenuItem::section('Users');
        yield MenuItem::linkToRoute('Users List', 'fas fa-users', 'app_user_index');
        
        
        yield MenuItem::section('Orders');
        yield MenuItem::linkToRoute('Orders List', 'fas fa-shopping-cart', 'app_order_index');
        
        yield MenuItem::section('Store');
        yield MenuItem::linkToRoute('View Shop Frontend', 'fas fa-store', 'app_products');
        
        yield MenuItem::section('');
        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('https://use.fontawesome.com/releases/v5.15.4/css/all.css');
    }

    private function createSalesChart(): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        
        // Last 7 days
        $dates = [];
        $sales = [];
        
        // Get sales data for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("-$i days");
            $dateFormatted = $date->format('Y-m-d');
            $dateLabel = $date->format('D');
            $dates[] = $dateLabel;
            
            // Get daily sales (should be implemented in OrderRepository)
            $dailySales = $this->orderRepository->getDailySalesTotal($dateFormatted) ?? 0;
            $sales[] = $dailySales;
        }
        
        $chart->setData([
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Sales (DT)',
                    'backgroundColor' => 'rgba(220, 53, 69, 0.2)',
                    'borderColor' => '#dc3545',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'data' => $sales,
                ],
            ],
        ]);
        
        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'beginAtZero' => true,
                ],
            ],
        ]);
        
        return $chart;
    }
}
