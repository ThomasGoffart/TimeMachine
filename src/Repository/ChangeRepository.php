<?php

namespace NorthernIndustry\TimeMachineBundle\Repository;


use Doctrine\Persistence\ManagerRegistry;
use NorthernIndustry\TimeMachineBundle\Entity\Change;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Change|null find($id, $lockMode = null, $lockVersion = null)
 * @method Change|null findOneBy(array $criteria, array $orderBy = null)
 * @method Change[]    findAll()
 * @method Change[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChangeRepository extends ServiceEntityRepository {

	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, Change::class);
	}

}
