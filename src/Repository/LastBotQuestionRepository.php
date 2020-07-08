<?php

namespace App\Repository;

use App\Entity\LastBotQuestion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @method LastBotQuestion|null find($id, $lockMode = null, $lockVersion = null)
 * @method LastBotQuestion|null findOneBy(array $criteria, array $orderBy = null)
 * @method LastBotQuestion[]    findAll()
 * @method LastBotQuestion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LastBotQuestionRepository extends ServiceEntityRepository implements LastBotQuestionRepositoryInterface
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, LastBotQuestion::class);
        $this->em = $this->getEntityManager();
        $this->logger = $logger;
    }

    public function findByChatId(int $id): ?LastBotQuestion
    {
        return $this->findOneBy(['chatId' => $id]);
    }

    public function save(LastBotQuestion $entity): void
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
