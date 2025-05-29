<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductForm;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function RawwProduct(ManagerRegistry $manager, Request $request): Response
    {
        $doctrine = $manager->getManager();
        $productRepository = $doctrine->getRepository('App\Entity\Product');
        $categoryRepository = $doctrine->getRepository('App\Entity\Category');
        $products = $productRepository->findAll();
        $priceRange = $productRepository->getPriceRange();
        $categories = $categoryRepository->findAll();
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
            'categories' => $categories,
            'minPrice' => $priceRange[0]['minPrice'],
            'maxPrice' => $priceRange[0]['maxPrice'],
            'totalPages' => is_countable($products) ? ceil(count($products) / 20) : 0,
            'currentPage' => $request->get('page') == null ? 1 : $request->get('page'),
        ]);
    }
    #[Route('/byFilters',name: 'app_product_index_byFilters', methods: ['GET'])]
    public function ProductByFilters(ManagerRegistry $manager, Request $request): Response
    {
        if ($request->get('min-price') == null) {
            return $this->ProductByKeyWord($manager, $request);
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

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
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
    #[Route('/byKeyWord', name: 'app_product_index_byKeyWord')]
    public function ProductByKeyWord(ManagerRegistry $manager, Request $request): Response
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

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
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


    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('createNewCategory')->getData()) {
                $newCategory = $form->get('newCategory')->getData();
                $entityManager->persist($newCategory);
                $product->setCategoryId($newCategory);
            }
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,

        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id<\d+>}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, MailerService $mailer): Response
    {
        $oldProduct = clone $product;
        $form = $this->createForm(ProductForm::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('createNewCategory')->getData()) {
                $newCategory = $form->get('newCategory')->getData();
                $entityManager->persist($newCategory);
                $product->setCategoryId($newCategory);
            }
            if ($oldProduct->getDiscountPercent() < $product->getDiscountPercent()) {
                foreach($product->getWishlist() as $wishlist) { // should've been plural
                    $mailer->sendEmail(
                        $wishlist->getUser()->getEmail(),
                        'Product On Sale',
                        "<html>
                            <body>
                                <h2>Good news!</h2>
                                <p>The product <strong>" . htmlspecialchars($product->getName()) . "</strong> is now on sale.</p>
                                <a href='" . $this->generateUrl('app_product_single', ['product' => $product->getId()], 0) . "'>You might want to check it out!</a>
                            </body>
                        </html>"
                    );
                }
            }
            if ($oldProduct->getStockQuantity() == 0 && $product->getStockQuantity() != 0) {
                foreach($product->getWishlist() as $wishlist) { // should've been plural
                    $mailer->sendEmail(
                        $wishlist->getUser()->getEmail(),
                        'Product On Sale',
                        "<html>
                            <body>
                                <h2>Good news!</h2>
                                <p>The product <strong>" . htmlspecialchars($product->getName()) . "</strong> is no longer out of stock.</p>
                                <a href='" . $this->generateUrl('app_product_single', ['product' => $product->getId()], 0) . "'>Hurry before it goes out again, click here!</a>
                            </body>
                        </html>"
                    );
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $ProductWithSameCategoryCount=count($productRepository->findByCategory($product->getCategoryId()->getId()));
            $category=$product->getCategoryId();
            if($ProductWithSameCategoryCount==1){
                $entityManager->remove($category);
            }
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }



}
