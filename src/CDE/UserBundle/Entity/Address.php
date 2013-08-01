<?php

namespace CDE\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use CDE\UserBundle\Model\AddressInterface;

/**
 * CDE\UserBundle\Entity\Address
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CDE\UserBundle\Entity\AddressRepository")
 */
class Address implements AddressInterface
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
     * @ORM\OneToOne(targetEntity="User", inversedBy="address")
     */
    private $user;

    /**
     * @var string $first
     *
     * @ORM\Column(name="first", type="string", length=255)
     */
    private $first;

    /**
     * @var string $last
     *
     * @ORM\Column(name="last", type="string", length=255)
     */
    private $last;

    /**
     * @var string $phone
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var string $line1
     *
     * @ORM\Column(name="line1", type="string", length=255)
     */
    private $line1;

    /**
     * @var string $line2
     *
     * @ORM\Column(name="line2", type="string", length=255, nullable=true)
     */
    private $line2;

    /**
     * @var string $line3
     *
     * @ORM\Column(name="line3", type="string", length=255, nullable=true)
     */
    private $line3;

    /**
     * @var string $city
     *
     * @ORM\Column(name="city", type="string", length=255)
     */
    private $city;

    /**
     * @var string $state
     *
     * @ORM\Column(name="state", type="string", length=255)
     */
    private $state;

    /**
     * @var string $code
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var string $country
     *
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var text $instructions
     *
     * @ORM\Column(name="instructions", type="text")
     */
    private $instructions;

    public function __toString()
    {
        return $this->first.' '.$this->last.', '.$this->line1;
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
     * @return Address
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
     * Set first
     *
     * @param string $first
     * @return Address
     */
    public function setFirst($first)
    {
        $this->first = $first;
        return $this;
    }

    /**
     * Get first
     *
     * @return string 
     */
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * Set last
     *
     * @param string $last
     * @return Address
     */
    public function setLast($last)
    {
        $this->last = $last;
        return $this;
    }

    /**
     * Get last
     *
     * @return string 
     */
    public function getLast()
    {
        return $this->last;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Address
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set line1
     *
     * @param string $line1
     * @return Address
     */
    public function setLine1($line1)
    {
        $this->line1 = $line1;
        return $this;
    }

    /**
     * Get line1
     *
     * @return string 
     */
    public function getLine1()
    {
        return $this->line1;
    }

    /**
     * Set line2
     *
     * @param string $line2
     * @return Address
     */
    public function setLine2($line2)
    {
        $this->line2 = $line2;
        return $this;
    }

    /**
     * Get line2
     *
     * @return string 
     */
    public function getLine2()
    {
        return $this->line2;
    }

    /**
     * Set line3
     *
     * @param string $line3
     * @return Address
     */
    public function setLine3($line3)
    {
        $this->line3 = $line3;
        return $this;
    }

    /**
     * Get line3
     *
     * @return string 
     */
    public function getLine3()
    {
        return $this->line3;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Address
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Address
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set code
     *
     * @param string $code
     * @return Address
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
     * Set country
     *
     * @param string $country
     * @return Address
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set instructions
     *
     * @param text $instructions
     * @return Address
     */
    public function setInstructions($instructions)
    {
        $this->instructions = $instructions;
        return $this;
    }

    /**
     * Get instructions
     *
     * @return text 
     */
    public function getInstructions()
    {
        return $this->instructions;
    }
}