<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Adress;
use App\Entity\Cart;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/order')]
final class OrderController extends AbstractController
{
    private OrderRepository $orderRepository;
    private EntityManager $entityManager;

    private array $countries = [
        'US' => 'United States',
        'CA' => 'Canada',
        'UK' => 'United Kingdom',
        'DE' => 'Germany',
        'FR' => 'France',
        'IT' => 'Italy',
        'ES' => 'Spain',
        'JP' => 'Japan',
        'AU' => 'Australia',
        'NZ' => 'New Zealand',
        'CN' => 'China',
        'IN' => 'India',
        'BR' => 'Brazil',
        'MX' => 'Mexico',
        // Add more countries as needed
    ];

    public function __construct(private ManagerRegistry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();
        $this->orderRepository = $this->entityManager->getRepository(Order::class);
    }
    #[Route('/', name: 'app_order_index', methods: ['GET'])]
    public function index(): Response
    {
        // Get all orders (you might want to add pagination later)
        $orders = $this->orderRepository->findBy([], ['created_at' => 'DESC']);
        
        return $this->render('order/index.html.twig', [
            'orders' => $orders,
            'controller_name' => 'OrderController',
        ]);
    }
    #[Route('/checkout', name: 'cart_checkout')]
    public function checkout(CartRepository $cartRepository ): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to the cart.');
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);

        if (!$cart || count($cart->getCartItems()) === 0) {
            $this->addFlash('warning', 'Your cart is empty.');
            return $this->redirectToRoute('cart_list');
        }

        $subtotal = 0;
        $cartItems = $cart->getCartItems();
        foreach ($cartItems as $item) {
            $subtotal += $item->getProduct()->getPrice()*(1 - $item->getProduct()->getDiscountPercent()/ 100 ) * $item->getQuantity();
        }

        $discount = 0;
        $tax = 0.00;  
        $shipping = 19.00;
        $total = $subtotal - $discount + $tax + $shipping;

        $order = new Order();
        $order->setUserId($this->getUser());
        $order->setOrderNumber(random_int(100000, 999999));
        $order->setStatus('pending');
        $order->setSubtotal($subtotal);
        $order->setDiscount($discount);
        $order->setTax($tax);
        $order->setShipping($shipping);
        $order->setTotal($total);
        $order->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($order);


        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOrderId($order);
            $orderItem->setProductId($cartItem->getProduct());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getProduct()->getPrice()*(1 - $cartItem->getProduct()->getDiscountPercent()/ 100 ));
            $orderItem->setSubtotal($cartItem->getProduct()->getPrice()*(1 - $cartItem->getProduct()->getDiscountPercent()/ 100 ) * $cartItem->getQuantity());

            $this->entityManager->persist($orderItem);
            $order->addOrderItem($orderItem);
        }
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $this->addFlash("success", "Order placed successfully");

        return $this->render('order/checkout.html.twig' , [
            'order' => $order,
            'id' => $order->getId(),
            'cartItems' => $cartItems,
            'countries' => $this->countries,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,

        ]);


    }



    #[Route('/my_orders', name: 'order_list')]
    public function list(): Response
    {
        $orders = $this->orderRepository->findBy(['user' => $this->getUser()]);
        return $this->render('order/list.html.twig' , [
            'orders' => $orders,
        ]);
    }

    #[Route('/success/{id}', name: 'checkout_success')]
    public function success(int $id, CartRepository $cartRepository): Response
    {

        $order = $this->orderRepository->find($id);

        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('order_list');
        }


        $orderItems = $order->getOrderItems();


        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to the cart.');
        }



        $cart = $cartRepository->findOneBy(['user' => $user]);

        if ($cart) {
            foreach ($cart->getCartItems() as $cartItem) {
                $this->entityManager->remove($cartItem);
            }
            $this->entityManager->flush();
        }
        $adress = $user->getAdress();
        $order->setShippingAddressId($adress);


        return $this->render('order/success.html.twig', [
            'order' => $order,
            'orderItems' => $orderItems,
            'subtotal' => $order->getSubtotal(),
            'discount' => $order->getDiscount(),
            'tax' => $order->getTax(),
            'shipping' => $order->getShipping(),
            'total' => $order->getTotal(),
        ]);
    }


}
