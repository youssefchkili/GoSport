<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Entity\Product;
use App\Form\CouponForm;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/coupon/{product}')]
final class CouponController extends AbstractController
{
    #[Route(name: 'app_coupon_index', methods: ['GET'])]
    public function index(CouponRepository $couponRepository,Product $product): Response
    {
        return $this->render('coupon/index.html.twig', [
            'coupons' => $couponRepository->findByProduct($product),
            'product' => $product
        ]);
    }

    #[Route('/new', name: 'app_coupon_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,Product $product): Response
    {
        $coupon = new Coupon();
        $form = $this->createForm(CouponForm::class, $coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $numberOfCoupons = $form->get('number_of_coupons')->getData();

            for ($i = 0; $i < $numberOfCoupons; $i++) {
                $newCoupon = clone $coupon;
                $newCoupon->setCode(uniqid('CPN'));
                $newCoupon->setProduct($product);
                $entityManager->persist($newCoupon);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_coupon_index', [
                'product' => $product->getId()
            ], Response::HTTP_SEE_OTHER);
        }


        return $this->render('coupon/new.html.twig', [
            'coupon' => $coupon,
            'product' => $product,
            'form' => $form,
        ]);
    }





    #[Route('/{id}', name: 'app_coupon_delete', methods: ['POST'])]
    public function delete(Request $request, Coupon $coupon, EntityManagerInterface $entityManager,Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coupon->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($coupon);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_coupon_index', [
            'product' => $product->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
