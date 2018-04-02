<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;




/**
 * Allows the user to write their about page
 */
class WritePostType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $profile = $options['profile'];


    $builder->add('title', TextType::class, array(
                              'required' => false,
                              'label' => "Title (Leave blank to treat this as a Note)"

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
    ))
    ->add('tags', TextType::class, array(
        'required' => false,
        'mapped' => false,
        'label' => "Enter some tags, separated by spaces e.g. one, two, three"));


        /* If the user has entered api keys, dynamically add extra fields to posse */
        if ($profile->getTwitterKeys() !== NULL)
        {
            $twitterData = $profile->getTwitterKeys();
            if ( array_key_exists('twitter_oauth_access_token', $twitterData) &&
                 array_key_exists('twitter_oauth_access_token_secret', $twitterData) &&
                 array_key_exists('twitter_consumer_key', $twitterData) &&
                 array_key_exists('twitter_consumer_secret', $twitterData) )
                 {
                   if ( $twitterData['twitter_oauth_access_token'] !== NULL &&
                        $twitterData['twitter_oauth_access_token_secret'] !== NULL &&
                        $twitterData['twitter_consumer_key'] !== NULL &&
                        $twitterData['twitter_consumer_secret'] !== NULL
                        )
                   {
                     $builder->add('twitter', CheckboxType::class, array(
                       'label' => "Twitter",
                        'mapped' => false,
                        'required' => false
                      ));
                   }
                 }
        }
  }

  /**
     * Options resolver http://stackoverflow.com/questions/43092246/symfony-3-passing-variables-into-forms/43092919#43092919
     */
    public function configureOptions(OptionsResolver $resolver)
    {
      $resolver->setRequired('profile');
      $resolver->setAllowedTypes('profile', array(UserProfile::class));
    }

}
