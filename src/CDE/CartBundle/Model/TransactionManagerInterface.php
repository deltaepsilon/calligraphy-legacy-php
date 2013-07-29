<?php

namespace CDE\CartBundle\Model;

use CDE\CartBundle\Model\TransactionInterface;

interface TransactionManagerInterface
{
    /**
     * @return TransactionInterface
     */
    public function create();
    
    /**
     * Persists new transaction to database
     */
    public function add(TransactionInterface $transaction);
    
    /**
     * Updates transaction to database
     */
    public function update(TransactionInterface $transaction);
    
    /**
     * Deletes transaction from database
     */
    public function remove(TransactionInterface $transaction);
    
    /**
     * Finds one or more transactions
     * 
     * @return TransactionInterface
     */
    public function find($id = null);

    /**
     * Finds one or more transactions for a user
     * 
     * @return TransactionInterface
     */
    public function findByUser($user, $id = null);
    
    /**
     * Processes
     */
    public function sendProducts($transaction, $email = true);

}
