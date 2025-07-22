<?php

namespace App\Repository;

use App\Entity\Curso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CursoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Curso::class);
    }

    /**
     * Obtiene todos los cursos cuyo estado es activo (1)
     * 
     * @return Curso[]
     */
    public function findActivos(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.estado = :estado')
            ->setParameter('estado', 1)
            ->orderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

