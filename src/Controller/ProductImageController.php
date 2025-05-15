<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductImageForm;
use App\Repository\ProductImageRepository;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Attribute\Route;

#[Route('/product/image/{product}')]
final class ProductImageController extends AbstractController
{


    #[Route(name: 'app_product_image_index', methods: ['GET'])]
    public function index(ProductImageRepository $productImageRepository,Product $product): Response
    {

        return $this->render('product_image/index.html.twig', [
            'product_images' => $productImageRepository->findByProduct($product),
            'product' => $product,
        ]);
    }

    #[Route('/new', name: 'app_product_image_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,Product $product,ProductImageRepository $productImageRepository ): Response
    {
        $filesystem = new Filesystem();
        $directoryPath = $this->getParameter('product_uploads_dir').'/'.$product->getId();
        $productImage = new ProductImage();

        $filename = uniqid() . '.jpg';
        $form = $this->createForm(ProductImageForm::class, $productImage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if(!$filesystem->exists($directoryPath))
            {
                $filesystem->mkdir($directoryPath);
            }
            $uploadedFile = $form->get('imageFile')->getData();
            if($uploadedFile)
            {
                $uploadedFile->move(
                    $directoryPath,
                    $filename
                );
                $productImage->setImagePath($this->getParameter('web_uploads_path').'/'.$product->getId().'/'.$filename);
            }
            else
            {
                $filename='bestPlayerRightNow.jpg';
                $productImage->setImagePath($this->getParameter('web_uploads_path').'/'.$filename);
            }

            $productImage->setProduct($product);
            $primaryImage = $productImageRepository->findPrimaryImageForProduct($product);

            if(!$primaryImage)
            {
                if(!$form->get('is_primary')->getData()){
                    $this->addFlash('warning',"at least one image must be a primary image");
                    $productImage->setIsPrimary(true);
                }

            }
            $entityManager->persist($productImage);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_image_index', [ 'product' => $product->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product_image/new.html.twig', [
            'product_image' => $productImage,
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}', name: 'app_product_image_show', methods: ['GET'])]
    public function show(ProductImage $productImage,Product $product): Response
    {
        return $this->render('product_image/show.html.twig', [
            'product_image' => $productImage,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_image_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductImage $productImage, EntityManagerInterface $entityManager,Product $product,ProductImageRepository $productImageRepository): Response
    {
        $form = $this->createForm(ProductImageForm::class, $productImage);
        $form->remove('imageFile');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $productImage->setIsPrimary($form->get('is_primary')->getData());
            $entityManager->persist($productImage);

            if ($productImage->isPrimary()) {
                // Ensure no other images are primary
                $otherImages = $productImageRepository->findBy(['product' => $product, 'is_primary' => true]);
                foreach ($otherImages as $otherImage) {
                    if ($otherImage !== $productImage) {
                        $otherImage->setIsPrimary(false);
                        $entityManager->persist($otherImage);
                    }
                }

                $entityManager->persist($productImage);
                $this->addFlash('success', 'Primary image updated successfully.');
            } else {
                // Current image is not primary


                $otherPrimary = $entityManager->createQueryBuilder()
                    ->select('pi')
                    ->from(ProductImage::class, 'pi')
                    ->where('pi.product = :product')
                    ->andWhere('pi.is_primary = :isPrimary')
                    ->andWhere('pi.id != :excludeId')
                    ->setParameter('product', $product)
                    ->setParameter('isPrimary', true)
                    ->setParameter('excludeId', $productImage->getId())
                    ->getQuery()
                    ->getOneOrNullResult();
                $allImages = $productImageRepository->findBy(['product' => $product]);

                if (!$otherPrimary) {

                    // No primary image exists
                    if (count($allImages) == 1) {
                        // Only one image exists (the current one), force it to be primary
                        $productImage->setIsPrimary(true);
                        $entityManager->persist($productImage);
                        $this->addFlash('warning', 'The only image must be primary.');
                } elseif (count($allImages) > 1) {
                        // Multiple images, none are primary: set current image as primary
                        $productImage->setIsPrimary(true);
                        $entityManager->persist($productImage);
                        $this->addFlash('warning', 'This image has been set as primary since no other images are primary.');
                    }
                }
                // If another primary image exists, no action needed
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_product_image_index', ['product' => $product->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('product_image/edit.html.twig', [
            'product_image' => $productImage,
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}', name: 'app_product_image_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        ProductImage $productImage,
        EntityManagerInterface $entityManager ,
        Product $product,
        ProductImageRepository $productImageRepository,
        Filesystem $filesystem,
        ParameterBagInterface $params): Response
    {
        if ($this->isCsrfTokenValid('delete'.$productImage->getId(), $request->getPayload()->getString('_token'))) {
            $uploadDir = $params->get('product_uploads_dir').'/'.$product->getId();
            $imagePath = $uploadDir.'/'.basename($productImage->getImagePath());

            // Handle primary image reassignment
            if ($productImage->isPrimary()) {
                $newPrimary = $productImageRepository->findNonPrimaryImageForProduct($product);
                if ($newPrimary) {
                    $newPrimary->setIsPrimary(true);
                    $entityManager->persist($newPrimary);
                }
            }

            // Remove from database
            $entityManager->remove($productImage);
            $entityManager->flush();

            // Delete physical file
            if ($filesystem->exists($imagePath)) {
                $filesystem->remove($imagePath);
                $this->addFlash('success', 'Image deleted successfully');
            } else {
                $this->addFlash('warning', 'Database record deleted but file not found');
            }

            // Optional: Remove directory if empty
            if ($filesystem->exists($uploadDir) && empty(glob($uploadDir.'/*'))) {
                $filesystem->remove($uploadDir);

            }
        }

        return $this->redirectToRoute('app_product_image_index', ['product' => $product->getId()], Response::HTTP_SEE_OTHER);
    }
}
