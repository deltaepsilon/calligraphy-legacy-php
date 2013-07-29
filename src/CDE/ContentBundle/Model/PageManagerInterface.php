<?php

namespace CDE\ContentBundle\Model;

use CDE\ContentBundle\Model\PageInterface;
use CDE\UserBundle\Entity\User;

interface PageManagerInterface
{
    /**
     * @return PageInterface
     */
    public function create();
    
    /**
     * Persists new page to database
     */
    public function add(PageInterface $page);
    
    /**
     * Updates page to database
     */
    public function update(PageInterface $page);
    
    /**
     * Deletes page from database
     */
    public function remove(PageInterface $page);
    
    /**
     * Finds one or more pages
     * 
     * @return PageInterface
     */
    public function find($id);
    
    /**
     * Finds a page using the slug
     * 
     * @return PageInterface
     */
    public function findBySlug($slug);
    
    /**
     * Finds a page's top level tag
     * 
     * @return TagInterface
     */
    public function findParentTag(PageInterface $page);
    
    /**
     * Validates a page against a user
     * 
     * @return boolean
     */
    public function validatePage(PageInterface $page, User $user);
    
    /**
     * Finds pages in ascending sort order
     * 
     * @return PageInterface
     */
    public function findBySort();
    
}
