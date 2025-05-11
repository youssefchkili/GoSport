<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * @return Product[]
     */
    public function findByKeyWord($keyWord): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name like :val or p.slug like :val or p.description like :val')
            ->setParameter('val', '%'.$keyWord.'%')
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Float[]
     */
    public function getPriceRange($keyWord = ""): array
    {
        return $this->createQueryBuilder('p')
            ->select(' min( p.price ) as minPrice, max( p.price ) as maxPrice')
            ->andWhere('p.name like :val or p.slug like :val or p.description like :val')
            ->setParameter('val', '%'.$keyWord.'%')
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findAll(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findByFilters($keyWord, $minPrice, $maxPrice, $categoriesAllowed): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name like :val or p.slug like :val or p.description like :val')
            ->andWhere('p.price*(100-p.discount_percent)/100 >= :minPrice')
            ->andWhere('p.price*(100-p.discount_percent)/100 <= :maxPrice')
            ->andWhere('p.category_id IN (:categoriesAllowed)')
            ->setParameter('val', '%'.$keyWord.'%')
            ->setParameter('minPrice', $minPrice)
            ->setParameter('maxPrice', $maxPrice)
            ->setParameter('categoriesAllowed', $categoriesAllowed)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
