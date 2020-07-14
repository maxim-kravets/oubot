<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Promocode;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, User::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    public function findById(int $id): ?User
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findByChatId(int $id): ?User
    {
        return $this->findOneBy(['chatId' => $id]);
    }

    public function findAdminByName(string $name): ?User
    {
        return $this->findOneBy([
            'administrator' => true,
            'name' => $name
        ]);
    }

    public function findAdminByChatId(int $id): ?User
    {
        return $this->findOneBy([
            'administrator' => true,
            'chatId' => $id
        ]);
    }

    public function getAdminsList(): Paginator
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('u')
            ->from('App\Entity\User', 'u')
            ->where('u.administrator=1')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
        ;

        return new Paginator($query, false);
    }

    public function getListForMailing(?Item $item = null, ?Promocode $promocode = null): Paginator
    {
        $query = $this->createQueryBuilder('u')
            ->select('u', 'up', 'ui')
            ->leftJoin('u.userItems', 'ui')
            ->leftJoin('u.userPromocodes', 'up')
//            ->where('u.administrator=1')
        ;

        if (!empty($item)) {
            $query
                ->andWhere('ui.item=:itemId')
                ->setParameter('itemId', $item->getId())
            ;
        }

        if (!empty($promocode)) {
            $query
                ->andWhere('up.promocode=:promocodeId')
                ->setParameter('promocodeId', $promocode->getId())
            ;
        }

        $query
            ->andWhere('u.lastMailingDate<:date')
            ->setParameter('date', (new DateTime('-30days')))
        ;

        $query
            ->orderBy('u.id', 'DESC')
            ->getQuery()
        ;

        return new Paginator($query, false);
    }

    public function save(User $entity): void
    {
        try {
            $this->em->persist($entity);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }
    }

    public function remove(User $entity): void
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
