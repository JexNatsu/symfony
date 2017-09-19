<?php

namespace SG\PlatformBundle\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class AntifloodValidator extends ConstraintValidator{
	private $requestStack;
	private $em;

	public function __construct(RequestStack $requestStack, EntityManagerInterface $em){
		$this->requestStack = $requestStack;
		$this->em           = $em;
	}

	public function validate($value, Constraint $constraint){
		$request = $this->requestStack->getCurrentRequest();

		$ip = $request->getClientIp();

		// On vérifie si cette IP a déjà posté une candidature il y a moins de 15 secondes
		$isFlood = false;
		$this->em
			->getRepository('SGPlatformBundle:Application')
			->isFlood($ip, 15)
		;
		
		if ($isFlood) {
			$this->context->addViolation($constraint->message);
			/*
			$this->context
				->buildViolation($constraint->message)
				->setParameters(array('%string%' => $value))
				->addViolation();
			*/
		}
	}
}

?>