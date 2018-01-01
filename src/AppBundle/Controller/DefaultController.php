<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**===========================================================================================
     * Home page
     * ===========================================================================================
     */
    public function indexAction(Request $request, $year = null, $month = null)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null || $user->getProfile() === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Sweet, we can load the page */
      $userProfile = $user->getProfile();     // Posts are loaded separately, so we only need to pass in the user profile for the navbar.

      /* Load all the posts via year and month -- if they're null all posts for this month are returned */
      $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findByYearAndMonth($year, $month);

      /* Sort the tags out */
      foreach ($posts as $post)
      {
        $this->get('fpn_tag.tag_manager')->loadTagging($post);
      }

      /* Generate the dates for current and last months for linking */
      $dates = $this->generateDatesArray($year, $month);

      return $this->render('AppBundle:public:index.html.twig', array('profile' => $userProfile, 'dates' => $dates, 'posts' => $posts));
    }

    /**===========================================================================================
     * About page -- should redirect to home if user->profile->about === null
     * ===========================================================================================
     */
    public function aboutAction(Request $request)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Check if the user profile is null, if it is then they probably don't want to show a blank page so redirect */
      if ($user->getProfile()->getAbout() === null)
      {
        return $this->redirectToRoute('index');
      }

      /* We can load the page, so we can just get the profile object which contains about anyway */
      return $this->render('AppBundle:public:about.html.twig', array('profile' => $user->getProfile() ));
    }

    /**===========================================================================================
     * 'Blog' page, lists only Posts which have a title
     * ===========================================================================================
     */
    public function listArticlesAction(Request $request)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Load the articles and their tags */
      $posts = $this->getDoctrine()->getRepository('AppBundle:Post')->findAllArticles(true);  // Pass in true to restrict to visible articles
      $tagManager = $this->get('fpn_tag.tag_manager');

      foreach ($posts as $post)
      {
        $tagManager->loadTagging($post);
      }

      /* We can load the page yay */
      return $this->render('AppBundle:public:list_articles.html.twig', array('profile' => $user->getProfile(), 'posts' => $posts ));
    }

    /**===========================================================================================
     * Viewing a single article
     * ===========================================================================================
     */
    public function viewArticleAction(Request $request, $slug)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Load the article via the slug */
      $post = $this->getDoctrine()->getRepository('AppBundle:Post')->findOneBySlug($slug);

      /* Check the post isn't null or invisible. */
      if ($post === NULL || $post->getVisible() === false)
      {
        return $this->redirectToRoute('list_articles'); // Returning to /blog sounds sensible for a bad blog url or secret post
      }

      /* Load the tags on the post object */
      $tagManager = $this->get('fpn_tag.tag_manager');
      $tagManager->loadTagging($post);

      /* We can load the page yay */
      return $this->render('AppBundle:public:view_article.html.twig', array('profile' => $user->getProfile(), 'post' => $post ));
    }

    /**===========================================================================================
     * View a single post via its id
     * ===========================================================================================
     */
    public function viewPostByIdAction(Request $request, $id)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Load the article via id */
      $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($id);

      /* Check the post isn't null or invisible. */
      if ($post === NULL || $post->getVisible() === false)
      {
        return $this->redirectToRoute('index'); // Returning to / sounds sensible for a bad id or secret post TODO maybe throw a 404
      }

      /* Load the previous and the next posts */
      $previous = $this->getDoctrine()->getRepository('AppBundle:Post')->findPreviousPost($post->getDate()); // Previous by date !id
      $next = $this->getDoctrine()->getRepository('AppBundle:Post')->findNextPost($post->getDate());

      /* Load the tags on the post object */
      $tagManager = $this->get('fpn_tag.tag_manager');
      $tagManager->loadTagging($post);

      return $this->render('AppBundle:public:view_post.html.twig', array('profile' => $user->getProfile(), 'post' => $post, 'previous' => $previous, 'next' => $next ));

    }

    /**===========================================================================================
    * Because there's old URLs out there with the /note/{id} links, handle redirect here
    * ===========================================================================================
    */
    public function viewNoteByIdAction(Request $request, $id)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Load the post via the note id, extract the new id and redirect */
      $post = $this->getDoctrine()->getRepository('AppBundle:Post')->findOneByNoteId($id);

      if ($post === NULL)
      {
        return $this->redirectToRoute('index'); // Returning to / sounds sensible for a bad id
      }

      return $this->redirectToRoute('view_post_by_id', array('id'=>$post->getId() ));

    }

    /**===========================================================================================
    * Displays all tags available in the system, and if a tagstring is present all posts for that tag
    * ===========================================================================================
    */
    public function viewTagsAction(Request $request, $tagString = null)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Get all the tags with the count */
      $tags = $this->getDoctrine()->getRepository('AppBundle:Tag')->getTagsWithCountArray('post');

      /* If the tagstring isn't null, we have a search so do a search for all posts for those tags */
      $posts = null;
      if ($tagString !== null) // Check for a tag query
      {
        $posts = array(); // Initialise posts, it'll now be picked up by the template
        $ids = array();   // Taggable provides an interface that returns ids, so we need to collect these

        foreach (explode('+', $tagString) as $tString) // Explode the tagstring and loop
        {
          $results = $this->getDoctrine()->getRepository('AppBundle:Tag')->getResourceIdsForTag('post', $tString); // Get the post ids for the tag

          $ids = array_merge($ids, $results); // Merge the results in
        }

        $ids = array_unique($ids);  // Purge duplicates

        foreach ($ids as $id) // Loop over ids, adding the results to the posts array
        {

          $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($id);
          if ($post)
          {
            $this->get('fpn_tag.tag_manager')->loadTagging($post);  // Load tags for display
            $posts[] = $post; // Add to array
          }
        }

      }

      return $this->render('AppBundle:public:tags.html.twig', array('profile' => $user->getProfile(), 'tags' => $tags, 'search' => $tagString, 'posts' => $posts ));
    }

    /**===========================================================================================
     * Returns the date objects utilised by the index pages for back and forward links
     * ===========================================================================================
     */
    protected function generateDatesArray($year, $month)
    {
      $dates = array();

      if ($year === null && $month === null)  // If they're null, then we can just use this month
      {
        $dates['current'] = new \DateTime();

        $dates['nextMonth'] = new \DateTime();
        $dates['nextMonth']->modify('+1 month');

        $dates['lastMonth'] = new \DateTime();
        $dates['lastMonth']->modify('-1 month');
      } else
      {
        $dates['current'] = \DateTime::createFromFormat('d-n-Y', "01-".$month."-".$year);

        $dates['nextMonth'] = \DateTime::createFromFormat('d-n-Y', "01-".$month."-".$year);
        $dates['nextMonth']->modify('+1 month');

        $dates['lastMonth'] = \DateTime::createFromFormat('d-n-Y', "01-".$month."-".$year);
        $dates['lastMonth']->modify('-1 month');
      }

      return $dates;
    }
}
