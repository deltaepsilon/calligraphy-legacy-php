<?php

namespace CDE\AffiliateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use \CDE\AffiliateBundle\Model\AffiliateInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * CDE\AffiliateBundle\Entity\Affiliate
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Affiliate implements AffiliateInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $affiliate
     *
     * @ORM\Column(name="affiliate", type="string", length=255)
     */
    private $affiliate;

    /**
     * @var string $ip
     *
     * @ORM\Column(name="ip", type="string", length=100, unique=true)
     */
    private $ip;

	/**
	 * @var datetime $created
	 *
	 * @Gedmo\Timestampable(on="create")
	 * @ORM\Column(name="created", type="datetime")
	 */
    private $created;

	/**
	 * @var datetime $updated
	 *
	 * @Gedmo\Timestampable(on="update")
	 * @ORM\Column(name="updated", type="datetime")
	 */
    private $updated;

	/**
	 * @var users $users
	 *
	 * @ORM\OneToMany(targetEntity="CDE\UserBundle\Entity\User", mappedBy="affiliate")
	 */
	private $users;


	public function __toString()
	{
		return $this->affiliate;
	}

	public function __construct()
	{
		$this->users = new ArrayCollection();
	}

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set affiliate
     *
     * @param string $affiliate
     * @return Affiliate
     */
    public function setAffiliate($affiliate)
    {
        $this->affiliate = $affiliate;
        return $this;
    }

    /**
     * Get affiliate
     *
     * @return string 
     */
    public function getAffiliate()
    {
        return $this->affiliate;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return Affiliate
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set created
     *
     * @param date $created
     * @return Affiliate
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return date 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param date $updated
     * @return Affiliate
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return date 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

	/**
	 * @param users $users
	 * @return Affiliate
	 */
	public function setUsers(Array $users) {
		$this->users = $users;
		return $this;
	}
	/**
	 * Get users
	 *
	 * @return users
	 */
	public function  getUsers()
	{
		return $this->users;
	}
}