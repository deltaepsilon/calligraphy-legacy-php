<?php

namespace CDE\OAuthBundle\Tests\Controller;

use CDE\TestBundle\Base\BaseUserTest;
use Symfony\Component\HttpFoundation\Response;

class OAuthControllerTest extends BaseUserTest
{

    protected $accessToken;

    public function __construct() {
        parent::__construct();
        $this->logIn($this->getUser('ROLE_ADMIN'), new Response());

    }

    public function getClientManager() {
        return $this->container->get('cde_oauth.manager.client');
    }

    public function create() {
        $client = $this->getClient();

        $crawler = $client->followRedirects(); // Follow all redirects
        $crawler = $client->request('GET', '/admin/oauth/client/create');


        $buttonCrawlerNode = $crawler->selectButton("Allow");
        $form = $buttonCrawlerNode->form();
        $crawler = $client->submit($form);

        $content = $client->getResponse()->getContent();
        $json = json_decode($content);

        $this->assertEquals($json->token_type, 'bearer');
        $this->accessToken = $json;
        return $json;

    }

    public function api()
    {
        $client = $this->getLoggedOutClient(); // You wouldn't want to test this in a logged in state would you?

        $crawler = $client->request('GET', '/api/getComments', array(
            'token_type' => 'bearer',
            'access_token' => $this->accessToken->access_token,
        ));


        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

        $contents = $client->getResponse()->getContent();
        $comments = json_decode($contents);
        $this->assertTrue(is_array($comments));

    }

    public function delete() {
        $refreshToken = $this->getClientManager()->findByRefreshToken($this->accessToken->refresh_token);
        $oAuthClient = $refreshToken->getClient();

        $client = $this->getClient();
        $crawler = $client->request('GET', '/admin/oauth/client/delete/'.$oAuthClient->getId());

        $this->assertEquals($client->getResponse()->getStatusCode(), 302);
    }

    public function testAll() {
        $this->create();
        $this->api();
        $this->delete();

    }
}
