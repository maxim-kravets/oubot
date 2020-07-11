<?php

namespace App\Repository;

use App\Entity\Promocode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Promocode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Promocode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Promocode[]    findAll()
 * @method Promocode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromocodeRepository extends ServiceEntityRepository implements PromocodeRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Promocode::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    function findById(int $id): ?Promocode
    {
        return $this->findOneBy(['id' => $id]);
    }

    function findByName(string $name): ?Promocode
    {
        return $this->findOneBy(['name' => $name]);
    }

    function getList(int $page = 1, int $limit = 5): Paginator
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('p')
            ->from('App\Entity\Promocode', 'p')
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        return new Paginator($query, false);
    }

    function save(Promocode $entity): void
    {
        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }
    }

    function remove(Promocode $entity): void
    {
        try {
            $this->em->remove($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }
    }
}
