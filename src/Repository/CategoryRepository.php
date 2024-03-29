<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Category[]
     */
    public function findAllOrdered(): array
    {
        $qb = $this->createQueryBuilder('category')
            ->addOrderBy('category.name', Criteria::DESC)
            ->addSelect('COUNT(fortuneCookie.id) AS fortuneCookiesTotal')
            ->leftJoin('category.fortuneCookies', 'fortuneCookie')
            ->addGroupBy('category.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $term
     * @return Category[]
     */
    public function search(string $term): array
    {
        $termList = explode(' ', $term);
        $qb = $this->addOrderByCategoryName();
            
        return $this->addGroupByCategoryAndCountFortunes($qb)
            ->andWhere('category.name LIKE :searchTerm OR category.name IN (:termList) OR category.iconKey LIKE :searchTerm OR fortuneCookie.fortune LIKE :searchTerm')
            ->setParameter('searchTerm', '%'.$term.'%')
            ->setParameter('termList', $termList)
            ->getQuery()
            ->getResult();
    }

    public function findWithFortunesJoin(int $id): ?Category
    {
        return $this->addFortuneCookieJoinAndSelect()
            ->andWhere('category.id = :id')
            ->setParameter('id', $id)
            ->orderBy('RAND()', Criteria::ASC)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function addFortuneCookieJoinAndSelect(QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder('category'))
            ->addSelect('fortuneCookie')
            ->leftJoin('category.fortuneCookies', 'fortuneCookie');
    }

    private function addOrderByCategoryName(QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder('category'))
            ->addOrderBy('category.name', Criteria::DESC);
    }

    private function addGroupByCategoryAndCountFortunes(QueryBuilder $qb = null): QueryBuilder
    {
        return ($qb ?? $this->createQueryBuilder('category'))
            ->addSelect('COUNT(fortuneCookie.id) AS fortuneCookiesTotal')
            ->leftJoin('category.fortuneCookies', 'fortuneCookie')
            ->addGroupBy('category.id');
    }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
