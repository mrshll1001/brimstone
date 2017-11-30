<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides controllers for Protected actions such as the control panel and creating content
 */
class AdminController extends Controller
{

  /**
   * Renders the Control Panel
   */
  public function controlPanelAction(Request $request)
  {
    return $this->render('AppBundle:admin:control_panel.html.twig', array());
  }
}
