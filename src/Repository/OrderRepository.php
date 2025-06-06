<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }
    public function getDailySalesTotal(string $date): ?float
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT SUM(total) as daily_total
            FROM `order`
            WHERE DATE(created_at) = :date
            AND status = :status
        ';
        
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            'date' => $date,
            'status' => 'completed'
        ]);
        
        $result = $resultSet->fetchAssociative();
        
        return $result['daily_total'] ?? 0;
    }

    public function getLastByUser($user): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user_id = :id')
            ->setParameter('id', $user->getId())
            ->orderBy('o.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
            ;
    }

//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
