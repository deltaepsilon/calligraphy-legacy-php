<?php

namespace CDE\CartBundle\Entity;

use CDE\CartBundle\Model\ProductManagerInterface;
use Doctrine\ORM\EntityManager;
use CDE\CartBundle\Entity\Product;
use CDE\CartBundle\Model\ProductInterface;

class ProductManager implements ProductManagerInterface
{
    protected $em;
    protected $class;
    protected $repo;
    
    public function __construct(EntityManager $em, $class){
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
    }
    
    public function create()
    {
        $product = new Product();
        return $product;
    }
    
    public function add(ProductInterface $product)
    {
        if ($product->getType() === 'subscription') {
            $this->saveTags($product);
        }
        $this->cleanImages($product);
        $this->em->persist($product);
        $this->em->flush();
    }
    
    public function update(ProductInterface $product)
    {
        if ($product->getType() === 'subscription') {
            $this->saveTags($product);
        }        
        $this->cleanImages($product);
        $this->em->persist($product);
        $this->em->flush();
    }
    
    public function remove(ProductInterface $product)
    {
        $this->em->remove($product);
        $this->em->flush();
    }

    public function find($id = NULL)
    {
        if ($id) {
            $product = $this->repo->find($id);
        } else {
            $product = $this->repo->findBy(
                array(),
                array('title' => 'ASC')
            );
        }
        return $product;
    }
    
    public function cleanImages(ProductInterface $product)
    {
        $cleanImages = array();
        $images = array_unique($product->getImages());
        foreach ($images as $image) {
            if (strlen($image) > 0) {
                $cleanImages[] = $image;
            }
        }
        $product->setImages(array());
        $product->setImages($cleanImages);
    }
    
    public function findActive($id = NULL)
    {
        if ($id) {
            $product = $this->repo->findBy(
                array('id' => $id, 'active' => TRUE),
                array('type' => 'DESC')
            );
        } else {
            $product = $this->repo->findBy(
                array('active' => TRUE),
                array('type' => 'DESC')
            );
        }
        return $product;
    }

	public function findBySlug($slug) {
		$product = $this->repo->findBy(
			array('slug' => $slug)
		);
		return $product[0];
	}
    
    public function findActiveBySlug($slug)
    {
        $product = $this->repo->findBy(
                array('slug' => $slug, 'active' => TRUE)
            );
        if (isset($product[0])) {
            return $product[0];
        } else {
            return null;
        }

    }
    
    public function saveTags(ProductInterface $product) {
        /**
         *  Forms tend to not to respect array collections...
         * I'm using an entity field with a single select drop down in one of my forms, 
         * and entity types like to return single objects in that situation.  This function
         * requires an array collection, so I need to treat objects differently.  The 
         * addTag function treats objects correctly.
         */
        $tags = $product->getTags();
        if (is_object($tags)) {
            // When adding subtags, you get a PersistentCollection of tags, not a Tag
            if (get_class($tags) === 'Doctrine\ORM\PersistentCollection') {
                foreach ($tags->getValues() as $tag) {
                    // $page->addTag($tag);
                }
            } else {
                $product->addTag($tags);
            }
        }
    }

	public function setTempAvailable(ProductInterface $product, $products) {
		//Update product with cart quantities
		foreach ($products as $cartProduct) {
			if ($cartProduct->getId() == $product->getId() && !is_null($product->getAvailable())) {
				$product->decrementTempAvailable($cartProduct->getQuantity());
			}
		}
		return $product;
	}
    
}
