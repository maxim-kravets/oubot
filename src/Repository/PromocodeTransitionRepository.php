<?php

namespace App\Repository;

use App\Entity\Promocode;
use App\Entity\PromocodeTransition;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method PromocodeTransition|null find($id, $lockMode = null, $lockVersion = null)
 * @method PromocodeTransition|null findOneBy(array $criteria, array $orderBy = null)
 * @method PromocodeTransition[]    findAll()
 * @method PromocodeTransition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PromocodeTransitionRepository extends ServiceEntityRepository implements PromocodeTransitionRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, PromocodeTransition::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    function isUserTransitByPromocode(User $user, Promocode $promocode): bool
    {
        return !empty($this->findOneBy([
            'promocode' => $promocode->getId(),
            'user' => $user->getId()
        ]));
    }

    function save(PromocodeTransition $entity): void
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
