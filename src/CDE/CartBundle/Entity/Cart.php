<?php

namespace CDE\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use CDE\CartBundle\Model\CartInterface;
use CDE\CartBundle\Entity\Product;

/**
 * CDE\CartBundle\Entity\Cart
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CDE\CartBundle\Entity\CartRepository")
 */
class Cart implements CartInterface
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
     * @ORM\OneToOne(targetEntity="CDE\UserBundle\Entity\User", inversedBy="cart")
     */
    private $user;

    /**
     * @var integer $discount
     * 
     * @ORM\ManyToOne(targetEntity="Discount", inversedBy="cart")
     */
    private $discount;

    /**
     * @var array $products
     *
     * @ORM\Column(name="products", type="array")
     */
    private $products;

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

    public function __construct () 
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param integer $user
     * @return Cart
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return integer 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set discount
     *
     * @param integer $discount
     * @return Cart
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * Get discount
     *
     * @return integer 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set products
     *
     * @param array $products
     * @return Cart
     */
    public function setProducts(ArrayCollection $products = NULL)
    {
        if(!$products) {
            $products = new \Doctrine\Common\Collections\ArrayCollection();
        }
        $this->products = $products;
        return $this;
    }

    public function addProduct(Product $product)
    {
        $id = $product->getId();
        $exisitingProducts = $this->products->filter(
            function ($element) use ($id) {
                if($element->getId() === $id) {
                    return TRUE;
                }
            }
        );
        $existingProduct = $exisitingProducts->first();
        if ($existingProduct) {
            $quantity = $existingProduct->getQuantity();
            $existingProduct->setQuantity($quantity + 1);
        } else {
            $quantity = $product->getQuantity();
            $product->setQuantity($quantity + 1);
            $this->products[] = $product;
        }
        return $this;
    }
    
    public function removeProduct(Product $product)
    {
        $id = $product->getId();
        $productsToRemove = $this->products->filter(
            function ($element) use ($id) {
                if($element->getId() === $id) {
                    return TRUE;
                }
            }
        );
        $iterator = $productsToRemove->getIterator();
        foreach ($iterator as $productToRemove) {
            $this->products->removeElement($productToRemove);
        }
        return $this;
    }

    /**
     * Get products
     *
     * @return array 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Cart
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
     * @return Cart
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