<?php

namespace CDE\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\ContentBundle\Model\CommentInterface;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;

/**
 * CDE\ContentBundle\Entity\Comment
 *
 * @ExclusionPolicy("all")
 * @ORM\Table()
 * @ORM\Entity
 */
class Comment implements CommentInterface
{
    /**
     * @Expose
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Expose
     * @var integer $user
     * 
     * @ORM\ManyToOne(targetEntity="CDE\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @var integer $galleryuser
     *
     * @ORM\ManyToOne(targetEntity="CDE\UserBundle\Entity\User")
     */
    private $galleryuser;

    /**
     * @Expose
     * @var integer $gallery
     *
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="comments")
     */
    private $gallery;

    /**
     * @Expose
     * @var text $comment
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;
    
    /**
     * @Expose
     * @var boolean $marked
     *
     * @ORM\Column(name="marked", type="boolean")
     */
    private $marked;

    /**
     * @Expose
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @Expose
     * @var datetime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;


    public function __toString()
    {
        return (string)$this->id;
    }
    
    public function __construct()
    {
        $this->marked = FALSE;
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
     * @return Comment
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
     * Set galleryuser
     *
     * @param integer $galleryuser
     * @return Comment
     */
    public function setGalleryuser($galleryuser)
    {
        $this->galleryuser = $galleryuser;
        return $this;
    }

    /**
     * Get galleryuser
     *
     * @return integer 
     */
    public function getGalleryuser()
    {
        return $this->galleryuser;
    }

    /**
     * Set gallery
     *
     * @param integer $gallery
     * @return Comment
     */
    public function setGallery($gallery)
    {
        $this->gallery = $gallery;
        return $this;
    }

    /**
     * Get gallery
     *
     * @return integer 
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Set comment
     *
     * @param text $comment
     * @return Comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Get comment
     *
     * @return text 
     */
    public function getComment()
    {
        return $this->comment;
    }


    /**
     * Set marked
     *
     * @param text $marked
     * @return Comment
     */
    public function setMarked($marked)
    {
        $this->marked = $marked;
        return $this;
    }

    /**
     * Get marked
     *
     * @return text 
     */
    public function getMarked()
    {
        return $this->marked;
    }



    /**
     * Set created
     *
     * @param datetime $created
     * @return Comment
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
     * @return Comment
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