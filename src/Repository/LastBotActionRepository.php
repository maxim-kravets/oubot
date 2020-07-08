<?php

namespace App\Repository;

use App\Entity\LastBotAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method LastBotAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method LastBotAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method LastBotAction[]    findAll()
 * @method LastBotAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LastBotActionRepository extends ServiceEntityRepository implements LastBotActionRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, LastBotAction::class);
        $this->em = $this->getEntityManager();
    }

    public function findByChatId(int $chat_id): ?LastBotAction
    {
        return $this->findOneBy(['chatId' => $chat_id]);
    }

    public function save(LastBotAction $entity): void
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
