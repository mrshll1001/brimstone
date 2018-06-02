<?php

namespace AppBundle\Service;

use AppBundle\Entity\Post;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;

use GuzzleHttp\Client;
use Revolution\Mastodon\MastodonClient;


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
  private $mastodonKeys;

  const TWITTER_UPDATE_STATUS = "https://api.twitter.com/1.1/statuses/update.json";
  const TWITTER_STATUS_LIMIT = 280;

  const MASTODON_STATUS_LIMIT = 500;

  public function __construct(EntityManager $em, Router $router, RequestStack $requestStack)
  {
    $this->em = $em;
    $this->router = $router;
    $this->requestStack = $requestStack;

  }

  /**=======================================================================================================
   * Sends a single post to a Mastodon instance
   *=======================================================================================================
   */
  public function postToMastodon(Post $post)
  {
    /* Check Mastodon keys and set up if null */
    if ($this->mastodonKeys === NULL)
    {
      $this->setupMastodonKeys();
    }

    /* TODO generate status content */
    $status = $this->getPostAsStatus($post, self::MASTODON_STATUS_LIMIT);

    /* Set up Mastodon client and toot */
    $mastodon = new MastodonClient(new Client());
    $mastodon->domain($this->mastodonKeys['mastodon_domain'])
             ->token($this->mastodonKeys['mastodon_access_token'])
             ->createStatus($status);

  }

  /**=======================================================================================================
   * Sends a single post to Twitter
   *=======================================================================================================
   */
  public function postToTwitter(Post $post)
  {

    /* Check twitter keys and set up if null */
    if ($this->twitterKeys === NULL)
    {
      $this->setupTwitterKeys();
    }


    /* Retrieve access tokens */
    $settings = array(
  'oauth_access_token' => $this->twitterKeys['twitter_oauth_access_token'],
  'oauth_access_token_secret' => $this->twitterKeys['twitter_oauth_access_token_secret'],
  'consumer_key' => $this->twitterKeys['twitter_consumer_key'],
  'consumer_secret' => $this->twitterKeys['twitter_consumer_secret']
  );

    /* Instantiate twitter */
    $twitter = new \TwitterAPIExchange($settings);
    $method = "POST";

    /* Retrieve status */
    $status = $this->getPostAsStatus($post, self::TWITTER_STATUS_LIMIT);

    /* TODO check if $status is an array, and make a chained series of posts if it is. */

    /* Post status */
    $postFields = array('status' => $status);
    $twitter->buildOauth(self::TWITTER_UPDATE_STATUS, $method)->setPostfields($postFields)->performRequest();
  }

  /**=======================================================================================================
   * Transforms post into status based on rules so will appear differently in tweets etc.
   *=======================================================================================================
   */
  private function getPostAsStatus(Post $post, int $charLimit = 0)
  {
    /* If post is an article, can just return the title and the permalink */
    if ($post->getTitle() !== NULL)
    {
      return $post->getTitle()." ".$this->getPostUrl($post);
    }

    // TODO if post content > charLimit for status, split into array

    return $post->getContent()." ".$this->getPostUrl($post);


  }

  /**=======================================================================================================
   * Generates the absolute url for sharing the post externally
   *=======================================================================================================
   */
  private function getPostUrl(Post $post)
  {
    $context = new RequestContext();
    $context->fromRequest($this->requestStack->getCurrentRequest());
    $this->router->setContext($context);

    return $this->router->generate('view_post_by_id_short', array('id'=>$post->getId()), UrlGeneratorInterface::ABSOLUTE_URL);
  }

  /**=======================================================================================================
   * Sets the Syndicator's Twitter keys for sharing posts
   *=======================================================================================================
   */
  private function setupTwitterKeys()
  {
    $user = $this->em->getRepository('AppBundle:User')->getSingleUser();
    $this->twitterKeys = $user->getProfile()->getTwitterKeys();
  }

  private function setupMastodonKeys()
  {
    $user = $this->em->getRepository('AppBundle:User')->getSingleUser();
    $this->mastodonKeys = $user->getProfile()->getMastodonKeys();
  }
}
