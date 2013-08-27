<?php

namespace CDE\ContentBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RestControllerTest extends WebTestCase
{
    public function testCreateComment()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', 'api/createComment');
    }

    public function testGetComment()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/getComment');
    }

    public function testUpdateComments()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/updateComment');
    }

    public function testEditComment()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/editComment');
    }

    public function testGetcomments()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', 'api/getComments');
    }

}
