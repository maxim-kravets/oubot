<?php

namespace App\Repository;

use App\Entity\Promocode;
use App\Entity\User;
use App\Entity\UserPromocode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method UserPromocode|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPromocode|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPromocode[]    findAll()
 * @method UserPromocode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPromocodeRepository extends ServiceEntityRepository implements UserPromocodeRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, UserPromocode::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    public function isUserUsedPromocode(User $user, Promocode $promocode): bool
    {
        return !empty($this->findOneBy([
            'user' => $user->getId(),
            'promocode' => $promocode->getId()
        ]));
    }

    public function save(UserPromocode $entity): void
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
