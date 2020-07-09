<?php

namespace App\Repository;

use App\Entity\Support;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Support|null find($id, $lockMode = null, $lockVersion = null)
 * @method Support|null findOneBy(array $criteria, array $orderBy = null)
 * @method Support[]    findAll()
 * @method Support[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportRepository extends ServiceEntityRepository implements SupportRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Support::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    public function findById(int $id): ?Support
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findUnansweredByUserId(int $id): ?Support
    {
        return $this->findOneBy([
            'user' => $id,
            'answered' => 0
        ]);
    }

    public function getListUnanswered(): Paginator
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('s')
            ->from('App\Entity\Support', 's')
            ->where('s.answered=0')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
        ;

        return new Paginator($query, false);
    }

    public function save(Support $entity): void
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
