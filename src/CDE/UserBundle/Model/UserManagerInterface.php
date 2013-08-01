<?php

namespace   CDE\UserBundle\Model;

interface UserManagerInterface
{
    /**
     * Creates user model using FOSUserBundle user_manager service
     */
    public function create();

    /**
     * Adds user model using FOSUserBundle user_manager service
     */
    public function add($user);
    
    /**
     * Updates user model using FOSUserBundle user_manager service
     */
    public function update($user);
    
    /**
     * @return User
     */
    public function find($id);

    /**
     * @return User
     */
    public function findByPage($page, $limit);
    
    /**
     * Sets default expiresAt and credentialsExpireAt
     */
    public function setDefaultExpires($user);

	/**
	 * Sets user IP address
	 */
	public function setIp($user);
}
