<?php

namespace CDE\ContentBundle\Model;

use CDE\ContentBundle\Model\GalleryInterface;
use CDE\UserBundle\Entity\User;

interface GalleryManagerInterface
{
    /**
     * @return GalleryInterface
     */
    public function create();
    
    /**
     * Persists new gallery to database
     */
    public function add(GalleryInterface $gallery);
    
    /**
     * Updates gallery to database
     */
    public function update(GalleryInterface $gallery);
    
    /**
     * Deletes gallery from database
     */
    public function remove(GalleryInterface $gallery);
    
    /**
     * Finds one or more galleries
     * 
     * @return GalleryInterface
     */
    public function find(User $user);

    /**
     * Finds galleries by page
     *
     * @return GalleryInterface
     */
    public function findByPage($page, $limit);
    
    /**
     * Finds one or more galleries
     * 
     * @return GalleryInterface
     */
    public function findAbsolute($id);

    /**
     * Finds one or more galleries by user
     * 
     * @return GalleryInterface
     */
    public function findByUser(User $user);
    
    
    
}
