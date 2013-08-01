<?php

namespace CDE\SubscriptionBundle\Model;

use CDE\SubscriptionBundle\Model\SubscriptionInterface;
use CDE\UserBundle\Entity\User;

interface SubscriptionManagerInterface
{
    /**
     * @return SubscriptionInterface
     */
    public function create();
    
    /**
     * Persists new option to database
     */
    public function add(SubscriptionInterface $subscription);
    
    /**
     * Updates option to database
     */
    public function update(SubscriptionInterface $subscription);
    
    /**
     * Deletes option from database
     */
    public function remove(SubscriptionInterface $subscription);
    
    /**
     * Finds one or more options
     * 
     * @return SubscriptionInterface
     */
    public function find($id);

    /**
     * Paginates subscriptions
     *
     * @return SubscriptionInterface
     */
    public function findByPage($page = 1, $limit = 10);
    
    /**
     * 
     * Returns an exisiting subscription with matching user and product
     * Returns NULL if no exisiting found
     * 
     * @return SubscriptionInterface
     */
    public function checkExisiting(SubscriptionInterface $subscription);
    
    /**
     * Finds subscriptions by user
     * 
     * @return SubscriptionInterface
     */
    public function findByUser(User $user);
    
}
