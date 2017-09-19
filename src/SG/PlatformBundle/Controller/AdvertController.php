<?php
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

namespace SG\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SG\PlatformBundle\Entity\Advert;
use SG\PlatformBundle\Form\AdvertType;
use SG\PlatformBundle\Form\AdvertEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SG\PlatformBundle\Event\PlatformEvents;
use SG\PlatformBundle\Event\MessagePostEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdvertController extends Controller{
	public function indexAction($page){
        if ($page < 1) {
			throw new NotFoundHttpException('Page "'. $page .'" inexistante.');
		}

		$nbPerPage = 3;
		$listAdverts = $this->getDoctrine()->getManager()->getRepository('SGPlatformBundle:Advert')->getAdverts($page, $nbPerPage);

		$nbPages = ceil(count($listAdverts) / $nbPerPage);

		if ($page > $nbPages && $nbPages > 0) {
			throw $this->createNotFoundException("La page ". $page ." n'existe pas.");
		}

		return $this->render('SGPlatformBundle:Advert:index.html.twig', array(
			'listAdverts' => $listAdverts,
			'nbPages'     => $nbPages,
			'page'        => $page
			));
	}

	// @ParamConverter("advert", options={"mapping": {"advert_id": "id"}})
	// @ParamConverter("date", options={"format": "Y-m-d"})
	public function viewAction(Advert $advert){
	    $em = $this->getDoctrine()->getManager();

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

	/**
	 * @Security("has_role('ROLE_AUTEUR')")
	 */
	public function addAction(Request $request){
	    $advert = new Advert();

	    //$form = $this->get('form.factory')->create(AdvertType::class, $advert);
	    $form = $this->createForm(AdvertType::class, $advert);

		if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
			// EXEMPLE D'EVENEMENT CREER
			$event = new MessagePostEvent($advert->getContent, $advert->getUser());
			$this->get('event_dispatcher')->dispatch(PlatformEvents::POST_MESSAGE, $event);
			//$advert->setContent($event->getMessage());

			$em = $this->getDoctrine()->getManager();
			$em->persist($advert);
			$em->flush();

			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
			return $this->redirectToRoute('sg_platform_view', array('id' => $advert->getId()));
		}

		return $this->render('SGPlatformBundle:Advert:add.html.twig', array('form' => $form->createView()));
	}

	public function editAction($id, Request $request){
		$em = $this->getDoctrine()->getManager();
	    $advert = $em->getRepository('SGPlatformBundle:Advert')->find($id);

	    if ($advert === null){
	    	throw new NotFoundHttpException("L'annonce d'id ". $id ." n'existe pas.");
	    }

	    $form = $this->createForm(AdvertEditType::class, $advert);

		if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
			$em->flush();

			$request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');
			return $this->redirectToRoute('sg_platform_view', array('id' => $advert->getId()));
		}

		return $this->render('SGPlatformBundle:Advert:edit.html.twig', array('advert' => $advert, 'form' => $form->createView()));
	}

	public function deleteAction($id, Request $request){
		$em = $this->getDoctrine()->getManager();
		$advert = $em->getRepository('SGPlatformBundle:Advert')->find($id);

		if ($advert === null) {
			throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
		}

		$form = $this->get('form.factory')->create();

		if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()){
			$em->remove($advert);
			$em->flush();

			$request->getSession()->getFlashBag()->add('info', "L'annonce à bien été supprimée");
			return $this->redirectToRoute('sg_platform_home');
		}


		return $this->render('SGPlatformBundle:Advert:delete.html.twig', array('advert' => $advert, 'form' => $form->createView()));
	}

	public function menuAction($limit){
		$em = $this->getDoctrine()->getManager();
		$listAdverts = $em->getRepository('SGPlatformBundle:Advert')->findBy(array(), array('date' => 'desc'), $limit, 0);

		return $this->render('SGPlatformBundle:Advert:menu.html.twig', array('listAdverts' => $listAdverts));
	}

	public function purgeAction($days, Request $request){
		$purger = $this->container->get('sg_platform.purger.advert');
		$purger->purge($days);
		
		$request->getSession()->getFlashBag()->add('info', 'Les annonces plus vieilles que '.$days.' jours ont été purgées.');

		return $this->redirectToRoute('sg_platform_home');
	}

	public function testAction(){
		$advert = new Advert;

		$advert->setDate(new \Datetime());  // Champ « date » OK
		$advert->setTitle('abc');           // Champ « title » incorrect : moins de 10 caractères
		//$advert->setContent('blabla');    // Champ « content » incorrect : on ne le définit pas
		$advert->setAuthor('A');            // Champ « author » incorrect : moins de 2 caractères
		$advert->setEmail('yoanboit');		// Champ « email » incorrect
		// On récupère le service validator
		$validator = $this->get('validator');

		// On déclenche la validation sur notre object
		$listErrors = $validator->validate($advert);

		// Si $listErrors n'est pas vide, on affiche les erreurs
		if(count($listErrors) > 0) {
			// $listErrors est un objet, sa méthode __toString permet de lister joliement les erreurs
			return new Response((string) $listErrors);
		} else {
			return new Response("L'annonce est valide !");
		}
	}

	public function translationAction($name){
		return $this->render('SGPlatformBundle:Advert:translation.html.twig', array('name' => $name));
	}
}

?>
