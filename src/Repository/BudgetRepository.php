<?php

namespace App\Repository;

use App\Entity\Budget;
use App\Utils\DateIntervalResolver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;


/**
 * @method Budget|null find($id, $lockMode = null, $lockVersion = null)
 * @method Budget|null findOneBy(array $criteria, array $orderBy = null)
 * @method Budget[]    findAll()
 * @method Budget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BudgetRepository extends ServiceEntityRepository
{
    
    protected $dateResolver;
    
    public function __construct(ManagerRegistry $registry, DateIntervalResolver $dateResolver)
    {
        parent::__construct($registry, Budget::class);
        $this->dateResolver = $dateResolver;
    }
    
    public function getTodayRecords($userId)
    {
        $date = new \DateTime('today midnight');
        $today = $date->format('Y-m-d H:i:s');
        
        return $this->createQueryBuilder('b')
            ->andWhere('b.created > :date')
            ->setParameter('date', $today)
            ->andWhere('b.user = :user')
            ->setParameter('user', $userId)
            ->orderBy('b.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getLastWeekRecords($userId)
    {
        // sum(amount), date(created)
        $interval = $this->dateResolver->getLastWeek();
        
        // aggregation example
        //SELECT sum(amount) as sum, DATE(created) as day
        //FROM `budget`
        //WHERE created > '2020-01-27 00:00:00' AND created < '2020-01-31 00:00:00'
        //GROUP BY day
        return $this->createQueryBuilder('b')
            ->select('SUM(b.amount) as sum', 'DATE(b.created) as date', 'b.type')
            ->andWhere('b.created < :start')
            ->setParameter('start', $interval['start'])
            ->andWhere('b.created > :end')
            ->setParameter('end', $interval['end'])
            ->andWhere('b.user = :user')
            ->setParameter('user', $userId)
            ->groupBy('date', 'b.type')
            ->orderBy('date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    // @todo: unite with the method above
    public function getLastMonthRecords($userId)
    {
        // sum(amount), date(created)
        $interval = $this->dateResolver->getLastMonth();
        
        // aggregation example
        //SELECT sum(amount) as sum, DATE(created) as day
        //FROM `budget`
        //WHERE created > '2020-01-27 00:00:00' AND created < '2020-01-31 00:00:00'
        //GROUP BY day
        return $this->createQueryBuilder('b')
            ->select('SUM(b.amount) as sum', 'DATE(b.created) as date', 'b.type')
            ->andWhere('b.created < :start')
            ->setParameter('start', $interval['start'])
            ->andWhere('b.created > :end')
            ->setParameter('end', $interval['end'])
            ->andWhere('b.user = :user')
            ->setParameter('user', $userId)
            ->groupBy('date', 'b.type')
            ->orderBy('date', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    
    public function getTodaySum($userId, $type)
    {
        $date = new \DateTime('today midnight');
        $today = $date->format('Y-m-d H:i:s');
        
        return $this->createQueryBuilder('b')
            ->select('SUM(b.amount) as sum')
            ->andWhere('b.created > :date')
            ->setParameter('date', $today)
            ->andWhere('b.type = :type')
            ->setParameter('type', $type)
            ->andWhere('b.user = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * @param $userId int
     * @param $type string income|expense
     *
     * @return array|null
     * @throws \Exception
     */
    public function getLastWeekSum($userId, $type) {
        $interval = $this->dateResolver->getLastWeek();
    
        //SQL aggregation example
        //SELECT sum(amount) as sum
        //FROM `budget`
        //WHERE created > '2020-01-24 00:00:00' AND created < '2020-01-31 00:00:00' AND type = 'expense'
        return $this->createQueryBuilder('b')
            ->select('SUM(b.amount) as sum')
            ->andWhere('b.created < :start')
            ->setParameter('start', $interval['start'])
            ->andWhere('b.created > :end')
            ->setParameter('end', $interval['end'])
            ->andWhere('b.type = :type')
            ->setParameter('type', $type)
            ->andWhere('b.user = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getResult()
        ;
    
    }
    
    // @todo: unite with the method above
    public function getLastMonthSum($userId, $type) {
        $interval = $this->dateResolver->getLastMonth();
        
        //SQL aggregation example
        //SELECT sum(amount) as sum
        //FROM `budget`
        //WHERE created > '2020-01-24 00:00:00' AND created < '2020-01-31 00:00:00' AND type = 'expense'
        return $this->createQueryBuilder('b')
            ->select('SUM(b.amount) as sum')
            ->andWhere('b.created < :start')
            ->setParameter('start', $interval['start'])
            ->andWhere('b.created > :end')
            ->setParameter('end', $interval['end'])
            ->andWhere('b.type = :type')
            ->setParameter('type', $type)
            ->andWhere('b.user = :user')
            ->setParameter('user', $userId)
            ->getQuery()
            ->getResult()
            ;
        
    }
    
    
    // /**
    //  * @return Budget[] Returns an array of Budget objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Budget
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
