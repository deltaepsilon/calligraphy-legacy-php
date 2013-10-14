<?php

namespace CDE\StripeBundle\Entity;

use CDE\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Token
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Token
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer $user
     *
     * @ORM\OneToOne(targetEntity="CDE\UserBundle\Entity\User", inversedBy="token")
     */
    private $user;

    /**
     * @var boolean
     *
     * @ORM\Column(name="livemode", type="boolean")
     */
    private $livemode;

    /**
     * @var integer
     *
     * @ORM\Column(name="created", type="integer")
     */
    private $created;

    /**
     * @var boolean
     *
     * @ORM\Column(name="used", type="boolean")
     */
    private $used;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type;

    /**
     * @var \stdClass
     *
     * @ORM\Column(name="card", type="object")
     */
    private $card;


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
     * Set livemode
     *
     * @param boolean $livemode
     * @return Token
     */
    public function setLivemode($livemode)
    {
        $this->livemode = $livemode;
    
        return $this;
    }

    /**
     * Get livemode
     *
     * @return boolean 
     */
    public function getLivemode()
    {
        return $this->livemode;
    }

    /**
     * Set created
     *
     * @param integer $created
     * @return Token
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return integer 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set used
     *
     * @param boolean $used
     * @return Token
     */
    public function setUsed($used)
    {
        $this->used = $used;
    
        return $this;
    }

    /**
     * Get used
     *
     * @return boolean 
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Token
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set card
     *
     * @param \stdClass $card
     * @return Token
     */
    public function setCard($card)
    {
        $this->card = $card;
    
        return $this;
    }

    /**
     * Get card
     *
     * @return \stdClass 
     */
    public function getCard()
    {
        return $this->card;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser(User $user) {
        $this->user = $user;
        return $this;
    }
}
