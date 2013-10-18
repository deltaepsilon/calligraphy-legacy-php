<?php

namespace CDE\SubscriptionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\SubscriptionBundle\Model\SubscriptionInterface;
use CDE\CartBundle\Model\ProductInterface;
use CDE\UserBundle\Entity\User;
/**
 * CDE\SubscriptionBundle\Entity\Subscription
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CDE\SubscriptionBundle\Entity\SubscriptionRepository")
 */
class Subscription implements SubscriptionInterface
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
     * @var integer $user
     * 
     * @ORM\ManyToOne(targetEntity="CDE\UserBundle\Entity\User", inversedBy="subscriptions")
     */
    private $user;

    /**
     * @var integer $product
     * 
     * @ORM\ManyToOne(targetEntity="CDE\CartBundle\Entity\Product", cascade={"persist"}, inversedBy="subscriptions")
     */
    private $product;

    /**
     * @var datetime $expires
     *
     * @ORM\Column(name="expires", type="datetime")
     */
    private $expires;

    /**
     * @var datetime $reset
     *
     * @ORM\Column(name="reset", type="boolean")
     */
    private $reset;

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

    public function __toString()
    {
        $id = $this->id;
        return "Subscription #$id";
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
     * Set user
     *
     * @param user $user
     * @return Subscription
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return user 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set product
     *
     * @param ProductInterface $product
     * @return Subscription
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return subscription
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set expires
     *
     * @param datetime $expires
     * @return Subscription
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * Get expires
     *
     * @return datetime 
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Subscription
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param datetime $updated
     * @return Subscription
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return datetime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }
    
    public function getDaysRemaining()
    {
        $current = time();
        $seconds = $this->expires->getTimestamp() - $current;
        $days = $seconds / ( 60 * 60 * 24 );
        $result = round($days, 1);
        return $result;
    }
    
    public function addDays($days)
    {
        $now = new \DateTime();
        $expires = $this->expires;
        if ($expires < $now) {
            $expires = $now;
        }
        $interval = new \DateInterval('P'.$days.'D');
        $expires->add($interval);
        $this->setExpires($expires);
        return $this;
    }

    public function setReset($reset) {
        if (is_bool($reset)) {
            $this->reset = $reset;
        } else {
            throw new \Exception('Must be a boolean!');
        }

        return $this;
    }

    public function getReset() {
        return $this->reset;
    }
    
}