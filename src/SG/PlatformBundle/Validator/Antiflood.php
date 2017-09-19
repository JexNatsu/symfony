<?php

namespace SG\PlatformBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Antiflood extends Constraint{
	public $message = "Vous avez déjà posté un message il y a moins de 15 secondes, merci d'attendre un peu.";
	//public $message = "Votre message %string% est considéré comme flood";

	public function validateBy(){
		return 'sg_platform_antiflood';
	}
}

?>
