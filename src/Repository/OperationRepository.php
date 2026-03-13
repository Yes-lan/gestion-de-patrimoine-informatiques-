<?php

namespace App\Repository;

use App\Entity\Operation;
use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Operation>
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    public function findByPatient(Patient $patient): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.patient = :patient')
            ->setParameter('patient', $patient)
            ->orderBy('o.dateOperation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
