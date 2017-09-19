<?php

namespace SG\PlatformBundle\Repository;

class ApplicationRepository extends \Doctrine\ORM\EntityRepository{
	public function getApplicationsWithAdvert($limit){
		$qb = $this->createQueryBuilder('a')
				->innerJoin('a.advert', 'adv')
				->addSelect('adv');

		$qb->setMaxResults($limit);

		return $qb->getQuery()->getResult();
	}

	/**
   * @param string   $ip
   * @param integer  $seconds
   * @return bool    True si au moins une candidature créée il y a moins de $seconds secondes a été trouvée. False sinon.
   */
	public function isFlood($ip, $seconds){
		return (bool) $this->createQueryBuilder('a')
			->select('COUNT(a)')
			->where('a.date >= :date')
			->setParameter('date', new \Datetime($seconds.' seconds ago'))
			// Nous n'avons pas cet attribut, je laisse en commentaire, mais voici comment pourrait être la condition :
			//->andWhere('a.ip = :ip')->setParameter('ip', $ip)
			->getQuery()
			->getSingleScalarResult()
		;
	}
}
