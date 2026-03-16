<?php

namespace App\Repository;

use App\Entity\Patient;
use App\Entity\PatientPhoto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PatientPhoto>
 */
class PatientPhotoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PatientPhoto::class);
    }

    /**
     * @return PatientPhoto[]
     */
    public function findByPatient(Patient $patient): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.patient = :patient')
            ->setParameter('patient', $patient)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
