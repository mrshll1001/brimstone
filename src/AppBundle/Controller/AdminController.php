<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/* Entities */
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;

/* Exceptions */
use AppBundle\Exception\NullProfileException;

/* Forms */
use AppBundle\Form\QuickProfileType;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\WriteAboutType;

/**
 * Provides controllers for Protected actions such as the control panel and creating content
 */
class AdminController extends Controller
{

  /**=======================================================================================================
   * Renders the Control Panel
   *=======================================================================================================
   */
  public function controlPanelAction(Request $request)
  {

    /* try to execute the code, but if there's no profile yet we need to set it up */
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

  /**=======================================================================================================
   * Settings page. Contains a variety of forms to allow the user to modify their profile or website settings
   *=======================================================================================================
   */
  public function userSettingsAction(Request $request)
  {
    /* Perform standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

      /* We need multiple forms on this page so we're going to handle them each in turn */

      /* First, the QuickProfile form to let the user update their profile information */
      $quickProfileForm = $this->createForm(QuickProfileType::class, $user->getProfile()); // Create the form with the profile object on the user

      $quickProfileForm->handleRequest($request); // Handle the request here first

      if ($quickProfileForm->isSubmitted() && $quickProfileForm->isValid())
      {
        $user->setProfile($quickProfileForm->getData()); // Update the object
        $em = $this->getDoctrine()->getManager();       // Get the Doctrine EM
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('user_settings'); // Return to the settings page
      }


      /* Now we handle the change password form */
      $changePasswordForm = $this->createForm(ChangePasswordType::class); // We don't give this a user object as we just want the password information

      $changePasswordForm->handleRequest($request); // HAndle the request

      if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid())
      {
        $plainPassword = $changePasswordForm['plainPassword']->getData();               // Retrieve plain password
        $encodedPassword = $this->get('security.password_encoder')
                                ->encodePassword($user, $plainPassword);         // Generate a per-user salt


        $em = $this->getDoctrine()->getManager();        // Get doctrine
        $user->setPassword($encodedPassword);           // Set the encoded password
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('logout');  // Just for safety, redirect to logout
      }


      return $this->render('AppBundle:admin:user_settings.html.twig',
                            array('quickProfileForm' => $quickProfileForm->createView(),
                            'changePasswordForm' => $changePasswordForm->createView()
                          ));

    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**====================================================================================================================================
   * Provides a page / form to allow the user to write their 'about' section. Once their 'About' isn't null, the about link shows up on the public side of teh site
   * ====================================================================================================================================
   */
  public function writeAboutAction(Request $request)
  {
    /* Standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

      /* The WriteAbout form operates on the user profile object, so we create the form using it */
      $form = $this->createForm(WriteAboutType::class, $user->getProfile());  // Create form

      /* Handle request and check if the form was submitted */
      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid())
      {
        // TODO store stuff

        /* We don't need to redirect because we don't need an empty form AND don't need to change the page */
      }

      return $this->render('AppBundle:admin:write_about.html.twig', array('form' => $form->createView() ));


    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**=======================================================================================================
   * After logging in for the first time users should be directed here to initialise their UserProfile object
   *=======================================================================================================
   */
  public function configureInitialProfileAction(Request $request)
  {
    /* First perform a check to see if they've actually got a profile. If they have, redirect to the settings page */
    if (!is_null($this->getUser()->getProfile()))
    {
      return $this->redirectToRoute('user_settings');
    }


    /* Build the form around a new UserProfile object */
    $userProfile = new UserProfile();
    $form = $this->createForm(QuickProfileType::class, $userProfile);

    /* Handle form submission */
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) // Check the form was submitted
    {
      /* If form is submitted, store the user profile object and associate it with the user */

      $userProfile = $form->getData();                 // Acquire the data from the form
      $em = $this->getDoctrine()->getManager();       // Get the EM from Doctrine

      $user = $this->getUser();                     // Get the user

      $em->persist($userProfile);                 // Persist it
      $user->setProfile($userProfile);           // Associate it with teh user
      $em->persist($user);                      // Persist the user just-in-caase
      $em->flush();                            // Flush


      /* Finally, redirect to the control panel page */
      return $this->redirectToRoute('control_panel');
    }

    // Form handling
    return $this->render('AppBundle:admin:configure_initial_profile.html.twig', array('form' => $form->createView() ));
  }


  /**=======================================================================================================
  * Shortcut class for returning the user
  * @return Usser object
  *=======================================================================================================
  */
  protected function getUser()
  {
    return $this->get('security.token_storage')->getToken()->getUser();
  }

  /**=======================================================================================================
   * Performs various checks on the user and redirects appropriately based on the resulting conditions
   *=======================================================================================================
   */
  protected function checkUser(User $user)
  {
    /* If user's UserProfile object is null, redirect them to the Configuration */
    if (is_null($user->getProfile()))
    {
      throw new NullProfileException();
    }

  }
}
