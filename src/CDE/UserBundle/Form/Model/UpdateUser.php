<?php

namespace CDE\UserBundle\Form\Model;

use FOS\UserBundle\Model\UserInterface;

class UpdateUser extends CheckPassword
{
    /**
     * @var string
     */
    public $new;
    
    /**
     * @var string
     */
    public $email;

    /**
     * @var boolean
     */
    public $commentEmail;
    
    public function __construct(UserInterface $user)
    {
        parent::__construct($user);
        $this->email = $user->getEmail();
        $this->commentEmail = $user->getCommentEmail();
    }
}
