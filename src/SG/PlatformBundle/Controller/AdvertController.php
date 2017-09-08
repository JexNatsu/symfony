<?php
	// REDIRECTION
	/*
	return $this->redirectToRoute('sg_platform_home');
	*/

    // REPONSE EN JSON
    /*
    $response = new Response(json_encode(array('id' => $id)));
    $response->headers->set('Content-Type', 'application/json');
    return $response;

    // return new JsonResponse(array('id' => $id));
    */

    // USAGE DE SESSION
    /*
    $session = $request->getSession();
    $userId = $session->get('user_id');
    $session->set('user_id', 666);
    return new Response("<body>Je suis une page de test, je n'ai rien à dire ". $userId ."</body>");
    */

	// REQUEST
    //$tag = $request->query->get('tag');

    // UTILISATION SERVICE ANTISPAM CREER
	/*
	$antispam = $this->container->get('sg_platform.antispam');
	$text = '...';
	if ($antispam->isSpam($text)) {
		throw new \Exception('Votre message a été détecté comme spam !');
	}
	*/

namespace SG\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SG\PlatformBundle\Entity\Advert;
use SG\PlatformBundle\Entity\Image;
use SG\PlatformBundle\Entity\Application;
use SG\PlatformBundle\Entity\AdvertSkill;

class AdvertController extends Controller{
	public function indexAction($page){
        if ($page < 1) {
			throw new NotFoundHttpException('Page "'. $page .'" inexistante.');
		}

		$nbPerPage = 3;
		$listAdverts = $this->getDoctrine()->getManager()->getRepository('SGPlatformBundle:Advert')->getAdverts($page, $nbPerPage);

		$nbPages = ceil(count($listAdverts) / $nbPerPage);

		if ($page > $nbPages) {
			throw $this->createNotFoundException("La page ". $page ." n'existe pas.");
		}

		return $this->render('SGPlatformBundle:Advert:index.html.twig', array(
			'listAdverts' => $listAdverts,
			'nbPages'     => $nbPages,
			'page'        => $page
			));
	}

	public function viewAction($id){
	    $em = $this->getDoctrine()->getManager();
	    $advert = $em->getRepository('SGPlatformBundle:Advert')->find($id);

	    if ($advert === null){
	    	throw new NotFoundHttpException("L'annonce d'id ". $id ." n'existe pas.");
	    }

	    $listApplications = $em
	    	->getRepository('SGPlatformBundle:Application')
	    	->findBy(array('advert' => $advert));

	    $listAdvertSkills = $em
	    	->getRepository('SGPlatformBundle:AdvertSkill')
	    	->findBy(array('advert' => $advert));

	    return $this->render('SGPlatformBundle:Advert:view.html.twig', array(
			'advert' 		   => $advert,
			'listApplications' => $listApplications,
			'listAdvertSkills' => $listAdvertSkills
	    ));
	}

	public function addAction(Request $request){
	    $em = $this->getDoctrine()->getManager();

	    // ! Création d'una annonce via le formulaire !

		// Si la requête est en POST, formulaire soumis
		if ($request->isMethod('POST')) {
		  $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
		  return $this->redirectToRoute('sg_platform_view', array('id' => $advert->getId()));
		}

		return $this->render('SGPlatformBundle:Advert:add.html.twig');
	}

	public function editAction($id, Request $request){
		$em = $this->getDoctrine()->getManager();
	    $advert = $em->getRepository('SGPlatformBundle:Advert')->find($id);

	    if ($advert === null){
	    	throw new NotFoundHttpException("L'annonce d'id ". $id ." n'existe pas.");
	    }

	    // ! Modification d'una annonce via le formulaire !

	    // Si la requête est en POST, formulaire soumis
		if ($request->isMethod('POST')) {
			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
			return $this->redirectToRoute('sg_platform_view', array('id' => $advert->getId()));
		}

		return $this->render('SGPlatformBundle:Advert:edit.html.twig', array('advert' => $advert));
	}

	public function deleteAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		$advert = $em->getRepository('SGPlatformBundle:Advert')->find($id);

		if ($advert === null) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		// On boucle sur les catégories de l'annonce pour les supprimer
		foreach ($advert->getCategories() as $category) {
			$advert->removeCategory($category);
		}

		$em->flush();

		$listApplications = $em
	    	->getRepository('SGPlatformBundle:Application')
	    	->findBy(array('advert' => $advert));

		return $this->render('SGPlatformBundle:Advert:delete.html.twig');
	}

	public function menuAction($limit){
		$em = $this->getDoctrine()->getManager();
		$listAdverts = $em->getRepository('SGPlatformBundle:Advert')->findBy(array(), array('date' => 'desc'), $limit, 0);

		return $this->render('SGPlatformBundle:Advert:menu.html.twig', array('listAdverts' => $listAdverts));
	}
}

?>