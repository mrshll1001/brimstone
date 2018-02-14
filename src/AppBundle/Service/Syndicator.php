<?php

namespace AppBundle\Service;

use AppBundle\Entity\Post;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * A class that handles syndication of post content to various services
 *
 */
class Syndicator
{
  protected $em;
  protected $router;

  private $twitterKeys;

  public function __construct(EntityManager $em, Router $router)
  {
    $this->em = $em;
    $this->router = $router;

    /* Set up twitter keys for the session, if using */
    $user = $em->getRepository('AppBundle:User')->getSingleUser();
    $this->twitterKeys = $user->getProfile()->getTwitterKeys();

  }

  /**=======================================================================================================
   * Sends a single post to Twitter
   *=======================================================================================================
   */
  public function postToTwitter(Post $post)
  {
    $settings = array(
  'oauth_access_token' => $this->twitterKeys['twitter_oauth_access_token'],
  'oauth_access_token_secret' => $this->twitterKeys['twitter_oauth_access_token_secret'],
  'consumer_key' => $this->twitterKeys['twitter_consumer_key'],
  'consumer_secret' => $this->twitterKeys['twitter_consumer_secret']
  );


    $twitter = new \TwitterAPIExchange($settings);

    $url = "https://api.twitter.com/1.1/statuses/update.json";
    $method = "POST";

    $status = "Please delete me ".$this->router->generate('view_post_by_id', array('id'=>$post->getId(), true ));

    $postFields = array('status' => $status);
    $twitter->buildOauth($url, $method)->setPostfields($postFields)->performRequest();
  }
}
