<?php

namespace CDE\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\ContentBundle\Model\PageInterface;
use CDE\ContentBundle\Model\TagInterface;

/**
 * CDE\ContentBundle\Entity\Page
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CDE\ContentBundle\Entity\PageRepository")
 */
class Page implements PageInterface
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
     * @var integer $tag
     * 
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="pages")
     * 
     */
    private $tags;
    
    /**
     * @var integer $sort
     * 
     * @ORM\Column(name="sort", type="integer", nullable=true)
     */
    private $sort;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string $slug
     *
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var boolean $active
     * 
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var text $html
     *
     * @ORM\Column(name="html", type="text")
     */
    private $html;

    /**
     * @var text $css
     *
     * @ORM\Column(name="css", type="text", nullable=true)
     */
    private $css;

    /**
     * @var datetime $created
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
    
    private $signedHtml;

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
     * Set tag
     *
     * @param integer $tag
     * @return Page
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
     * Get tag
     *
     * @return string 
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set sort
     *
     * @param string $sort
     * @return Page
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    /**
     * Get sort
     *
     * @return string 
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Page
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
     * Set slug
     *
     * @param string $slug
     * @return Page
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
     * Set active
     *
     * @param boolean $active
     * @return Tag
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
     * Set html
     *
     * @param text $html
     * @return Page
     */
    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Get html
     *
     * @return text 
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Set css
     *
     * @param text $css
     * @return Page
     */
    public function setCss($css)
    {
        $this->css = $css;
        return $this;
    }

    /**
     * Get css
     *
     * @return text 
     */
    public function getCss()
    {
        return $this->css;
    }

    /**
     * Set created
     *
     * @param datetime $created
     * @return Page
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
     * @return Page
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
    
    public function getActiveText()
    {
        if($this->active) {
            return 'Active';
        }
        return 'Disabled';
    }
    
    /**
     * Set signedHtml
     *
     * @param text $sighnedHtml
     * @return Page
     */
    public function setSignedHtml($signedHtml)
    {
        $this->signedHtml = $signedHtml;
        return $this;
    }

    /**
     * Get signedHtml
     *
     * @return text 
     */
    public function getSignedHtml()
    {
        return $this->signedHtml;
    }
}