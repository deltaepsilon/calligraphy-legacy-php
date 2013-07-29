<?php

namespace CDE\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\CartBundle\Model\DiscountInterface;

/**
 * CDE\CartBundle\Entity\Discount
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CDE\CartBundle\Entity\DiscountRepository")
 */
class Discount implements DiscountInterface
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
     * @var integer $transaction
     *
     * @ORM\OneToMany(targetEntity="Transaction", mappedBy="discount", cascade={"persist"})
     */
    private $transaction;

    /**
     * @var integer $product
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="discounts", cascade={"persist"})
     */
    private $product;

    /**
     * @var integer $cart
     *
     * @ORM\OneToMany(targetEntity="Cart", mappedBy="discount")
     */
    private $cart;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var integer $expires
     *
     * @ORM\Column(name="expires", type="integer")
     */
    private $expires;

    /**
     * @var integer $uses
     *
     * @ORM\Column(name="uses", type="integer")
     */
    private $uses;

    /**
     * @var integer $maxUses
     *
     * @ORM\Column(name="maxUses", type="integer")
     */
    private $maxUses;

    /**
     * @var float $value
     *
     * @ORM\Column(name="value", type="float", nullable=true)
     */
    private $value;

    /**
     * @var float $percent
     *
     * @ORM\Column(name="percent", type="float", nullable=true)
     */
    private $percent;

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

    public function __construct()
    {
        $this->code = uniqid();
        $this->value = 0;
        $this->percent = 0;
        $this->uses = 0;
        $this->maxUses = 0;
    }
    
    public function __toString()
    {
        
        return (string)$this->id;
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
     * Set transaction
     *
     * @param object $transaction
     * @return Discount
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

    /**
     * Get transaction
     *
     * @return object 
     */
    public function getTransaction()
    {
        return $this->transaction;
    }


    /**
     * Set product
     *
     * @param object $product
     * @return Discount
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Get product
     *
     * @return object 
     */
    public function getProduct()
    {
        return $this->product;
    }



    /**
     * Set cart
     *
     * @param object $cart
     * @return Discount
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * Get cart
     *
     * @return object 
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Discount
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set description
     *
     * @param text $description
     * @return Discount
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set expires
     *
     * @param integer $expires
     * @return Discount
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * Get expires
     *
     * @return integer 
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Get expires
     *
     * @return datetime 
     */
    public function getExpiresDate()
    {
        $expiresDate = $this->created;
        if (!$expiresDate) {
            $expiresDate = new \DateTime();
        }
        $interval = new \DateInterval('P'.$this->expires.'D');
        $expiresDate->add($interval);
        return $expiresDate;
    }

    /**
     * Set uses
     *
     * @param integer $uses
     * @return Discount
     */
    public function setUses($uses)
    {
        $this->uses = $uses;
        return $this;
    }

    public function incrementUses()
    {
        $this->uses += 1;
        return $this;
    }

    /**
     * Get uses
     *
     * @return integer 
     */
    public function getUses()
    {
        return $this->uses;
    }

    /**
     * Set maxUses
     *
     * @param integer $maxUses
     * @return Discount
     */
    public function setMaxUses($maxUses)
    {
        $this->maxUses = $maxUses;
        return $this;
    }

    /**
     * Get maxUses
     *
     * @return integer 
     */
    public function getMaxUses()
    {
        return $this->maxUses;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return Discount
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set percent
     *
     * @param float $percent
     * @return Discount
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;
        return $this;
    }

    /**
     * Get percent
     *
     * @return float 
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Discount
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
     * @return Discount
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
}