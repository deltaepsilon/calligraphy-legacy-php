<?php

namespace CDE\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\CartBundle\Model\ProductInterface;
use CDE\ContentBundle\Model\TagInterface;


/**
 * CDE\CartBundle\Entity\Product
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CDE\CartBundle\Entity\ProductRepository")
 */
class Product implements ProductInterface
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
     * @var object $discounts
     *
     * @ORM\OneToMany(targetEntity="Discount", mappedBy="product")
     */
    private $discounts;


    /**
     * @var object $subscriptions
     *
     * @ORM\OneToMany(targetEntity="CDE\SubscriptionBundle\Entity\Subscription", mappedBy="product")
     */
    private $subscriptions;


    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string $slug
     *
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price;

	/**
	 * @var integer $available
	 *
	 * @ORM\Column(name="available", type="integer", nullable=TRUE)
	 */
	private $available;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var string $keyImage
     *
     * @ORM\Column(name="keyImage", type="string", length=255, nullable=TRUE)
     */
    private $keyImage;

    /**
     * @var array $images
     *
     * @ORM\Column(name="images", type="array", nullable=TRUE)
     */
    private $images;

    /**
     * @var string $uri
     *
     * @ORM\Column(name="uri", type="string", length=255, nullable=TRUE)
     */
    private $uri;

    /**
     * @var integer $days
     *
     * @ORM\Column(name="days", type="integer", nullable=TRUE)
     */
    private $days;

    /**
     * @var boolean $recurring
     *
     * @ORM\Column(name="recurring", type="boolean", nullable=TRUE)
     */
    private $recurring;

    /**
     * @var integer $tag
     * 
     * @ORM\ManyToMany(targetEntity="CDE\ContentBundle\Entity\Tag", inversedBy="products")
     * 
     */
    private $tags;

    /**
     * @var float $discountPercent
     *
     * @ORM\Column(name="discountPercent", type="float", nullable=TRUE)
     */
    private $discountPercent;

    /**
     * @var float $discountValue
     *
     * @ORM\Column(name="discountValue", type="float", nullable=TRUE)
     */
    private $discountValue;

    /**
     * @var datetime $expiration
     *
     * @ORM\Column(name="expiration", type="integer", nullable=TRUE)
     */
    private $expiration;

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
    
    private $quantity;
    
    private $discountCodes;
    
    private $discountExpiration;
    
    private $signedUri;

	private $tempAvailable;

	private $PHYSICAL = 'physical';
    
    public function __construct()
    {
        $this->discountCodes = array();
        $this->tags = array();
    }
    
    public function __toString()
    {
        return $this->title;
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
     * Set discounts
     *
     * @param object $discounts
     * @return Transaction
     */
    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;
        return $this;
    }

    /**
     * Get discounts
     *
     * @return object 
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * Set subscriptions
     *
     * @param object $subscriptions
     * @return Transaction
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
        return $this;
    }

    /**
     * Get subscriptions
     *
     * @return object 
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Product
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     * @return Product
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
     * Set type
     *
     * @param string $type
     * @return Product
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
     * Set slug
     *
     * @param string $slug
     * @return Product
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

	/**
	 * Set available
	 *
	 * @param float $available
	 * @return Product
	 */
	public function setAvailable($available)
	{
		if ($this->type != $this->PHYSICAL) {
			$available = null;
		}
		$this->available = $available;
		return $this;
	}

	/**
	 * Get available
	 *
	 * @return float
	 */
	public function getAvailable()
	{
		return $this->available;
	}

    /**
     * Set active
     *
     * @param boolean $active
     * @return Product
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set keyImage
     *
     * @param string $keyImage
     * @return Product
     */
    public function setKeyImage($keyImage)
    {
        $this->keyImage = $keyImage;
        return $this;
    }

    /**
     * Get keyImage
     *
     * @return string 
     */
    public function getKeyImage()
    {
        return $this->keyImage;
    }

    /**
     * Set images
     *
     * @param array $images
     * @return Product
     */
    public function setImages($images)
    {
        $this->images = $images;
        return $this;
    }

    /**
     * Get images
     *
     * @return array 
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set uri
     *
     * @param string $uri
     * @return Product
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Get uri
     *
     * @return string 
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Set days
     *
     * @param integer $days
     * @return Product
     */
    public function setDays($days)
    {
        $this->days = $days;
        return $this;
    }

    /**
     * Get days
     *
     * @return integer 
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set recurring
     *
     * @param boolean $recurring
     * @return Product
     */
    public function setRecurring($recurring)
    {
        $this->recurring = $recurring;
        return $this;
    }

    /**
     * Get recurring
     *
     * @return boolean 
     */
    public function getRecurring()
    {
        return $this->recurring;
    }

    /**
     * Set tags
     *
     * @param object $tags
     * @return Product
     */
    public function setTags(TagInterface $tags)
    {
        $this->tags = $tags;
        return $this;
    }
    public function addTag(TagInterface $tag)
    {
        if (is_object ($this->tags) && preg_match('/Tag/', get_class($this->tags))) {
            $this->tags = array($tag);
        } else {
            $this->tags[] = $tag;
        }
        return $this;
    }
    
    /**
     * Get tags
     *
     * @return object 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set discountPercent
     *
     * @param float $discountPercent
     * @return Product
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;
        return $this;
    }

    /**
     * Get discountPercent
     *
     * @return float 
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * Set discountValue
     *
     * @param float $discountValue
     * @return Product
     */
    public function setDiscountValue($discountValue)
    {
        $this->discountValue = $discountValue;
        return $this;
    }

    /**
     * Get discountValue
     *
     * @return float 
     */
    public function getDiscountValue()
    {
        return $this->discountValue;
    }

    /**
     * Set expiration
     *
     * @param integer $expiration
     * @return Product
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * Get expiration
     *
     * @return integer 
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Product
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
     * @return Product
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

    /**
     * Set quantity
     *
     * @param integer $quantity
     * @return Product
     */
    public function setQuantity($quantity)
    {
		$available = $this->available;
		if ($available) {
			$quantity = min($available, $quantity);
		}
        $this->quantity = max($quantity, 0);
        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set discountCode
     *
     * @param integer $discountCode
     * @return Product
     */
    public function setDiscountCodes($discountCodes)
    {
        $this->discountCodes = $discountCodes;
        return $this;
    }

    public function addDiscountCode($discountCode)
    {
        $this->discountCodes[] = $discountCode;
        return $this;
    }

    /**
     * Get discountCode
     *
     * @return integer
     */
    public function getDiscountCodes()
    {
        return $this->discountCodes;
    }

    /**
     * Set discountExpiration
     *
     * @param datetime $discountExpiration
     * @return Product
     */
    public function setDiscountExpiration($discountExpiration)
    {
        $this->discountExpiration = $discountExpiration;
        return $this;
    }

    /**
     * Get discountExpiration
     *
     * @return datetime 
     */
    public function getDiscountExpiration()
    {
        return $this->discountExpiration;
    }

    /**
     * Set signedUri
     *
     * @param integer $signedUri
     * @return Product
     */
    public function setSignedUri($signedUri)
    {
        $this->signedUri = $signedUri;
        return $this;
    }

    /**
     * Get signedUri
     *
     * @return integer
     */
    public function getSignedUri()
    {
        return $this->signedUri;
    }

	/**
	 * Decrement available
	 *
	 * @return Product
	 */
	public function decrementAvailable($quantity = 1)
	{
		if($this->type == $this->PHYSICAL) {
			$available = $this->available;
			$this->available = max($available - $quantity, 0);
		}
		return $this;
	}

	public function getTempAvailable() {
		if (is_null($this->tempAvailable)) {
			return $this->available;
		}
		return $this->tempAvailable;
	}

	public function decrementTempAvailable($quantity = 1)
	{
		if($this->type == $this->PHYSICAL) {
			if (is_null($this->tempAvailable)) {
				$this->tempAvailable = $this->available;
			}
			$this->tempAvailable = max($this->tempAvailable - $quantity, 0);
		}
		return $this;
	}


}