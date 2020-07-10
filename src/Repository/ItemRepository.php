<?php

namespace App\Repository;

use App\Entity\Item;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository implements ItemRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Item::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    function findById(int $id): ?Item
    {
        return $this->findOneBy(['id' => $id]);
    }

    function findByName(string $name): ?Item
    {
        return $this->findOneBy(['name' => $name]);
    }

    function getList(int $page, int $limit = 5, ?int $category_id = null): Paginator
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('i')
            ->from('App\Entity\Item', 'i')
            ->where('i.visible=1')
        ;

        if (!empty($category_id)) {
            $query
                ->where('i.category=:category_id')
                ->setParameter('category_id', $category_id)
            ;
        }

        $query
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        return new Paginator($query, false);
    }

    public function save(Item $entity): void
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
