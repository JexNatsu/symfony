<?php

namespace SG\PlatformBundle\Twig;

use SG\PlatformBundle\Antispam\SGAntispam;

class AntispamExtension extends \Twig_Extension{
	/**
	 * @var SGAntispam
	 */
	private $sgAntispam;

	public function __construct (SGAntispam $sgAntispam){
		$this->sgAntispam = $sgAntispam;
	}

	/**
	 * Exemple de méthode pour Twig
	 *
	 * @param string $text
	 * @return bool
	 */
	public function checkIfArgumentIsSpam($text){
		return $this->sgAntispam->isSpam($text);
	}

	public function getFunctions(){
		return array(
			new \Twig_SimpleFunction('checkIfSpam', array($this, 'checkIfArgumentIsSpam'))
			//new \Twig_SimpleFunction('checkIfSpam', array($this->sgAntispam, 'isSpam'))
		);
	}

	// La méthode getName() identifie votre extension Twig, elle est obligatoire
	public function getName(){
		return 'SGAntispam';
	}
}

?>
