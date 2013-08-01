<?php

namespace CDE\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\ContentBundle\Model\GalleryInterface;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * CDE\ContentBundle\Entity\Gallery
 *
 * @ExclusionPolicy("all")
 * @ORM\Table()
 * @ORM\Entity
 */
class Gallery implements GalleryInterface
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
     * @var integer $user
     * 
     * @ORM\ManyToOne(targetEntity="CDE\UserBundle\Entity\User")
     */
    private $user;
    
    /**
     * @var integer $comments
     * 
     * @Expose
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="gallery", cascade={"persist", "remove"})
     */
    private $comments;

    /**
     * @var string $filename
     *
     * @Expose
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;

    /**
     * @var string $title
     *
     * @Expose
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $description
     * @Expose
     * @ORM\Column(name="description", type="text")
     */
    private $description;

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
    
    private $signedUri;

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
     * Set user
     *
     * @param integer $user
     * @return Gallery
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
     * Set comments
     *
     * @param integer $comments
     * @return Gallery
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * Get comments
     *
     * @return integer 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set filename
     *
     * @param string $filename
     * @return Gallery
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Gallery
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
     * @return Gallery
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
     * Set created
     *
     * @param datetime $created
     * @return Gallery
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
     * @return Gallery
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
     * Set signedUri
     *
     * @param datetime $signedUri
     * @return Gallery
     */
    public function setSignedUri($signedUri)
    {
        $this->signedUri = $signedUri;
        return $this;
    }

    /**
     * Get signedUri
     *
     * @return datetime 
     */
    public function getSignedUri()
    {
        return $this->signedUri;
    }

}