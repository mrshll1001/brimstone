<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;



/**
 * Lightweight version of the user profile form, used after first sign-in to generate the initial version of the UserProfile object
 */
class FullProfileType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('name', TextType::class, array(
      'label' => "What would you like as your display name?",
      'required' => true

    ))
    ->add('description', TextareaType::class, array(
      'required' => true,
      'label' => "A quick description of you",
      'attr'=>array('placeholder'=>"(max 255 characters)") // Necessary for materialize as for some reason it doesn't pick up the textareas in its css
    ))
    ->add('siteTitle', TextType::class, array(
      'required' => true,
      'label' => "What would you like the title of this site to be?"
    ))
    ->add('about', TextareaType::class, array(
      'required' => false,
      'label' => "Write at length about yourself",
      'attr'=>array('class'=>'materialize-textarea', 'placeholder'=>"A long page about you, you can even write in markdown!") // Necessary for materialize as for some reason it doesn't pick up the textareas in its css
    ));
  }
}
