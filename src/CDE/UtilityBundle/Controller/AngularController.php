<?php

namespace CDE\UtilityBundle\Controller;

use CDE\UserBundle\Controller\UserController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AngularController extends Controller
{
    public function loginAction()
    {
        $userController = new UserController();
        $userController->container = $this->container;
        return $userController->loginPartialAction();
    }

}
