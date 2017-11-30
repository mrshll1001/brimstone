<?php

// src/AppBundle/Controller/RegistrationController.php
namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends Controller
{
  /**
  * Handles set up of the initial user account
  */
  public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
  {
    // ==================================================
    /* In Brimstone there should only ever be one user.
    So perform a check to see if there's a user already. */
    // ======================================================

    $users = $this->getDoctrine()->getRepository('AppBundle:User')->findAll();  // Get The user collection.

    // Check the collection is empty
    if (empty($users))
    {
      // 0) Collection is empty, there are no users -- we can proceed to display the setup form
      // 1) build the form
      $user = new User();
      $form = $this->createForm(UserType::class, $user);

      // 2) handle the submit (will only happen on POST)
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {

        // 3) Encode the password (you could also do this via Doctrine listener)
        $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        // 4) save the User!
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // ... do any other work - like sending them an email, etc
        // maybe set a "flash" success message for the user

        return $this->redirectToRoute('index');
      }

      return $this->render(
        'AppBundle:registration:register.html.twig',
        array('form' => $form->createView())
      );
    } else
    {
      // =======================================================================
      // We have a user, so we should redirect the request appropriately
      // =======================================================================

      // Check their login status
      if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
      {
        // They're logged in, redirect them to the control panel
        return $this->redirectToRoute('control_panel');
      } else
      {
        // They're not logged in, redirect them back to the 'home' page.
        return $this->redirectToRoute('index');
      }

    }

  }
}
