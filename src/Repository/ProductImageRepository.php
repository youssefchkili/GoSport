<?php

namespace App\Repository;

use App\Entity\Product;
use App\Entity\ProductImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductImage>
 */
class ProductImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductImage::class);
    }
    public function findByProduct($product): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.product = :val')
            ->setParameter('val', $product)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    public function findPrimaryImageForProduct(Product $product): ?ProductImage
    {
        return $this->createQueryBuilder('pi')
            ->where('pi.product = :product')
            ->andWhere('pi.is_primary = :isPrimary')
            ->setParameter('product', $product)
            ->setParameter('isPrimary', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findNonPrimaryImageForProduct(Product $product): ?ProductImage
    {
        return $this->createQueryBuilder('pi')
            ->where('pi.product = :product')
            ->andWhere('pi.is_primary = :isPrimary')
            ->setParameter('product', $product)
            ->setParameter('isPrimary', false)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

//    /**
//     * @return ProductImage[] Returns an array of ProductImage objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProductImage
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
