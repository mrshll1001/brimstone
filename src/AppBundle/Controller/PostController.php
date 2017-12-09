<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBunde\Entity\Post;

/**
 * Handles actions related to individual Post objects ie swapping visibility and removing
 */
class PostController extends Controller
{

  /**===========================================================================================
   * Removes a post by its ID
   * ===========================================================================================
   */
  public function removePostByIdAction(Request $request, $id)
  {
    /* First, get the unfortunate post */
    $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($id);

    /* Remove the post using Doctrine's em */
    if ($post !== NULL) // Double check the post isn't null, just in case there's weird behaviour
    {
      $em = $this->getDoctrine()->getManager(); // Get the manager
      $em->remove($post);                       // Remove the entity
      $em->flush();                             // Finish
    }

    /* Redirect over to the Articles table TODO handle a submission wherein the routing information is hidden in the form for redirect */
    return $this->redirectToRoute('my_articles');

  }

}
