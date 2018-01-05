<?php

namespace AppBundle\Service;

use FeedIo\Feed as RSSFeed;
use AppBundle\Entity\Feed;
use Debril\RssAtomBundle\Provider\FeedContentProviderInterface;
use AppBundle\Repository\PostRepository;

/**
* Provides an RSS feed based on the site's articles (posts w/ titles)
*/
class ArticleRssFeedProvider implements FeedContentProviderInterface
{
  protected $repo;

  public function __construct(PostRepository $repo)
  {
    $this->repo = $repo;
  }

  public function getFeedContent(array $options)
  {
    /* Declare a new RSSFeed object, and set the basic information*/

    $rssFeed = new RSSFeed();

    $rssFeed->setTitle('My Article Feed Provider');
    $rssFeed->setDescription('One day, this will contain all the articles in the site');
    $rssFeed->setLink('https://somesite.com');
    $rssFeed->setLastModified(new \DateTime());

    /* TODO Inject Doctrine and pull out the x articles */

    return $rssFeed;
  }

}
