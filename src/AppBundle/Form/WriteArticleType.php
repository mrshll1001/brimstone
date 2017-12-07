<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;



/**
 * Allows the user to write their about page
 */
class WriteArticleType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('title', TextType::class, array(
                              'required' => true,

    ))
    ->add('date', DateType::class, array(
      'label' => "Do you want to customise the date? Leave blank to just use now",
      'required' => false,
      'widget'=>'single_text',
      'attr'=> array('class' => 'datepicker') // this gets picked up by the browser's date / calendar thing
    ))
    ->add('content', TextareaType::class, array(
                      'required' => true,
                      'label' => "Content",
                      'attr'=>array('class'=>'materialize-textarea', 'placeholder'=>"What's on your mind?") // Necessary for materialize as for some reason it doesn't pick up the textareas in its css
    ));
  }
}
