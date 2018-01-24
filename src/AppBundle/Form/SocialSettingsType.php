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
class SocialSettingsType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('twitter_oauth_access_token', TextType::class, array(
      'label' => "oAuth Access Token",
      'required' => false
    ))
    ->add('twitter_oauth_access_token_secret', TextType::class, array(
      'label' => 'oAuth Access Token Secret',
      'required' => false
    ))
    ->add('twitter_consumer_key', TextType::class, array(
      'label' => 'Consumer Key',
      'required' => false
    ))
    ->add('twitter_consumer_secret', TextType::class, array(
      'label' => 'Consumer Secret',
      'required' => false
    ));
  }
}
