<?php

namespace CDE\CartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\CartBundle\Model\TransactionInterface;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * CDE\CartBundle\Entity\Transaction
 *
 * @ExclusionPolicy("all")
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CDE\CartBundle\Entity\TransactionRepository")
 */
class Transaction implements TransactionInterface
{
    /**
     * @var integer $id
     *
     * @Expose
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var object $user
     *
     * @Expose
     * @ORM\ManyToOne(targetEntity="CDE\UserBundle\Entity\User", inversedBy="transactions")
     */
    private $user;

    /**
     * @var object $discount
     *
     * @Expose
     * @ORM\ManyToOne(targetEntity="Discount", inversedBy="transaction")
     */
    private $discount;

    /**
     * @var object $discountApplied
     *
     * @Expose
     * @ORM\Column(name="discountApplied", type="float", nullable=true)
     */
    private $discountApplied;

    /**
     * @var array $products
     *
     * @Expose
     * @ORM\Column(name="products", type="array")
     */
    private $products;

    /**
     * @var array $details
     *
     * @Expose
     * @ORM\Column(name="details", type="array")
     */
    private $details;

    /**
     * @var array $payment
     *
     * @ORM\Column(name="payment", type="array")
     */
    private $payment;

    /**
     * @var float $amount
     *
     * @Expose
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var string $status
     *
     * @Expose
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var boolean $processed
     *
     * @Expose
     * @ORM\Column(name="processed", type="boolean")
     */
    private $processed;

    /**
     * @var datetime $created
     *
     * @Expose
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     *
     * @Expose
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    public function __construct()
    {
        $this->processed = FALSE;
        $this->products = new ArrayCollection();
        $this->details = new ArrayCollection();
        $this->payment = new ArrayCollection();
    }

    public function __toString()
    {
        return 'Transaction #'.$this->id;
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
     * @param object $user
     * @return Transaction
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return object 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set discount
     *
     * @param object $discount
     * @return Transaction
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * Get discount
     *
     * @return object 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set discountApplied
     *
     * @param object $discountApplied
     * @return Transaction
     */
    public function setDiscountApplied($discountApplied)
    {
        $this->discountApplied = $discountApplied;
        return $this;
    }

    /**
     * Get discountApplied
     *
     * @return object 
     */
    public function getDiscountApplied()
    {
        return $this->discountApplied;
    }

    /**
     * Set products
     *
     * @param array $products
     * @return Transaction
     */
    public function setProducts($products)
    {
        $this->products = $products;
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
     * Set details
     *
     * @param array $details
     * @return Transaction
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * Get details
     *
     * @return array 
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set payment
     *
     * @param array $payment
     * @return Transaction
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * Get payment
     *
     * @return array 
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set processed
     *
     * @param string $processed
     * @return Transaction
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
        return $this;
    }

    /**
     * Get processed
     *
     * @return string 
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Transaction
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
     * @return Transaction
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
     * Return stringified JSON representation of transaction
     *
     * @return string
     */
    public function getJSON() {
        $transaction = array(
            'id' => $this->id,
            'amount' => $this->amount,
            'user' => array(
                'id' => $this->user->getId(),
                'affiliate' => $this->user->getAffiliate(),
                'username' => $this->user->getUsername()
            )
        );

        $products = array();

        foreach($this->products->toArray() as $product) {
            $products[] = array(
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'category' => $product->getType(),
                'price' => $product->getPrice(),
                'quantity' => $product->getQuantity()
            );
        }
        $transaction['products'] = $products;

        return json_encode($transaction);
    }

}