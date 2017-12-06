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
    public function indexAction(Request $request)
    {
      /* First, try to load the user object */
      $user = $this->getDoctrine()->getRepository('AppBundle:User')->getSingleUser();

      /* If the user object is null, then Brimstone hasn't been set up, so load the template that says so */
      if ($user === null)
      {
        return $this->render('AppBundle:public:not_setup.html.twig', array());
      }

      /* Sweet, we can load the page */
      $userProfile = $user->getProfile();     // Posts are loaded separately, so we only need to pass in the user profile for the navbar.


        return $this->render('AppBundle:public:index.html.twig', array('profile' => $userProfile));
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
}
