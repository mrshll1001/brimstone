<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;




/**
 * Lightweight version of the user profile form, used after first sign-in to generate the initial version of the UserProfile object
 */
class AddRssFeedType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('url', TextType::class, array(
      'label' => "Enter a URL to add a new feed",
      'required' => true
    ));
  }
}
