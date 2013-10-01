<?php

namespace CDE\UtilityBundle\Controller;

use CDE\UserBundle\Controller\UserController;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AngularController extends FOSRestController
{
    protected function getAWSService() {
        return $this->get('cde_utility.manager.aws');
    }
    public function loginAction()
    {
        $userController = new UserController();
        $userController->container = $this->container;
        return $userController->loginPartialAction();
    }

    public function listImagesAction($prefix)
    {
        $response = $this->getAWSService()->listImages($prefix);
        $view = $this->view($response, 200)
            ->setHeader('Expires', gmdate('D, d M Y H:i:s', strtotime('+1 days')) . ' GMT')
            ->setHeader('Cache-Control', 'max-age=86400, public')
            ->setFormat('json');
        return $this->handleView($view);
    }

    public function userAction()
    {
        $user = $this->getUser();
        $view = $this->view($user, 200)
            ->setFormat('json');
        return $this->handleView($view);
    }

}
