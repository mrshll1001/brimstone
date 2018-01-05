<?php

namespace AppBundle\Service;

use FeedIo\Feed as RSSFeed;
use FeedIo\Feed\Item;
use AppBundle\Entity\Post;
use Debril\RssAtomBundle\Provider\FeedContentProviderInterface;
use AppBundle\Repository\PostRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;



/**
* Provides an RSS feed based on the site's articles (posts w/ titles)
*/
class ArticleRssFeedProvider implements FeedContentProviderInterface
{
  protected $em;
  protected $router;

  const TITLE_SUFFIX = "'s Articles";
  const ARTICLE_LIMIT = 20;
  const ITEM_DESCRIPTION_PREFIX = "Article posted at ";

  public function __construct(EntityManager $em, Router $router)
  {
    $this->em = $em;
    $this->router = $router;
  }

  public function getFeedContent(array $options)
  {
    /* Declare a new RSSFeed object, and set the basic information using user profile */
    // TODO handle null profiles, and return a blank feed..

    $rssFeed = new RSSFeed();

    $name = $this->em->getRepository('AppBundle:User')->getSingleUser()->getProfile()->getName();
    $rssFeed->setTitle($name.ArticleRssFeedProvider::TITLE_SUFFIX);

    $description = $this->em->getRepository('AppBundle:User')->getSingleUser()->getProfile()->getDescription();
    $rssFeed->setDescription($description);

    $rssFeed->setLink($this->router->generate('index'));  // Use the routing component to get the URL

    /* Use the EM to retrieve latest 20 articles and use these as the feed items */
    $posts = $this->em->getRepository('AppBundle:Post')->findAllArticles(true); // Only retrieve visible articles

    foreach ($posts as $post)
    {
      $rssFeed->add( $this->postToRssItem($post) );
    }

    /* Use the last article as the last modified date */
    $rssFeed->setLastModified($posts[0]->getDate());

    return $rssFeed;
  }

  /**
  * Takes a post and returns it as an RSS item
  * @param Post $post
  * @return Item $item
  */
  protected function postToRssItem(Post $post)
  {
    $item = new Item();

    /* Use the post data to fill out the RSS Item fields */
    $item->setPublicId($post->getId());
    $item->setLink($this->router->generate('view_post_by_id', array('id' => $post->getId() )) );
    // $item->setTitle($post->getTitle());
    $item->setTitle($this->router->generate('view_post_by_id', array('id' => $post->getId() )));
    $item->setDescription(ArticleRssFeedProvider::ITEM_DESCRIPTION_PREFIX.$post->getDate()->format("Y-m-d H:i:s"));
    $item->setLastModified($post->getDate());

    return $item;
  }

}
