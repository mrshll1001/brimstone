<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserProfile
 *
 * @ORM\Table(name="user_profile")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserProfileRepository")
 */
class UserProfile
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;

    /**
     * @var string
     *
     * @ORM\Column(name="site_title", type="string", length=255)
     */
    private $siteTitle;

    /**
     * @var array
     *
     * @ORM\Column(name="twitter_keys", type="json_array", nullable=true)
     */
    private $twitterKeys;

    /**
     * @var array
     *
     * @ORM\Column(name="mastodon_keys", type="json_array", nullable=true)
     */
    private $mastodonKeys;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return UserProfile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return UserProfile
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set about
     *
     * @param string $about
     *
     * @return UserProfile
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }

    /**
     * Get about
     *
     * @return string
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Set twitterKeys
     *
     * @param array $twitterKeys
     *
     * @return UserProfile
     */
    public function setTwitterKeys($twitterKeys)
    {
        $this->twitterKeys = $twitterKeys;

        return $this;
    }

    /**
     * Get twitterKeys
     *
     * @return array
     */
    public function getTwitterKeys()
    {
        return $this->twitterKeys;
    }

    /**
     * Set siteTitle
     *
     * @param string $siteTitle
     *
     * @return UserProfile
     */
    public function setSiteTitle($siteTitle)
    {
        $this->siteTitle = $siteTitle;

        return $this;
    }

    /**
     * Get siteTitle
     *
     * @return string
     */
    public function getSiteTitle()
    {
        return $this->siteTitle;
    }

    /**
     * Set mastodonKeys.
     *
     * @param array|null $mastodonKeys
     *
     * @return UserProfile
     */
    public function setMastodonKeys($mastodonKeys = null)
    {
        $this->mastodonKeys = $mastodonKeys;

        return $this;
    }

    /**
     * Get mastodonKeys.
     *
     * @return array|null
     */
    public function getMastodonKeys()
    {
        return $this->mastodonKeys;
    }
}
