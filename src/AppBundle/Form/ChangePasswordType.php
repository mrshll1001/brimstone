<?php

namespace AppBundle\Form;

use AppBundle\Entity\UserProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;



/**
 * Lightweight form to let the user change their password
 */
class ChangePasswordType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder->add('plainPassword', RepeatedType::class, array(
        'type' => PasswordType::class,
        'first_options'  => array('label' => 'Password'),
        'second_options' => array('label' => 'Repeat Password'),
    ))
;
  }
}
