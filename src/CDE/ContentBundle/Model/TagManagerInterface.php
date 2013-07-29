<?php

namespace CDE\ContentBundle\Model;

use CDE\ContentBundle\Model\TagInterface;
use CDE\UserBundle\Entity\User;

interface TagManagerInterface
{
    /**
     * @return TagInterface
     */
    public function create();
    
    /**
     * Persists new tag to database
     */
    public function add(TagInterface $tag);
    
    /**
     * Updates option to database
     */
    public function update(TagInterface $tag);
    
    /**
     * Deletes option from database
     */
    public function remove(TagInterface $tag);
    
    /**
     * Finds one or more options
     * 
     * @return TagInterface
     */
    public function find($id);
    
    /**
     * Finds a tag using the slug
     * 
     * @return TagInterface
     */
    public function findBySlug($slug);
    
    /**
     * Finds tags by user
     * 
     * @return TagInterface
     */
    public function findByUser(User $user);
    
    /**
     * Returns a tag's pages as a table of contents
     * 
     */
    public function getToc(TagInterface $tag);
}
