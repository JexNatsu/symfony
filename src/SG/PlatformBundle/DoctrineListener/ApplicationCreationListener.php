<?php
namespace SG\PlatformBundle\DoctrineListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use SG\PlatformBundle\Email\ApplicationMailer;
use SG\PlatformBundle\Entity\Application;

class ApplicationCreationListener{
    /**
    * @var ApplicationMailer
    */
    private $applicationMailer;

    public function __construct(ApplicationMailer $applicationMailer){
        $this->applicationMailer = $applicationMailer;
    }

    public function postPersist(LifecycleEventArgs $args){
        try{
            $entity = $args->getObject();

            // On ne veut envoyer un email que pour les entités Application
            if (!$entity instanceof Application) {
                return;
            }

            $this->applicationMailer->sendNewNotification($entity);
        } catch(\Exception $e){
            
        }
    }
}
