<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/* Entities */
use AppBundle\Entity\User;

/* Exceptions */
use AppBundle\Exception\NullProfileException;

/**
 * Provides controllers for Protected actions such as the control panel and creating content
 */
class AdminController extends Controller
{

  /**
   * Renders the Control Panel
   */
  public function controlPanelAction(Request $request)
  {

    /* try to execute the code, but there's no profile yet we need to set it up */
    try
    {
      $user = $this->getUser(); // Get the User
      $this->checkUser($user);  // Check stuff about them


      return $this->render('AppBundle:admin:control_panel.html.twig', array());

    } catch (NullProfileException $e)
    {

      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page
    }




  }

  /**
   * After logging in for the first time users should be directed here to initialise their UserProfile object
   */
  public function configureInitialProfileAction(Request $request)
  {
    return $this->render('AppBundle:admin:configure_initial_profile.html.twig', array());
  }


  /**
  * Shortcut class for returning the user
  * @return Usser object
  */
  protected function getUser()
  {
    return $this->get('security.token_storage')->getToken()->getUser();
  }

  /**
   * Performs various checks on the user and redirects appropriately based on the resulting conditions
   */
  protected function checkUser(User $user)
  {
    echo $user->getProfile();
    /* If user's UserProfile object is null, redirect them to the Configuration */
    if (is_null($user->getProfile()))
    {
      throw new NullProfileException();
    }

  }
}
