<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Stripe\Stripe;
use Stripe\StripeClient; // Ajoutez ceci au début du fichier

class PaymentController extends AbstractController
{
    private OrderRepository $orderRepository;
    private EntityManager $entityManager;
    public function __construct(private ManagerRegistry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();
        $this->orderRepository = $this->entityManager->getRepository(Order::class);
    }
    #[Route('/payment/test-session', name: 'payment_test_session', methods: ['POST'])]
    public function createTestSession(Request $request): JsonResponse
    {
        // Utilisez les clés de test Stripe
        Stripe::setApiKey('sk_test_VePHdqKTYQjKNInc7u56JBrQ'); // Clé secrète de test (mode test)

        try {
            // Créez une session de paiement fictive
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'], // Types de paiements (ex. carte)
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Produit test', // Produit fictif
                        ],
                        'unit_amount' => 5000, // Montant fictif (en cents, ex. 50 USD)
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://localhost:8000/payment/success', // URL de succès de test
                'cancel_url' => 'http://localhost:8000/payment/failure', // URL en cas d'annulation
            ]);

            // Envoyer l'ID de la session créée
            return new JsonResponse(['id' => $session->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }


    #[Route('/payment/create-intent', name: 'payment_create_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): JsonResponse
    {
        try {
            // Étape 1 : Initialisez le client Stripe avec votre clé secrète
            $stripe = new StripeClient('sk_test_VePHdqKTYQjKNInc7u56JBrQ'); // Clé secrète de test

            // Étape 2 : Créez le PaymentIntent
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => 500, // Montant en centimes (exemple : 500 = 5.00 GBP)
                'currency' => 'gbp', // Devise utilisée
                'payment_method_types' => ['card'], // Méthodes de paiement acceptées
                // 'payment_method' => 'pm_card_visa', // Optionnel : Si vous testez une méthode spécifique
            ]);

            // Étape 3 : Retournez une réponse JSON avec les détails du PaymentIntent
            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret, // Utilisé dans le front-end pour confirmer le paiement
            ]);
        } catch (\Exception $e) {
            // Gérez les erreurs et retournez une réponse HTTP appropriée
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    #[Route('/payment', name: 'payment_page')]
    public function paymentPage(): Response
    {

        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to the cart.');
        }

        $orderRepository = $this->entityManager->getRepository(Order::class);

        $order = $orderRepository->getLastByUser($user);
        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('cart_checkout');
        }

        return $this->render('payment/payment.html.twig', [
            'order' => $order,
        ]);

    }



    #[Route('/payment/success', name: 'payment_success')]
    public function success(CartRepository $cartRepository, MailerService $mailer): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to the cart.');
        }


        $orderRepository = $this->entityManager->getRepository(Order::class);

        $order = $orderRepository->getLastByUser($user);
        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('cart_checkout');
        }

        $orderItems = $user->getCart()->getCartItems();

        $adress = $user->getAdress();
        $order->setShippingAddressId($adress);

        $mailer->sendEmail(
            $this->getUser()->getEmail(),
            'Payment Confirmation',
            "<html>
                <body>
                    <h2>Thank you for your purchase!</h2>
                    <p>Here is a summary of your order:</p>
                    <table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;'>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>"
                        . implode('', array_map(function($item) {
                            return "<tr>
                                <td>" . htmlspecialchars($item->getProduct()->getName()) . "</td>
                                <td>" . $item->getQuantity() . "</td>
                                <td>" . number_format($item->getProduct()->getPrice()*(1 - $item->getProduct()->getDiscountPercent()/ 100 ), 2) . " DT</td>
                                <td>" . number_format(($item->getProduct()->getPrice()*(1 - $item->getProduct()->getDiscountPercent()/ 100 ) * $item->getQuantity()), 2) . " DT</td>
                            </tr>";
                        }, iterator_to_array($orderItems)))
                        . "</tbody>
                    </table>
                    <p style='margin-top:20px; font-size:16px;'><strong>Total: " . number_format($order->getTotal(), 2) . " DT</strong></p>
                    <p>
                        <a href='" . $this->generateUrl('payment_success_view', [], 0) . "' style='display:inline-block;padding:10px 20px;background:#28a745;color:#fff;text-decoration:none;border-radius:5px;'>View confirmation page</a>
                    </p>
                </body>
            </html>"
        );
        return $this->redirectToRoute('payment_success_view');
    }

    #[Route('/payment/success/view', name: 'payment_success_view')]
    public function successView(CartRepository $cartRepository, MailerService $mailer): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to add items to the cart.');
        }


        $orderRepository = $this->entityManager->getRepository(Order::class);

        $order = $orderRepository->getLastByUser($user);
        if (!$order) {
            $this->addFlash('error', 'Order not found.');
            return $this->redirectToRoute('cart_checkout');
        }

        $orderItems = $order->getOrderItems();

        $cart = $cartRepository->findOneBy(['user' => $user]);

        if ($cart) {
            $user->setCart(null);
            $cart->setUser(null);
            $this->entityManager->persist($user);
            $this->entityManager->persist($cart);
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

    #[Route('/payment/failure', name: 'payment_failure')]
    public function failure(): JsonResponse
    {
        return new JsonResponse(['status' => 'failure', 'message' => 'Paiement fictif annulé.']);
    }
}