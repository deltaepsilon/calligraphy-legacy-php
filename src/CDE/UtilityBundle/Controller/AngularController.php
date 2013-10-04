<?php

namespace CDE\UtilityBundle\Controller;

use CDE\UserBundle\Controller\UserController;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Tests\RequestTest;
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
    protected function getParameter($request, $name, $normalize = false) {
        $params = $request->request->all();
        if (isset($params[$name])) {
            if ($normalize) {
                if (strtolower($params[$name]) === 'true') {
                    $params[$name] = true;
                } else if (strtolower($params[$name]) === 'false') {
                    $params[$name] = false;
                }
            }
            return $params[$name];
        } else {
            return null;
        }
    }

    protected function getParameters($request, $names) {
        $params = $request->request->all();
        foreach ($names as $name) {
            if (!isset($params[$name])) {
                $result[$name] = null;
            }
        }
        return $params;

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

    public function registerAction(Request $request)
    {
        $userController = new UserController();
        $userController->container = $this->container;
        return $userController->registerPartialAction($request);
    }

    public function resetAction()
    {
        $userController = new UserController();
        $userController->container = $this->container;
        return $userController->resetPartialAction();
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
            $commentEmail = $this->getParameter($request, 'comment_email', true);

            if ( isset($password) && ($password !== $verification) ) {
                $view = $this->view(array('error' => 'Passwords do not match', 'field' => 'verification'), 200)->setFormat('json');
            } else if (isset($password) && strlen($password) < 3) {
                $view = $this->view(array('error' => 'Password is too short', 'field' => 'password'), 200)->setFormat('json');

            } else if (isset($email) && count($this->getUserManager()->validateEmail($email))) {
                $view = $this->view(array('error' => 'Invalid email', 'field' => 'email'), 200)->setFormat('json');

            } else if (!$this->getUserManager()->checkPassword($user, $oldPassword)) {
                $view = $this->view(array('error' => 'Bad password', 'field' => 'oldpassword'), 200)->setFormat('json');

            } else {
                if (isset($email)) {
                    $user->setEmail($email);
                }
                if (isset($password)) {
                    $user->setPlainPassword($password);
                    $this->getUserManager()->updatePassword($user);
                }

                if (isset($commentEmail)) {
                    $user->setCommentEmail($commentEmail);
                }

                $this->getUserManager()->update($user);
                $view = $this->view($user, 200)->setFormat('json');
            }

        }
        return $this->handleView($view);
    }

    public function addressAction()
    {
        $user = $this->getUser();
        $view = $this->view($user->getAddress(), 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function addressUpdateAction(Request $request) {
        $user = $this->getUser();
        if (!$user) {
            $view = $this->view(array('error' => 'Not found'), 401)->setFormat('json');
        } else {
            $params = $request->request->all();
            if (!isset($params['first'])) {
                $view = $this->view(array('error' => 'First name is required', 'field' => 'first'), 200)->setFormat('json');
            } else if (!isset($params['last'])) {
                $view = $this->view(array('error' => 'Last name is required', 'field' => 'last'), 200)->setFormat('json');
            } else {
                $params = $this->getParameters($request, array('first', 'last', 'phone', 'line1', 'line2', 'line3', 'city', 'state', 'code', 'country', 'instructions'));
                $address = $user->getAddress();
                if (!isset($address)) {
                    $address = $this->getAddressManager()->create();
                    $address->setUser($user);
                }
                $address->setFirst($params['first']);
                $address->setLast($params['last']);
                $address->setPhone($params['phone']);
                $address->setLine1($params['line1']);
                $address->setLine2($params['line2']);
                $address->setLine3($params['line3']);
                $address->setCity($params['city']);
                $address->setState($params['state']);
                $address->setCode($params['code']);
                $address->setCountry($params['country']);
                $address->setInstructions($params['instructions']);

                $this->getAddressManager()->update($address);

                $view = $this->view($address, 200)->setFormat('json');
            }

        }
        return $this->handleView($view);
    }

}
