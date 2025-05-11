<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
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
    #[Route('/checkout', name: 'cart_checkout')]
    public function checkout(CartRepository $cartRepository ): Response
    {
        $cart = $cartRepository->find(1);
        if (!$cart || count($cart->getCartItems()) === 0) {
            $this->addFlash('warning', 'Your cart is empty.');
            return $this->redirectToRoute('cart_list');
        }

        // Compute totals
        $subtotal = 0;
        $cartItems = $cart->getCartItems();
        foreach ($cartItems as $item) {
            $subtotal += $item->getProductID()->getPrice() * $item->getQuantity();
        }

        $discount = 0; // apply logic later if coupons exist
        $tax = 0.00;   // apply logic if tax rules exist
        $shipping = 19.00;
        $total = $subtotal - $discount + $tax + $shipping;

        $order = new Order();
        $order->setUserId($this->getUser());
        $order->setOrderNumber(random_int(100000, 999999)); // or a better strategy
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
            $orderItem->setProductId($cartItem->getProductID());
            $orderItem->setQuantity($cartItem->getQuantity());
            $orderItem->setUnitPrice($cartItem->getProductID()->getPrice());

            $this->entityManager->persist($orderItem);
        }
         $this->addFlash("success", "Order placed successfully");

        return $this->render('order/success.html.twig' , [
            'order' => $order,
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
        $orders = $this->orderRepository->findBy(['user_id' => $this->getUser()]);
        return $this->render('order/list.html.twig' , [
            'orders' => $orders,
        ]);
    }


}
