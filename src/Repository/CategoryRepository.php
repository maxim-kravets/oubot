<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository implements CategoryRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Category::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    function findById(int $id): ?Category
    {
        return $this->findOneBy(['id' => $id]);
    }

    function findByName(string $name): ?Category
    {
        return $this->findOneBy(['name' => $name]);
    }

    function getList(int $page, int $limit = 5): Paginator
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c')
            ->from('App\Entity\Category', 'c')
            ->orderBy('c.id', 'DESC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        return new Paginator($query, false);
    }

    function save(Category $entity): void
    {
        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }
    }
}
