<?php
namespace CDE\UserBundle\Entity;

use CDE\AffiliateBundle\Entity\Affiliate;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;


/**
 *
 * @ExclusionPolicy("all")
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Expose
     * @var boolean $commentEmail
     *
     * @ORM\Column(type="boolean")
     */
    private $commentEmail;

    /**
     * @var integer $address
     * 
     * @ORM\OneToOne(targetEntity="Address", mappedBy="user", cascade={"persist", "remove"})
     */
    private $address;
    
    /**
     * @var integer $cart
     * 
     * @ORM\OneToOne(targetEntity="CDE\CartBundle\Entity\Cart", mappedBy="user", cascade={"persist", "remove"})
     */
    private $cart;

    /**
     * @var integer $transactions
     * 
     * @ORM\OneToMany(targetEntity="CDE\CartBundle\Entity\Transaction", mappedBy="user", cascade={"remove"})
     */
    private $transactions;
    
    /**
     * @var integer $subscriptions
     * 
     * @ORM\OneToMany(targetEntity="CDE\SubscriptionBundle\Entity\Subscription", mappedBy="user", cascade={"remove"})
     */
    private $subscriptions;

	/**
	 * @var string $affiliate
	 *
     * @ORM\ManyToOne(targetEntity="CDE\AffiliateBundle\Entity\Affiliate", inversedBy="users")
	 */
	private $affiliate;


    public function __construct() {
        parent::__construct();
        $this->commentEmail = TRUE;
    }
    
    public function __toString()
    {
        return $this->username;
    }

    /**
     * Set commentEmail
     *
     * @param boolean $commentEmail
     * @return User
     */
    public function setCommentEmail($commentEmail)
    {
        $this->commentEmail = $commentEmail;
        return $this;
    }

    /**
     * Get commentEmail
     *
     * @return boolean
     */
    public function getCommentEmail()
    {
        return $this->commentEmail;
    }
    
    /**
     * Set address
     *
     * @param integer $address
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * Get address
     *
     * @return integer 
     */
    public function getAddress()
    {
        return $this->address;
    }


    /**
     * Set cart
     *
     * @param integer $cart
     * @return User
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * Get cart
     *
     * @return integer 
     */
    public function getCart()
    {
        return $this->cart;
    }
    
    /**
     * Set transactions
     *
     * @param integer $transactions
     * @return User
     */
    public function setTransactions($transactions)
    {
        $this->transactions = $transactions;
        return $this;
    }

    /**
     * Get transactions
     *
     * @return integer 
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * Set subscriptions
     *
     * @param integer $subscriptions
     * @return User
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
        return $this;
    }

    /**
     * Get subscriptions
     *
     * @return integer 
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }
    
    /**
     * Get expiresAt
     * 
     * @return datetime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * Get credentialsExpire
     * 
     * @return datetime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }
    
     /**
     * Sets the admin status
     *
     * @param Boolean $boolean
     * @return User
     */
    public function setAdmin($boolean)
    {
        if (true === $boolean) {
            $this->addRole('ROLE_ADMIN');
        } else {
            $this->removeRole('ROLE_ADMIN');
        }
        return $this;
    }

    public function setAffiliate(Affiliate $affiliate) {
        $this->affiliate = $affiliate;
        return $this;
    }

    public function getAffiliate() {
      return $this->affiliate;
    }

}