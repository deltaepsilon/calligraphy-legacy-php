<?php

namespace CDE\CartBundle\Model;

use CDE\CartBundle\Model\DiscountInterface;

interface DiscountManagerInterface
{
    /**
     * @return DiscountInterface
     */
    public function create();
    
    /**
     * Persists new discount to database
     */
    public function add(DiscountInterface $discount);
    
    /**
     * Updates discount to database
     */
    public function update(DiscountInterface $discount);
    
    /**
     * Deletes discount from database
     */
    public function remove(DiscountInterface $discount);
    
    /**
     * Finds one or more discounts
     * 
     * @return DiscountInterface
     */
    public function find($id);

    /**
     * Finds one by it's code
     * 
     * @return DiscountInterface
     */
    public function findByCode($code);

}
