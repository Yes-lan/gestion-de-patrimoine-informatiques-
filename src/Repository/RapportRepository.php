<?php

namespace App\Repository;

use App\Entity\Rapport;
use App\Entity\Operation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rapport>
 */
class RapportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rapport::class);
    }

    /**
     * Trouver tous les rapports d'un patient
     */
    public function findByOperation(Operation $operation): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.operation = :operation')
            ->setParameter('operation', $operation)
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Rechercher dans les rapports
     */
    public function searchByText(string $query, Operation $operation): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.operation = :operation')
            ->andWhere('r.contenuTexte LIKE :query OR r.titre LIKE :query')
            ->setParameter('operation', $operation)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Rapports finalisés d'un patient
     */
    public function findFinalizedByOperation(Operation $operation): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.operation = :operation')
            ->andWhere('r.statut = :statut')
            ->setParameter('operation', $operation)
            ->setParameter('statut', 'finalisé')
            ->orderBy('r.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
