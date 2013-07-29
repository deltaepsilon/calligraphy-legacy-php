<?php

namespace CDE\CartBundle\Model;

use CDE\CartBundle\Model\ProductInterface;

interface ProductManagerInterface
{
    /**
     * @return ProductInterface
     */
    public function create();
    
    /**
     * Persists new product to database
     */
    public function add(ProductInterface $product);
    
    /**
     * Updates product to database
     */
    public function update(ProductInterface $product);
    
    /**
     * Deletes product from database
     */
    public function remove(ProductInterface $product);
    
    /**
     * Finds one or more products
     * 
     * @return ProductInterface
     */
    public function find($id);

    /**
     * Finds active products
     * 
     * @return ProductInterface
     */
    public function findActive($id);

	/**
	 * Finds a product by slug
	 *
	 * @return ProductInterface
	 */
	public function findBySlug($slug);

    /**
     * Finds active products by slug
     * 
     * @return ProductInterface
     */
    public function findActiveBySlug($slug);
   
    /**
     * Updates products with quantities already in cart
     *
     * @return ProductInterface
     */
    public function setTempAvailable(ProductInterface $product, $products);
}
