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

    public function testUpdateUser()
    {
        $client = $this->getClient();
        $newEmail = 'newemail@quiver.is';
        $newPassword = 'newPassword';
        $oldPassword = 'user';

        // Bad verification
        $crawler = $client->request('POST', '/angular/user', array(
            'email' => $newEmail,
            'password' => $newPassword,
            'verification' => 'bad verification',
            'oldpassword' => $oldPassword,
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Passwords do not match');

        // Test bad password
        $crawler = $client->request('POST', '/angular/user', array(
            'email' => $newEmail,
            'password' => $newPassword,
            'verification' => $newPassword,
            'oldpassword' => 'badpassword',
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Bad password');

        // Bad email
        $crawler = $client->request('POST', '/angular/user', array(
            'email' => 'bademail',
            'oldpassword' => $newPassword,
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Invalid email');

        // Successful update
        $crawler = $client->request('POST', '/angular/user', array(
            'email' => $newEmail,
            'password' => $newPassword,
            'verification' => $newPassword,
            'oldpassword' => $oldPassword,
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->email, $newEmail);

        // Change password back
        $crawler = $client->request('POST', '/angular/user', array(
            'email' => $newEmail,
            'password' => $oldPassword,
            'verification' => $oldPassword,
            'oldpassword' => $newPassword,
        ));
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

    }

    public function testUpdateAddress()
    {
        $client = $this->getClient();

        // Add an address
        $crawler = $client->request('POST', '/angular/address', array(
            'first' => 'first',
            'last' => 'last',
            'last' => 'last',
            'phone' => 'phone',
            'line1' => 'line1',
            'line2' => 'line2',
            'line3' => 'line3',
            'city' => 'city',
            'state' => 'state',
            'code' => 'code',
            'country' => 'country',
            'instructions' => 'instructions'
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals('first', $response->first);
        $this->assertEquals('last', $response->last);

        // Forget the last name
        $crawler = $client->request('POST', '/angular/address', array(
            'first' => 'first'
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals('First and last name are required', $response->error);

    }

    public function testGetAddress()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', '/angular/address');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->first, 'first');
    }

}
