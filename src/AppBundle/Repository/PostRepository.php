<?php

namespace AppBundle\Repository;

/**
 * PostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PostRepository extends \Doctrine\ORM\EntityRepository
{

  /**=======================================================================================================
   * Retrieves all posts that have titles
   *=======================================================================================================
   */
  public function findAllArticles($showOnlyVisibile = false, $max = 0)
  {
    /* Fairly basic query, that searches for all posts where the title is not null */
    $qb = $this->createQueryBuilder('p');
    $qb->where('p.title IS NOT NULL');

    /* Check to see if we need to restrict the visibility (usually for public facing posts) */
    if ($showOnlyVisibile)
    {
      $qb->andWhere('p.visible = true');
    }

    if ($max > 0)
    {
      $qb->setMaxResults($max);
    }

    /* Order by the published date, generally the most useful */
    $qb->orderBy('p.date', 'DESC');

    return $qb->getQuery()->getResult();
  }

  /**=======================================================================================================
   * Retrieves all posts that have NO titles
   *=======================================================================================================
   */
  public function findAllNotes($showOnlyVisibile = false)
  {
    /* Fairly basic query, that searches for all posts where the title is null */
    $qb = $this->createQueryBuilder('p');
    $qb->where('p.title IS NULL');

    /* Check to see if we need to restrict the visibility (usually for public facing posts) */
    if ($showOnlyVisibile)
    {
      $qb->andWhere('p.visible = true');
    }

    /* Order by the published date, generally the most useful */
    $qb->orderBy('p.date', 'DESC');

    return $qb->getQuery()->getResult();
  }

  /**=======================================================================================================
   * Finds the next visible post to it based on date
   *=======================================================================================================
   */
  public function findNextPost(\DateTime $date)
  {
    /* Query for all entries with a greater date */
    $qb = $this->createQueryBuilder('p');
    $qb->where('p.date > :date');
    $qb->setParameter(':date', $date);

    /* Restrain for visibility */
    $qb->andWhere('p.visible = true');

    /* Limit to a single entry, and order by ascending to get the very bottom entry */
    $qb->orderBy('p.date', 'ASC');
    $qb->setFirstResult(0);
    $qb->setMaxResults(1);

    /* Return a singe entry or null as sometimes there won't be a previous or next post */
    return $qb->getQuery()->getOneOrNullResult();
  }


  /**=======================================================================================================
   * Finds the post previous to it based on date
   *=======================================================================================================
   */
  public function findPreviousPost(\DateTime $date)
  {
    /* Run a query to find all entries with a previous date */
    $qb = $this->createQueryBuilder('p');
    $qb->where('p.date < :date');
    $qb->setParameter(':date', $date);

    /* Remember the constrant for visibility */
    $qb->andWhere('p.visible = true');

    /* Limit to a single entry and order by descending to get the top entry in the result */
    $qb->orderBy('p.date', 'DESC');
    $qb->setFirstResult(0);
    $qb->setMaxResults(1);

    /* Return a singe entry or null as sometimes there won't be a previous or next post */
    return $qb->getQuery()->getOneOrNullResult();

  }

  /**=======================================================================================================
   * Given a year and a month, returns all posts for that period of time
   *=======================================================================================================
   */
  public function findByYearAndMonth($year = null, $month = null)
  {
    /* Given null, use the current year and month */
    if ($month === null)
    {
      $month = (int) date('n');
    }

    if ($year === null)
    {
      $year = (int) date('Y');
    }

    /* Generate date time objects for the queries */
    $startDate = \DateTime::createFromFormat('d-n-Y', "01-".$month."-".$year);
    $startDate->setTime(0, 0 ,0); // First second

    /* Set the end date to be the same date but modify to be the last day of the month */
    $endDate = \DateTime::createFromFormat('d-n-Y', "01-".$month."-".$year);
    $endDate->modify('last day of this month');
    $endDate->setTime(23, 59, 59); // Last second

    /* Build the query to search between the two dates  */
    $qb = $this->createQueryBuilder('p');
    $qb->where('p.date BETWEEN :start AND :end');
    $qb->setParameter(':start', $startDate);
    $qb->setParameter(':end', $endDate);

    /* Only retrieve visible posts */
    $qb->andWhere('p.visible = true');

    /* Order by descending */
    $qb->orderBy('p.date', 'DESC');

    return $qb->getQuery()->getResult();

  }

  /**=======================================================================================================
   * Retrieve all visible TEMPORARY TODO DELETE ME
   *=======================================================================================================
   */
  public function findAllVisible()
  {
    $qb = $this->createQueryBuilder('p');
    $qb->where('p.visible = true');

    $qb->orderBy('p.date', 'DESC');

    return $qb->getQuery()->getResult();
  }

}
