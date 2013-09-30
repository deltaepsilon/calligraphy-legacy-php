<?php

namespace CDE\UtilityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AngularControllerTest extends WebTestCase
{
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

}
