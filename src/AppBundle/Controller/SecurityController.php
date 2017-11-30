<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
  public function loginAction(Request $request, AuthenticationUtils $authUtils)
  {

    // get the login error if there is one
    $error = $authUtils->getLastAuthenticationError();

    // last username entered by the user
    $lastUsername = $authUtils->getLastUsername();

    return $this->render('AppBundle:security:login.html.twig', array(
      'last_username' => $lastUsername,
      'error'         => $error,
    ));
  }
}
