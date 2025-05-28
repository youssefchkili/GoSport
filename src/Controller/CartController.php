<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/cart')]
final class CartController extends AbstractController
{
    private CartRepository $cartRepository;
    private EntityManager $entityManager;

    public function __construct(private ManagerRegistry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();
        $this->cartRepository = $this->entityManager->getRepository(Cart::class);
    }
    #[Route('/list', name: 'cart_list')]
    public function list(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to the cart.');
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user]);

        if (!$cart) {
            $this->addFlash('info', 'Your cart is empty.');
            return $this->render('cart/list.html.twig', [
                'cartItems' => [],
                'total' => 0,
            ]);
        }

        $cartItems = $cart->getCartItems();
        $total = 0;
        foreach ($cartItems as $item) {
            $product = $item->getProduct();
            if ($product) {
                $total += $product->getPrice() * $item->getQuantity();
            }


        }
        return $this->render('cart/list.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);

    }

    #[Route('/add/{product_id}/{quantity}', name: 'cart_add')]
    public function add($product_id, $quantity, ProductRepository $productRepository): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to the cart.');
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user]);


        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $this->entityManager->persist($cart);
            $this->entityManager->flush();
        }

        $product = $productRepository->find($product_id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        $existingItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if($existingItem) {
           $existingItem->setQuantity($existingItem->getQuantity() + $quantity);

        }else{
            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setCart($cart);
            $cartItem->setQuantity($quantity);
            $this->entityManager->persist($cartItem);
        }

        $this->entityManager->flush();

        //return $this->redirectToRoute('cart_list');
        return new JsonResponse(['success' => true, 'message' => "success"]);
    }


    #[Route('/delete/{product_id}', name: 'cart_delete')]
    public function delete($product_id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($product_id);

        if(!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        $cart = $this->cartRepository->find(1);
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['cart_id' => $cart->getId(), 'product_id' => $product->getId()]);
        if($cartItem->getQuantity()>1){
            $cartItem->setQuantity($cartItem->getQuantity()-1);
        }else{
            $this->entityManager->remove($cartItem);
        }

        $this->entityManager->flush();
        return $this->redirectToRoute('cart_list');

    }

    #[Route('/clear', name: 'cart_clear')]
    public function clear(): Response
    {
        $cart = $this->cartRepository->find(1);

        foreach ($cart->getCartItems() as $item) {
            $this->entityManager->remove($item);
        }

        $this->entityManager->flush();

        $this->addFlash('success', 'Cart has been cleared.');
        return $this->redirectToRoute('cart_list');
    }




}
