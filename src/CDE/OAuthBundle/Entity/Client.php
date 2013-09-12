<?php

namespace CDE\OAuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;

/**
 * Client
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected  $id;

    /**
     * @ORM\OneToMany(targetEntity="AuthCode", mappedBy="client", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $authCode;

    /**
     * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="client", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $accessToken;

    /**
     * @ORM\OneToMany(targetEntity="RefreshToken", mappedBy="client", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $refreshToken;

    public function __toString() {
        return strval($this->id);
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
     * Get random_id
     *
     * @return string
     */
    public function getRandomId() {
        return $this->randomId;
    }

    /**
     * Get authCode
     *
     * @return entity
     */
    public function getAuthCode() {
        return $this->authCode;
    }

    /**
     * Get accessToken
     *
     * @return entity
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Get refreshToken
     *
     * @return entity
     */
    public function getRefreshToken() {
        return $this->refreshToken;
    }
}
