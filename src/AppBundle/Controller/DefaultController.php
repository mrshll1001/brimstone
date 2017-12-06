<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Default page
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
}
