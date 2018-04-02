<?php

namespace AppBundle\Service;

use AppBundle\Entity\Post;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;


/**
 * A class that handles syndication of post content to various services
 *
 */
class Syndicator
{
  protected $em;
  protected $router;

  private $requestStack;
  private $twitterKeys;

  const TWITTER_UPDATE_STATUS = "https://api.twitter.com/1.1/statuses/update.json";
  const TWITTER_STATUS_LIMIT = 280;

  public function __construct(EntityManager $em, Router $router, RequestStack $requestStack)
  {
    $this->em = $em;
    $this->router = $router;
    $this->requestStack = $requestStack;

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
    /* Retrieve access tokens */
    $settings = array(
  'oauth_access_token' => $this->twitterKeys['twitter_oauth_access_token'],
  'oauth_access_token_secret' => $this->twitterKeys['twitter_oauth_access_token_secret'],
  'consumer_key' => $this->twitterKeys['twitter_consumer_key'],
  'consumer_secret' => $this->twitterKeys['twitter_consumer_secret']
  );

    /* Instantiate twitter */
    $twitter = new \TwitterAPIExchange($settings);

    // $url = "https://api.twitter.com/1.1/statuses/update.json";
    $method = "POST";

    $status = "Please delete me ".$this->getPostUrl($post);

    $postFields = array('status' => $status);
    $twitter->buildOauth(self::TWITTER_UPDATE_STATUS, $method)->setPostfields($postFields)->performRequest();
  }

  /**=======================================================================================================
   * Generates the absolute url for sharing the post externally
   *=======================================================================================================
   */
  public function getPostUrl(Post $post)
  {
    $context = new RequestContext();
    $context->fromRequest($this->requestStack->getCurrentRequest());
    $this->router->setContext($context);

    return $this->router->generate('view_post_by_id', array('id'=>$post->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
  }
}
