<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextAreaType;



/**
 * Lightweight version of the user profile form, used after first sign-in to generate the initial version of the UserProfile object
 */
class QuickProfileType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('name', TextType::class, array(
      'label' => "What would you like as your display name?",
      'required' => true

    ))
    ->add('description', TextAreaType::class, array(
      'required' => true,
      'label' => "A quick description of you",
      'attr'=>array('class'=>'materialize-textarea', 'placeholder'=>"(max 255 characters)") // Necessary for materialize as for some reason it doesn't pick up the textareas in its css
    ));
  }
}