<?php

namespace SG\PlatformBundle\Beta;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class BetaListener{
	protected $betaHTML;

	protected $endDate;

	public function __construct(BetaHTMLAdder $betaHTML, $endDate){
		$this->betaHTML = $betaHTML;
		$this->endDate  = new \Datetime($endDate);
	}

	public function processBeta(FilterResponseEvent $event){
		if (!$event->isMasterRequest()){
			return;
		}

		$remainingDays = $this->endDate->diff(new \Datetime())->days;
		if ($remainingDays <= 0) {
			return;
		}

		$response = $this->betaHTML->addBeta($event->getResponse(), $remainingDays);

		// On met à jour la réponse avec la nouvelle valeur
		$event->setResponse($response);
	}
}
