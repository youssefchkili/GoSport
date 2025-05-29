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
            ->leftJoin('p.category', 'c')
            ->andWhere('p.name LIKE :val OR p.slug LIKE :val OR p.description LIKE :val OR c.name = :value')
            ->setParameter('val', '%' . $keyWord . '%')
            ->setParameter('value', $keyWord)
            ->orderBy('p.price', 'ASC')
            ->getQuery()
            ->getResult();
        }

    /**
     * @return Float[]
     */
    public function getPriceRange(): array
    {
        return $this->createQueryBuilder('p')
            ->select(' min( p.price*(100-p.discount_percent)/100 ) as minPrice, max( p.price*(100-p.discount_percent)/100 ) as maxPrice')
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
            ->leftJoin('p.category_id', 'c')
            ->andWhere('p.name like :val or p.slug like :val or p.description like :val OR c.name LIKE :val')
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
    
    /**
     * @return Product[] Returns an array of Product objects
     */
    public function findByCategory($categoryId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category_id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->setMaxResults(8)
            ->getQuery()
            ->getResult()
        ;
    }
    /**
     * Find featured products
     *
     * @param int $limit The maximum number of products to return
     * @return Product[]
     */
    public function findFeaturedProducts(int $limit = 4): array
    {
        // Check if isFeatured property exists in your Product entity
        // If not, you might need to modify this query
        $qb = $this->createQueryBuilder('p');

        // If your Product entity has isFeatured property
        if (property_exists('App\Entity\Product', 'isFeatured')) {
            $qb->where('p.isFeatured = :featured')
                ->setParameter('featured', true);
        }

        // If your Product entity has isActive property
        if (property_exists('App\Entity\Product', 'isActive')) {
            $qb->andWhere('p.isActive = :active')
                ->setParameter('active', true);
        }

        $qb->orderBy('p.id', 'DESC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find latest products
     *
     * @param int $limit The maximum number of products to return
     * @return Product[]
     */
    public function findLatestProducts(int $limit = 4): array
    {
        $qb = $this->createQueryBuilder('p');

        // If your Product entity has isActive property
        if (property_exists('App\Entity\Product', 'isActive')) {
            $qb->where('p.isActive = :active')
                ->setParameter('active', true);
        }

        // Use createdAt if it exists, otherwise fall back to id
        if (property_exists('App\Entity\Product', 'createdAt')) {
            $qb->orderBy('p.createdAt', 'DESC');
        } else {
            $qb->orderBy('p.id', 'DESC');
        }

        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Find products with discount (for best selling simulation)
     */
    public function findBestSelling(int $limit = 4): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.discount_percent > 0')
            ->orderBy('p.discount_percent', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find products by category for filtering
     */
    public function findByCategoryId(int $categoryId, int $limit = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.category_id = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->orderBy('p.id', 'DESC');
        
        if ($limit) {
            $qb->setMaxResults($limit);
        }
        
        return $qb->getQuery()->getResult();
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
