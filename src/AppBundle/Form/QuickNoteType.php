<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;



/**
 * Quick form for allowing the user to write a short-form post w/o a title (ie a note)
 */
class QuickNoteType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('content', TextareaType::class, array(
      'required' => true,
      'label' => "Post",
      'attr'=>array('class'=>'materialize-textarea', 'placeholder'=>"What's on your mind?") // Necessary for materialize as for some reason it doesn't pick up the textareas in its css
    ))
    ->add('tags', TextType::class, array(
        'required' => false,
        'mapped' => false,
        'label' => "Enter some tags, separated by spaces e.g. one, two, three"));
  }
}
