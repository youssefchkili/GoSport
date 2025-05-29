<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Coupon;
use App\Entity\Order;
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

        $cart = $this->getUser()->getCart();

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
            if ($item->getProduct()) {
                $price = $item->getProduct()->getPrice()*(1 - $item->getProduct()->getDiscountPercent() / 100);
                $discount = 0;

                $order = $this->entityManager->getRepository(Order::class)->getLastByUser($user);
                $coupon = $order ? $order->getCouponId() : null;
                if ($coupon && $coupon->getProduct() && $coupon->getProduct() == $item->getProduct()) {
                    $discount = $coupon->getDiscountValue();
                }

                $total += $price * (1 - $discount / 100) * $item->getQuantity();
            }


        }
        return $this->render('cart/list.html.twig', [
            'cartItems' => $cartItems,
            'total' => $total,
            'order' => $this->entityManager->getRepository(Order::class)->getLastByUser($user),
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
            $order = new Order();
            $order->setUserId($user);
            $order->setStatus('pending');
            $order->setOrderNumber(rand(100000, 999999));
            $order->setSubtotal(0);
            $order->setDiscount(0);
            $order->setTax(0);
            $order->setShippingAddressId($user->getAdress());
            $order->setBillingAddressId($user->getAdress()->getId());
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setTotal(0);
            $order->setShipping(19);
            $this->entityManager->persist($order);
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

    #[Route('/apply/{couponCode}', name: 'cart_apply_coupon', methods: ['POST'])]
    public function apply($couponCode, ProductRepository $productRepository): JsonResponse
    {   
        $orderRepository = $this->entityManager->getRepository(Order::class);
        $user = $this->getUser();
        $order = $orderRepository->getLastByUser($user);

        $couponRepository = $this->entityManager->getRepository(Coupon::class);
        $coupon = $couponRepository->findOneBy(['code' => $couponCode]);
        if (!$coupon) {
            return new JsonResponse(['success' => true, 'message' => 'Invalid coupon code.']);
        }
        $couponProduct = $coupon->getProduct();
        $cart = $user->getCart();

        if (!$cart) {
            return new JsonResponse(['success' => true, 'message' => 'Your cart is empty.']);
        }

        $cartItems = $cart->getCartItems();
        $productInCart = false;

        foreach ($cartItems as $item) {
            if ($item->getProduct() && $item->getProduct() === $couponProduct) {
                $productInCart = true;
                break;
            }
        }

        if (!$productInCart) {
            return new JsonResponse(['success' => true, 'message' => 'Coupon product is not in your cart.']);
        }
        $order->setCouponId($coupon);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => "success"]);
    }


    #[Route('/delete/{product_id}', name: 'cart_delete')]
    public function delete($product_id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($product_id);

        if(!$product) {
            throw $this->createNotFoundException('Product not found.');
        }

        $cart = $this->getUser()->getCart();
        $cartItem = $this->entityManager->getRepository(CartItem::class)->findOneBy(['cart' => $cart->getId(), 'product' => $product->getId()]);
        if($cartItem->getQuantity()>1){
            $cartItem->setQuantity($cartItem->getQuantity()-1);
        }else{
            $cart->removeCartItem($cartItem);
            $this->entityManager->remove($cartItem);
        }

        $this->entityManager->persist($cart);
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
