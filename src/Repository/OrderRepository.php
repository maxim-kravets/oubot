<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Order;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository implements OrderRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Order::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    public function getOrder(User $user, Item $item): ?Order
    {
        return $this->findOneBy([
            'user' => $user->getId(),
            'item' => $item->getId()
        ]);
    }

    public function findById(int $id): ?Order
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function save(Order $order): void
    {
        try {
            $this->em->persist($order);
            $this->em->flush();
        } catch (ORMException $e) {
            $this->logger->critical($e->getMessage());
            die();
        }
    }
}
