<?php

namespace App\Repository;

use App\Entity\Asignacion;
use App\Entity\Curso;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AsignacionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Asignacion::class);
    }

    public function findAllConCursosYUsuarios()
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.usuario', 'u')  // Usa leftJoin en lugar de join para ver si hay nulls
            ->leftJoin('a.curso', 'c')
            ->addSelect('u', 'c')
            ->getQuery()
            ->getResult();
    }

}
