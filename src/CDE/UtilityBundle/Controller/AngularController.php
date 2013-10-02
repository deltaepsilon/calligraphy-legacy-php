<?php

namespace CDE\UtilityBundle\Controller;

use CDE\UserBundle\Controller\UserController;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;

class AngularController extends FOSRestController
{
    /**
     * Services
     */
    protected function getValidator() {
        return $this->get('validator');
    }

    /**
     * Managers
     */
    protected function getAWSService() {
        return $this->get('cde_utility.manager.aws');
    }

    protected function getAddressManager()
    {
        return $this->get('cde_user.manager.address');
    }

    public function getUserManager()
    {
        return $this->get('cde_user.manager.user');
    }

    /**
     * Convenience Methods
     */
    protected function getParameter($request, $name) {
        $params = $request->request->all();
        if (isset($params[$name])) {
            return $params[$name];
        } else {
            return null;
        }
    }

    /**
     * Actions
     */
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
        $view = $this->view($user, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function userUpdateAction(Request $request) {
        $user = $this->getUser();
        if (!$user) {
            $view = $this->view(array('error' => 'Not found'), 401)->setFormat('json');
        } else {
            $password = $this->getParameter($request, 'password');
            $verification = $this->getParameter($request, 'verification');
            $oldPassword = $this->getParameter($request, 'oldpassword');
            $email = $this->getParameter($request, 'email');

            if ( isset($password) && ($password !== $verification) ) {
                $view = $this->view(array('error' => 'Passwords do not match'), 200)->setFormat('json');
            } else if (isset($password) && strlen($password) < 3) {
                $view = $this->view(array('error' => 'Password is too short'), 200)->setFormat('json');

            } else if (isset($email) && count($this->getUserManager()->validateEmail($email))) {
                $view = $this->view(array('error' => 'Invalid email'), 200)->setFormat('json');

            } else if (!$this->getUserManager()->checkPassword($user, $oldPassword)) {
                $view = $this->view(array('error' => 'Bad password'), 200)->setFormat('json');

            } else {
                if (isset($email)) {
                    $user->setEmail($email);
                }
                if (isset($password)) {
                    $user->setPlainPassword($password);
                    $this->getUserManager()->updatePassword($user);
                }
                $this->getUserManager()->update($user);
                $view = $this->view($user, 200)->setFormat('json');
            }

        }
        return $this->handleView($view);
    }

    public function addressUpdateAction(Request $request) {
        $user = $this->getUser();
        if (!$user) {
            $view = $this->view(array('error' => 'Not found'), 401)->setFormat('json');
        } else {
            $address = $user->getAddress();
            $params = $request->query;
            $this->getAddressManager()->update($address);
        }
        return $this->handleView($view);
    }

}
