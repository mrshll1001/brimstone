<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;



/**
 * Allows the user to write their about page
 */
class WriteAboutType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('about', TextareaType::class, array(
      'required' => false,
      'label' => "About",
      'attr'=>array('class'=>'materialize-textarea', 'placeholder'=>"A long page about you, you can even write in markdown!") // Necessary for materialize as for some reason it doesn't pick up the textareas in its css
    ));
  }
}
