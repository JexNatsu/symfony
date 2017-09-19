<?php

namespace SG\PlatformBundle\Purger;

use Doctrine\ORM\EntityManagerInterface;

class AdvertPurger{

	/**
   	 * @var EntityManagerInterface
   	 */
	private $em;

	public function __construct(EntityManagerInterface $em){
		$this->em = $em;
	}

	public function purge($days){
		$advertRepository 	   = $this->em->getRepository('SGPlatformBundle:Advert');
		$advertSkillRepository = $this->em->getRepository('SGPlatformBundle:AdvertSkill');

		// date d'il y a $days jours
		$date = new \Datetime($days.' days ago');

		// On récupère les annonces à supprimer
    	$listAdverts = $advertRepository->getAdvertsBefore($date);
    	
    	foreach ($listAdverts as $advert){
    		// On récupère les AdvertSkill liées à cette annonce
    		$advertSkills = $advertSkillRepository->findBy(array('advert' => $advert));

    		// Pour les supprimer toutes avant de pouvoir supprimer l'annonce elle-même
    		foreach ($advertSkills as $advertSkill){
    			$this->em->remove($advertSkill);
    		}

    		// On peut maintenant supprimer l'annonce
    		$this->em->remove($advert);
    	}

    	$this->em->flush();
    	//Derouineau phili
	}
}

?>
