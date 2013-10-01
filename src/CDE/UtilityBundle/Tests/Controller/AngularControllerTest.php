<?php

namespace CDE\UtilityBundle\Tests\Controller;

use CDE\TestBundle\Base\BaseUserTest;
use Symfony\Component\HttpFoundation\Response;

class AngularControllerTest extends BaseUserTest
{
    public function __construct() {
        parent::__construct();
        $this->logIn($this->getUser('ROLE_ADMIN'), new Response());

    }
//    public function testLogin()
//    {
//        $client = static::createClient();
//
//        $crawler = $client->request('GET', '/login');
//    }

    public function testListBucket()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/angular/images/calligraphy|assets|melissa');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(is_array($response));
    }

    public function testUser()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', '/angular/user');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($this->user->getUsername(), $response->username);
        $this->assertEquals($this->user->getEmail(), $response->email);

    }

}
