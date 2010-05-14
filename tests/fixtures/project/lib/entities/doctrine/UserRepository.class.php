<?php

use \Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
	public function getEditUserQb(array $params)
	{
	  $qb = $this->_em->createQueryBuilder();

		$qb->from('Entities\User', 'a')
		   ->innerJoin('a.profile', 'p')
			 ->addSelect('a', 'p');

    return $qb;
	}
}