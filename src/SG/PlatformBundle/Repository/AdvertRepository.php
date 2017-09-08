<?php

namespace SG\PlatformBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/*
public function myFindAll(){
	// Méthode 1 : en passant par l'EntityManager
	$queryBuilder = $this->_em->createQueryBuilder()
		->select('a')
		->from($this->_entityName, 'a')
	;
	// Dans un repository, $this->_entityName est le namespace de l'entité gérée
	// Ici, il vaut donc OC\PlatformBundle\Entity\Advert

	// Méthode 2 : en passant par le raccourci (je recommande)
	$queryBuilder = $this->createQueryBuilder('a');

	// On n'ajoute pas de critère ou tri particulier, la construction
	// de notre requête est finie

	// On récupère la Query à partir du QueryBuilder
	$query = $queryBuilder->getQuery();

	// On récupère les résultats à partir de la Query
	$results = $query->getResult();

	// On retourne ces résultats
	return $results;
}

public function myFind($id){
	$queryBuilder = $this->createQueryBuilder('a');

	$queryBuilder->where('a.id = :id')->setParameter('id', $id);

	$query = $queryBuilder->getQuery();

	return $query->getResult();
}

public function findByAuthorAndDate($author, $year){
	$queryBuilder = $this->createQueryBuilder('a');

	$queryBuilder->where('a.author = :author')
					->setParameter('author', $author)
				 ->andWhere('a.date < :year')
					->setParameter('year', $year)
				 ->orderBy('a.date', 'DESC')
	;

	return $queryBuilder
		->getQuery()
		->getResult()
	;
}
*/
class AdvertRepository extends \Doctrine\ORM\EntityRepository{
	public function getAdverts($page, $nbPerPage){
		$query = $this->createQueryBuilder('a')
				->leftJoin('a.image', 'i')
				->addSelect('i')
				->leftJoin('a.categories', 'c')
				->addSelect('c')
				->orderBy('a.date', 'DESC')
				->getQuery();

		//return $query->getResult();;

		$query->setFirstResult(($page-1) * $nbPerPage)
			->setMaxResults($nbPerPage);

		return new Paginator($query, true);
	}

	public function whereCurrentYear(QueryBuilder $qb){
		$qb->andWhere('a.date BETWEEN :start AND :end')
			->setParameter('start', new \Datetime(date('Y').'-01-01'))  // Date entre le 1er janvier de cette année
			->setParameter('end',   new \Datetime(date('Y').'-12-31'))  // Et le 31 décembre de cette année
		;
	}

	public function getAdvertWithCategories(array $categoryNames){
		$qb = $this->createQueryBuilder('a')
				->innerJoin('a.categories', 'cat')
				->addSelect('cat');

		$qb->where($qb->expr()->in('cat.name', $categoryNames));

		return $qb->getQuery()->getResult();
	}
}
