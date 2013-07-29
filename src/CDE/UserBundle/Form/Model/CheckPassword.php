<?php

namespace CDE\UserBundle\Form\Model;

use FOS\UserBundle\Model\UserInterface;

class CheckPassword
{
    /**
     * User whose password is changed
     *
     * @var UserInterface
     */
    public $user;

    /**
     * @var string
     */
    public $current;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }
}
