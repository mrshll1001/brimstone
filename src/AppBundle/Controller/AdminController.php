<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/* Entities */
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use AppBundle\Entity\Post;

/* Exceptions */
use AppBundle\Exception\NullProfileException;

/* Forms */
use AppBundle\Form\QuickProfileType;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\WriteAboutType;
use AppBundle\Form\QuickNoteType;
use AppBundle\Form\WritePostType;

/**
 * Provides controllers for Protected actions such as the control panel and creating content
 */
class AdminController extends Controller
{

  /**=======================================================================================================
   * Renders the Control Panel, incl a shortform post form
   *=======================================================================================================
   */
  public function controlPanelAction(Request $request)
  {

    /* try to execute the code, but if there's no profile yet we need to set it up */
    try
    {
      $user = $this->getUser(); // Get the User
      $this->checkUser($user);  // Check stuff about them

      /* We have a form on the page to allow the user to quickly post some content, so let's render that */
      $post = new Post(); // We're basing it around a NEW post
      $form = $this->createForm(QuickNoteType::class, $post);

      /* Check form submission */
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid())
      {
        $post = $form->getData(); // Retrieve the data ffrom the form

        /* We're actually still missing some compulsory data like dates etc. So let's add them here */

        $post->setDate(new \DateTime());  // Date wasn't given, so we can default to now.
        $post->setVisible(true);         // TODO this is actually a bit presumptious, when the form develops further we should check this

        /* Handle Tagging using the FPN Tag Manager */
        $tagManager = $this->get('fpn_tag.tag_manager');
        $tagString = $form['tags']->getData();  // Get tags data as cs-string "abc, xyz, etc"
        $tagNames = $tagManager->splitTagNames($tagString); // Use Tag manager to splt the string into separate tags
        $tags = $tagManager->loadOrCreateTags($tagNames); // Tag manager loads or creates the tag objects for us

        $tagManager->addTags($tags, $post); // Get the tag manager to associate the tags with the post

        /* We've handled the post now so we should be ok to save it */
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();

        /* Now that the Post is in the database, we can safely save the tagging information we've created */
        $tagManager->saveTagging($post);  // This saves the tagging info and must be called AFTER the $post has been persisted && flushed


        /* If we're successful, we should probably want to redirect to a nice clean form */
        return $this->redirectToRoute('control_panel');
      }

      return $this->render('AppBundle:admin:control_panel.html.twig', array('title' => "Control Panel", 'form' => $form->createView() ));

    } catch (NullProfileException $e)
    {

      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page
    }

  }

  /**=======================================================================================================
   * Page to view all posts
   *=======================================================================================================
   */
  public function myNotesAction(Request $request)
  {
    try
    {
      /* Check profile */
      $user = $this->getUser();
      $this->checkUser($user);

      /* Load all the notes and load the tagging info for each of them*/
      $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findAllNotes();

      $tagManager = $this->get('fpn_tag.tag_manager');
      foreach ($posts as $p)
      {
        $tagManager->loadTagging($p);
      }

      return $this->render('AppBundle:admin:my_posts.html.twig', array('title' => "My Notes", 'posts' => $posts));


    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**=======================================================================================================
   * Table of the user's articles
   *=======================================================================================================
   */
  public function myArticlesAction(Request $request)
  {
    try
    {
      /* Perform the standard checks */
      $user = $this->getUser();
      $this->checkUser($user);

      /* TODO load all the articles */
      $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findAllArticles();

      return $this->render('AppBundle:admin:my_articles.html.twig', array('title' => "My Articles", 'posts' => $posts));

    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page
    }

  }

  /**=======================================================================================================
   * Page to write a full post / article
   *=======================================================================================================
   */
  public function writeArticleAction(Request $request)
  {
    try
    {
      /* Perform standard checks */
      $user = $this->getUser();
      $this->checkUser($user);

      /* Create the form and handle the submission */
      $post = new Post(); // Articles are based off of a new post object
      $form = $this->createForm(WritePostType::class, $post);

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid())
      {
        /* Create the article from the form data */
        $post = $form->getData();

        /* Remember to set the compulsory things */
        $post->setVisible(true);

        /* Generate a SLUG */
        $slug = $this->get('slugify')->slugify($post->getTitle());
        $post->setSlug($slug);

        /* We actually might have a date from the date field. If so, then we're ok but we need to check for a null value */
        if ($form['date']->getData() === null)
        {
          $post->setDate(new \DateTime());
        }

        /* Handle Tagging using the FPN Tag Manager */
        $tagManager = $this->get('fpn_tag.tag_manager');
        $tagString = $form['tags']->getData();  // Get tags data as cs-string "abc, xyz, etc"
        $tagNames = $tagManager->splitTagNames($tagString); // Use Tag manager to splt the string into separate tags
        $tags = $tagManager->loadOrCreateTags($tagNames); // Tag manager loads or creates the tag objects for us

        $tagManager->addTags($tags, $post); // Get the tag manager to associate the tags with the post

        /* Upload to the database */
        $em = $this->getDoctrine()->getManager();
        $em->persist($post);
        $em->flush();

        /* Now that the Post is in the database, we can safely save the tagging information we've created */
        $tagManager->saveTagging($post);  // This saves the tagging info and must be called AFTER the $post has been persisted && flushed

        /* Redirect here for now, but TODO make a posts table to show the user they were successful */
        return $this->redirectToRoute('write_article');

      }

      return $this->render('AppBundle:admin:write_post.html.twig', array('title' => "Write Post", 'form' => $form->createView() ));

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
                            array('title' => "Settings", 'quickProfileForm' => $quickProfileForm->createView(),
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
        /* Set the user's profile using the form data */
        $user->setProfile($form->getData());

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        /* We don't need to redirect because we don't need an empty form AND don't need to change the page */
      }

      return $this->render('AppBundle:admin:write_about.html.twig', array('title' => "About", 'form' => $form->createView() ));


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
