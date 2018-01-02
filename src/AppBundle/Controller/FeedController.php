<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBunde\Entity\Post;

/**
 * Handles actions related to individual Feed objects ie deleting
 */
class FeedController extends Controller
{

  /**===========================================================================================
   * Removes a feed by its ID
   * ===========================================================================================
   */
  public function removeFeedByIdAction(Request $request, $id)
  {
    /* First, get the unfortunate post */
    $feed = $this->getDoctrine()->getRepository('AppBundle:Feed')->find($id);

    /* Remove the post using Doctrine's em */
    if ($feed !== NULL) // Double check the post isn't null, just in case there's weird behaviour
    {
      $em = $this->getDoctrine()->getManager(); // Get the manager
      $em->remove($feed);                       // Remove the entity
      $em->flush();                             // Finish
    }

    /* Redirect based on the form's origin */
    $redirectRoute = $request->get('redirect_route');
    return $this->redirectToRoute($redirectRoute);

  }


}
