<?php

namespace CDE\UserBundle\Model;

use CDE\UserBundle\Model\AddressInterface;

interface AddressManagerInterface
{
    /**
     * @return AddressInterface
     */
    public function create();
    
    /**
     * Persists new address to database
     */
    public function add(AddressInterface $address);
    
    /**
     * Updates address to database
     */
    public function update(AddressInterface $address);
    
    /**
     * Deletes address from database
     */
    public function remove(AddressInterface $address);
    
    /**
     * Finds one or more addresses
     * 
     * @return AddressInterface
     */
    public function find($id);

    /**
     * Paginates addresses
     *
     * @return AddressInterface
     */
    public function findByPage($page = 1, $limit = 10);

}
