<?php

namespace SG\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use SG\PlatformBundle\Form\ImageType;
use SG\PlatformBundle\Form\CategoryType;
use SG\PlatformBundle\Repository\CategoryRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AdvertType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$pattern = 'D%';

        $builder
        	->add('date',		DateTimeType::class)
	    	->add('title',		TextType::class)
	    	->add('author',		TextType::class)
	    	->add('content',	TextareaType::class)
	    	->add('email',		EmailType::class)
	    	->add('image',		ImageType::class)
	    	->add('categories', EntityType::class, array(
	    			'class'			=> 'SGPlatformBundle:Category',
	    			'choice_label'	=> 'display',
	    			'multiple'		=> true,
	    			'expanded'		=> false,
	    			'query_builder'	=> function(CategoryRepository $repository) use($pattern){
	    				return $repository->getLikeQueryBuilder($pattern);
	    			}))
	    	->add('save',		SubmitType::class)
	    	/*
	    	->add('published',	CheckboxType::class, array('required' => false))
	    	->add('categories',	CollectionType::class, array(
	    			'entry_type' 	=> CategoryType::class,
	    			'allow_add'	 	=> true,
	    			'allow_delete'	=> true))
	    	*/
	    ;

	    $builder->addEventlistener(
	    	FormEvents::PRE_SET_DATA,
	    	function(FormEvent $event){
	    		$advert = $event->getData();
	    		if($advert === null) return;

	    		if (!$advert->getPublished() || $advert->getId() === null){
	    			$event->getForm()->add('published', CheckboxType::class, array('required' => false));
	    		} else {
	    			$event->getForm()->remove('published');
	    		}
	    	}
	    );
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SG\PlatformBundle\Entity\Advert'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sg_platformbundle_advert';
    }


}
