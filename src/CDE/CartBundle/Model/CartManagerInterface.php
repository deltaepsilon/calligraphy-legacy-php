<?php

namespace CDE\CartBundle\Model;

use CDE\CartBundle\Model\CartInterface;
use CDE\CartBundle\Model\ProductInterface;

interface CartManagerInterface
{
    /**
     * @return CartInterface
     */
    public function create($user = null);
    
    /**
     * Persists new cart to database
     */
    public function add(CartInterface $cart, $user = NULL);
    
    /**
     * Updates cart to database
     */
    public function update(CartInterface $cart, $user = NULL);
    
    /**
     * Deletes cart from database
     */
    public function clear(CartInterface $cart, $user = null);
    
    /**
     * Finds one or more carts
     * 
     * @return CartInterface
     */
    public function find($user = null);

    /**
     * Add product to cart
     * 
     * @return CartInterface
     */
    public function addProduct(ProductInterface $product, $user = null, $count = 1);

    /**
     * Calculates cart value
     */
    public function getCartValue(CartInterface $cart);

    /**
     * Remove product from cart
     * 
     * @return CartInterface
     */
    public function removeProduct(ProductInterface $product, $user = NULL, $count = 1);
    
    /**
     * Generates PayPal request link
     */
    public function getPaypalLink(CartInterface $cart, $parameters);

    /**
     * Generates PayPal redirect link
     */
    public function getRedirectLink($responseParams, $parameters);

    /**
     * Generates PayPal details link
     */
    public function getDetailsLink($parameters);

    /**
     * Generates PayPal payment finalize link
     */
    public function getPaypalFinal($responseParams, $parameters);

}
