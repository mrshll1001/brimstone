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

    /* Redirect based on the form's origin */
    $redirectRoute = $request->get('redirect_route');
    return $this->redirectToRoute($redirectRoute);

  }

  /**===========================================================================================
   * Inverts a posts visibility
   * ===========================================================================================
   */
  public function changePostVisibilityByIdAction(Request $request, $id)
  {
    /* Get the post */
    $post = $this->getDoctrine()->getRepository('AppBundle:Post')->find($id);

    /* Update the post's visibility */
    if ($post !== NULL) // Double check the post isn't null, just in case there's weird behaviour
    {
      $post->setVisible( !$post->getVisible() ); // Invert using boolean

      $em = $this->getDoctrine()->getManager(); // Get the manager
      $em->flush();                             // Finish, since Doctrine has it stored already we're good to go
    }

    /* Redirect based on the form's origin */
    $redirectRoute = $request->get('redirect_route');
    return $this->redirectToRoute($redirectRoute);


  }

}
