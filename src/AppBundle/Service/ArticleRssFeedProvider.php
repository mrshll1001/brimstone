<?php

namespace AppBundle\Service;

use FeedIo\Feed as RSSFeed;
use AppBundle\Entity\Feed;
use Debril\RssAtomBundle\Provider\FeedContentProviderInterface;
use Doctrine\ORM\EntityManager;

/**
* Provides an RSS feed based on the site's articles (posts w/ titles)
*/
class ArticleRssFeedProvider implements FeedContentProviderInterface
{
  protected $em;

  public function __construct(EntityManager $em)
  {
    $this->em = $em;
  }

  public function getFeedContent(array $options)
  {
    /* Declare a new RSSFeed object, and set the basic information*/

    $rssFeed = new RSSFeed();

    $feed->setTitle('My Article Feed Provider');
    $feed->setDescription('One day, this will contain all the articles in the site');
    $feed->setLink('https://somesite.com');
    $feed->setLastModified(new \DateTime());

    /* TODO Inject Doctrine and pull out the x articles */

    return $rssFeed;
  }

}
