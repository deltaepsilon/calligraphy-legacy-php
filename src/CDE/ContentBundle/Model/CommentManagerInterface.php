<?php

namespace CDE\ContentBundle\Model;

use CDE\ContentBundle\Model\CommentInterface;
use CDE\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

interface CommentManagerInterface
{
    /**
     * @return CommentInterface
     */
    public function create();
    
    /**
     * Persists new comment to database
     */
    public function add(CommentInterface $comment);
    
    /**
     * Updates comment to database
     */
    public function update(CommentInterface $comment);
    
    /**
     * Deletes comment from database
     */
    public function remove(CommentInterface $comment);
    
    /**
     * Finds one or more comments
     * 
     * @return CommentInterface
     */
    public function find($id);

    /**
     * Paginates comments
     *
     * @return CommentInterface
     */
    public function findByPage($page, $limit, $queryFilter);

    /**
     * Finds one or more comments
     * 
     * @return CommentInterface
     */
    public function findAbsolute($id);

    /**
     * Finds one or more comments by user
     * 
     * @return CommentInterface
     */
    public function findByUser(User $user);

    /**
     * Finds one or more comments by gallery user
     * 
     * @return CommentInterface
     */
    public function findByGalleryUser(User $user);

}
