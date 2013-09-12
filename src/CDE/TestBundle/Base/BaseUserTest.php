<?php

namespace CDE\TestBundle\Base;


use FOS\UserBundle\Model\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class BaseUserTest extends WebTestCase {

    protected $client;
    protected $container;
    protected $storage;
    protected $session;
    protected $user;
    protected $cookieJar;
    protected $cookie;
    protected $token;


    public function __construct() {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->storage = new MockFileSessionStorage(__dir__.'/../../../../app/cache/test/sessions');
        $this->session = new Session($this->storage);

    }

    public function getUserManager() {
        return $this->container->get('cde_user.manager.user');

    }

    public function getSecurityManager() {
        return $this->container->get('fos_user.security.login_manager');

    }

    protected function getClient() {
        $client = static::createClient();
        $client->getCookieJar()->set($this->cookie);
        return $client;
    }

    public function getUser($role = null) {
        if (!isset($this->user)) {
            $user = $this->getUserManager()->loadByUsername('user');

            if (isset($user)) {
                $this->user = $user;
            } else {
                $this->user = $this->getUserManager()->create();

                $this->user->setEnabled(true);
                $this->user->setUsername('user');
                $this->user->setEmail('user@quiver.is');
                $this->user->setPlainPassword('user');
                $this->getUserManager()->updatePassword($this->user);
                if (isset($role)) {
                    $this->user->addRole($role);
                }
                $this->getUserManager()->add($this->user);
            }

        }

        return $this->user;
    }

    public function logIn(User $user, Response $response) {
        $this->session->start();

        $this->cookie = new Cookie('MOCKSESSID', $this->storage->getId());
        $this->cookieJar = new CookieJar();
        $this->cookieJar->set($this->cookie);
        $this->token = new UsernamePasswordToken($user, 'user', 'main', $user->getRoles());
        $this->session->set('_security_main', serialize($this->token));


        $this->getSecurityManager()->loginUser(
            $this->container->getParameter('fos_user.firewall_name'),
            $user,
            $response
        );



        $this->session->save();




    }

    public function removeUser(User $user) {

    }
}