<?php

namespace CDE\ContentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use CDE\ContentBundle\Model\TagInterface;
/**
 * CDE\ContentBundle\Entity\Tag
 *
 * @Gedmo\Tree(type="nested")
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 */
class Tag implements TagInterface
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
     * @var integer $id
     * 
     * @ORM\ManyToMany(targetEntity="Page", mappedBy="tags")
     */
    private $pages;

    /**
     * @var integer $id
     * 
     * @ORM\ManyToMany(targetEntity="CDE\CartBundle\Entity\Product", mappedBy="tags")
     */
    private $products;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $slug
     *
     * @Gedmo\Slug(fields={"name"})
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
     * @var integer $lft
     *
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    private $lft;

    /**
     * @var integer $lvl
     *
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    private $lvl;

    /**
     * @var integer $rgt
     *
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    private $rgt;

    /**
     * @var integer $root
     *
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    private $root;
    
    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Tag", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Tag", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    private $children;
    
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

    public function __toString()
    {
        $parent = $this->getParent();
        if ($parent) {
            return $parent->__toString().' - '.$this->name;
        }
        return $this->name;
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
     * Get pages
     * 
     * @return object
     */
    public function getPages()
    {
        return $this->pages;
    }
    
    /**
     * Get products
     * 
     * @return object
     */    
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
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
     * Set parent
     *
     * @param integer $parent
     * @return Tag
     */
    public function setParent(Tag $parent = null)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return string 
     */
    public function getParent()
    {
        return $this->parent;
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

    /**
     * Get lvl
     *
     * @return integer 
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    public function getActiveText()
    {
        if($this->active) {
            return 'Active';
        }
        return 'Disabled';
    }
    
}