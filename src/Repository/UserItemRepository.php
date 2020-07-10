<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\User;
use App\Entity\UserItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method UserItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserItem[]    findAll()
 * @method UserItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserItemRepository extends ServiceEntityRepository implements UserItemRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, UserItem::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    function isUserHasItem(User $user, Item $item): bool
    {
        return !empty($this->findOneBy([
            'user' => $user->getId(),
            'item' => $item->getId()
        ]));
    }

    function getListByUserId(int $user_id, int $page = 1, int $limit = 5): Paginator
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('ui')
            ->from('App\Entity\UserItem', 'ui')
            ->orderBy('ui.id', 'DESC')
            ->where('ui.user=:user_id')
            ->setParameter('user_id', $user_id)
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        return new Paginator($query, false);
    }

    function save(UserItem $entity): void
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
