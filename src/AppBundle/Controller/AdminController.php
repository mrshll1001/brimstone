<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/* Entities */
use AppBundle\Entity\User;
use AppBundle\Entity\UserProfile;
use AppBundle\Entity\Post;
use AppBundle\Entity\Feed;

/* Services */
use AppBundle\Service\Syndicator;

/* Exceptions */
use AppBundle\Exception\NullProfileException;

/* Forms */
use AppBundle\Form\QuickProfileType;
use AppBundle\Form\FullProfileType;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\SocialSettingsType;
use AppBundle\Form\QuickNoteType;
use AppBundle\Form\WritePostType;
use AppBundle\Form\UploadFileType;
use AppBundle\Form\AddRssFeedType;

/**
 * Provides controllers for Protected actions such as the control panel and creating content
 */
class AdminController extends Controller
{

  /**=======================================================================================================
   * Renders the Control Panel, incl a shortform post form
   *=======================================================================================================
   */
  public function controlPanelAction(Request $request, Syndicator $syndicator)
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

        /* TODO sort out the proper POSSE behaviour */
        $syndicator->postToTwitter($post);

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
   * Page to view all notes
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

      return $this->render('AppBundle:admin:my_notes.html.twig', array('title' => "My Notes", 'posts' => $posts));


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

        /* Redirect to my articles */
        return $this->redirectToRoute('my_articles');

      }

      return $this->render('AppBundle:admin:write_post.html.twig', array('title' => "Write Post", 'form' => $form->createView() ));

    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**=======================================================================================================
   * Edit a post via id
   *=======================================================================================================
   */
  public function editPostAction(Request $request, $id)
  {
    try
    {
      /* Perform standard checks */
      $user = $this->getUser();
      $this->checkUser($user);

      /* Retrieve post and set up form */
      $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($id);
      $this->get('fpn_tag.tag_manager')->loadTagging($post);
      $form = $this->createForm(WritePostType::class, $post);

      /* Symfony can't map the tags in the forms, so we set it manually here */
      $tags = array();
      foreach ($post->getTags() as $tag)
      {
        array_push($tags, $tag->getName());
      }
      $tagString = implode(", ", $tags);
      $form->get('tags')->setData($tagString);


      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid())
      {
        /* Create the article from the form data */
        $post = $form->getData();

        /* Generate a SLUG */
        $slug = $this->get('slugify')->slugify($post->getTitle());
        $post->setSlug($slug);

        /* We actually might have a date from the date field. If so, then we're ok but we need to check for a null value */
        if ($form['date']->getData() === null)
        {
          $post->setDate(new \DateTime());
        }

        /* Notes complain when being updated due to the unique title constraint, so manually set title as null */
        if($form['title']->getData() === null || $form['title']->getData() === "")
        {
          $post->setTitle(null);
          $post->setSlug(null);
        }

        /* Handle Tagging using the FPN Tag Manager */
        $tagManager = $this->get('fpn_tag.tag_manager');
        $tagString = $form['tags']->getData();               // Get tags data as cs-string "abc, xyz, etc"
        $tagNames = $tagManager->splitTagNames($tagString); // Use Tag manager to splt the string into separate tags
        $tags = $tagManager->loadOrCreateTags($tagNames); // Tag manager loads or creates the tag objects for us

        $tagManager->replaceTags($tags, $post);     // Replace the entire taglist

        /* Upload to the database */
        $em = $this->getDoctrine()->getManager();
        // $em->persist($post);
        $em->flush();

        /* Now that the Post is in the database, we can safely save the tagging information we've created */
        $tagManager->saveTagging($post);  // This saves the tagging info and must be called AFTER the $post has been persisted && flushed

        /* Redirect to my articles */
        return $this->redirectToRoute('my_articles');

      }

      return $this->render('AppBundle:admin:write_post.html.twig', array('title' => "Edit Post", 'form' => $form->createView() ));


    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page
    }

  }

  /**=======================================================================================================
   * Settings page. Contains a variety of forms to allow the user to modify their profile or website settings
   *=======================================================================================================
   */
  public function changePasswordAction(Request $request)
  {
    /* Perform standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

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


      return $this->render('AppBundle:admin:change_password.html.twig',
                            array('title' => "Change Password", 'changePasswordForm' => $changePasswordForm->createView()
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
  public function editProfileAction(Request $request)
  {
    /* Standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

      /* The EditProfile form operates on the user profile object, so we create the form using it */
      $form = $this->createForm(FullProfileType::class, $user->getProfile());  // Create form

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

      return $this->render('AppBundle:admin:edit_profile.html.twig', array('title' => "Edit Profile", 'form' => $form->createView() ));


    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**====================================================================================================================================
   * Page to edit their social settings e.g. for post syndication and feed reading
   * ====================================================================================================================================
   */
  public function editSocialSettingsAction(Request $request)
  {
    /* Standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

      /* Form is un-mapped to the settings object because the settings are stored in a JSOn array */
      $form = $this->createForm(SocialSettingsType::class);

      /* Because it's unmapped, we also need to manually populate the form upon loading */
      $userProfile = $user->getProfile();
      $twitterData = $userProfile->getTwitterKeys();

      if ($twitterData !== null)
      {
        $form['twitter_oauth_access_token']->setData($twitterData['twitter_oauth_access_token']);
        $form['twitter_oauth_access_token_secret']->setData($twitterData['twitter_oauth_access_token_secret']);
        $form['twitter_consumer_key']->setData($twitterData['twitter_consumer_key']);
        $form['twitter_consumer_secret']->setData($twitterData['twitter_consumer_secret']);

      }

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid())
      {
        /* Build the JSON array from the form data */
        $twitterKeys = array();

        $twitterKeys['twitter_oauth_access_token'] = $form['twitter_oauth_access_token']->getData();
        $twitterKeys['twitter_oauth_access_token_secret'] = $form['twitter_oauth_access_token_secret']->getData();
        $twitterKeys['twitter_consumer_key'] = $form['twitter_consumer_key']->getData();
        $twitterKeys['twitter_consumer_secret'] = $form['twitter_consumer_secret']->getData();

        /* Retrieve the user profile object and update */
        $userProfile->setTwitterKeys($twitterKeys);

        $em = $this->getDoctrine()->getManager();
        $em->persist($userProfile);
        $em->flush();


      }

      return $this->render('AppBundle:admin:edit_social_settings.html.twig', array('title' => "Social Settings", 'form' => $form->createView() ) );

    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**=======================================================================================================
   * Page to import and export posts
   *=======================================================================================================
   */
  public function importExportAction(Request $request)
  {
    /* Standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

      /* Reusable upload file type is used for the form */
      $importForm = $this->createForm(UploadFileType::class);

      /* Handle form submission */
      $importForm->handleRequest($request);

      if ($importForm->isSubmitted() && $importForm->isValid()) // Check submission
      {
        /* If form was submitted, we should have a valid XML file, so we parse it and begin storing posts */
        $file = $importForm['file']->getData();      //   Get file
        $xml = simplexml_load_file($file);          //    Parse it
        $em = $this->getDoctrine()->getManager();  //     Get manager to store posts

        foreach ($xml->children() as $postData)
        {
          $post = new Post();

          /* Because XML doesn't return a null value we need to cast results to desired types and check */

          if ( (string)$postData->title !== "" )
          {
            $post->setTitle((string) $postData->title);
            $slug = $this->get('slugify')->slugify($post->getTitle());
            $post->setSlug($slug);

          }

          if ( (string)$postData->content !== "" )
          {
            $post->setContent((string) $postData->content);
          }

          // TODO update the location input to match current export format
          if ((string) $postData->content === "" && (string) $postData->location !== "" )
          {
            $post->setContent("I checked in to ".(string)$postData->location);

            $location = array();
            $location['lat'] = null;
            $location['long'] = null;
            $location['location'] = (string) $postData->location;

            $post->setLocation($location);
          }

          if ( (integer)$postData->note_id > 0 )
          {
            $post->setNoteId((integer) $postData->note_id);
          }

          if ( (string)$postData->inReplyTo !== "" )
          {
            $post->setInReplyTo((string) $postData->inReplyTo);
          }

          $date = \DateTime::createFromFormat('Y-m-d\TH:i:sT', $postData->date); // Can't use 'c' format in this function for whatever reason
          $post->setDate($date);

          $post->setVisible($postData->visible);


          /* Iterate over all the tags and add them to the post */
          $tagManager = $this->get('fpn_tag.tag_manager');
          foreach ($postData->tags->children() as $tag)
          {
            if((string) $tag !== "")
            {
              $newTag = $tagManager->loadOrCreateTag($tag);
              $tagManager->addTag($newTag, $post);
            }
          }

          /* Save everything */
          $em->persist($post);
          $em->flush();

          $tagManager->saveTagging($post);

        }

      }

      return $this->render('AppBundle:admin:import_export.html.twig', array('title' => "Import and Export Posts", 'import_form' => $importForm->createView() ));

    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page
    }

  }

  /**========================================================================================================
  * Fetches all posts in an XML file based off of posts.xml.twig
  * =======================================================================================================
  */
  public function downloadPostsAction(Request $request)
  {
    try
    {
      $user = $this->getUser(); // Get user
      $this->checkUser($user); // Check them

      /* Get Posts */
      $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findAll();

      /* Load the tags */
      $tm = $this->get('fpn_tag.tag_manager');

      foreach ($posts as $post)
      {
        $tm->loadTagging($post);
      }

      /* Render the template */
      $response = $this->render('AppBundle:export:posts_export.xml.twig', array('posts' => $posts));

      /* Configure the response to return a file instead of render a page */
      $response->headers->set('Content-Type', 'application/xml');
      $response->headers->set('Content-Disposition', 'attachment; filename=brimstone_posts.xml');

      /* Return XML file */
      return $response;



    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**=======================================================================================================
  * Renders the feeds page
  * =======================================================================================================
  */
  public function feedsAction(Request $request)
  {
    /* Standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

      /* Set up the add feed form and handle the request */
      $feed = new Feed();
      $form = $this->createForm(AddRssFeedType::class, $feed);

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid())
      {
        $feed = $form->getData();

        /* We have the URL stored but it's convenient to try and parse the feed's title for the user so attempt that via feedIo */
        $feedIo = $this->get('feedio');
        $title = $feedIo->read($form['url']->getData())->getFeed()->getTitle();

        $feed->setTitle($title);

        /* We should also set the feed type and colour */
        $feed->setFormat(Feed::FORMAT_RSS);

        $feed->setRandomColour();

        /* Store the feed */
        $em = $this->getDoctrine()->getManager();
        $em->persist($feed);
        $em->flush();

        return $this->redirectToRoute('rss_feeds');
      }

      /* Retrieve all of the feeds for display */
      $feeds = $this->getDoctrine()->getRepository('AppBundle:Feed')->findAll();

      return $this->render('AppBundle:admin:feeds.html.twig', array('title' => "Feeds", 'form' => $form->createView(), 'feeds' => $feeds ));
    } catch (NullProfileException $e)
    {
      return $this->redirectToRoute('configure_initial_profile'); // Redirect to the configuration page

    }

  }

  /**=======================================================================================================
   * View an RSS feed's contents
   *=======================================================================================================
   */
  public function viewRssFeedAction(Request $request, $id)
  {
    /* Standard checks */
    try
    {
      $user = $this->getUser(); // Get the user
      $this->checkUser($user); // Check them

      /* TODO stuff with editing feed details e.g. title and colour */

      /* Load the feed via its id (it doesn't need to be overly pretty here) and display it */
      $feed = $this->getDoctrine()->getRepository('AppBundle:Feed')->find($id);

      if ($feed === null) // Redirect if we're null
      {
        return $this->redirectToRoute('rss_feeds');
      }

      /* Use the feed object to load the actual feed data fia feedIo */
      $feedIo = $this->get('feedio');
      $feedData = $feedIo->read($feed->getUrl())->getFeed();
      $title = $feedData->getTitle();

      /* Finally, render */
      return $this->render('AppBundle:admin:view_rss_feed.html.twig', array('title' => "Viewing $title", 'feed' => $feedData, 'colour' => $feed->getColour() ));

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
      return $this->redirectToRoute('edit_profile');
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
