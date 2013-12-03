<?php

namespace CDE\CartBundle\Tests\Controller;

use CDE\OAuthBundle\Tests\Controller\OAuthControllerTest;
use CDE\TestBundle\Base\BaseUserTest;
use Symfony\Component\HttpFoundation\Response;

class RestControllerTest extends BaseUserTest
{
    public function __construct() {
        parent::__construct();
        $this->logIn($this->getUser('ROLE_ADMIN'), new Response());

    }

    public function getDiscountManager() {
        return $this->container->get('cde_cart.manager.discount');
    }

    public function getTransactionManager() {
        return $this->container->get('cde_cart.manager.transaction');
    }

    private function getJSONResponse($client) {
        try {
            $content = $client->getResponse()->getContent();
            $json = json_decode($content);
        } catch (\ErrorException $e) {
            return array();
        }
        return $json;
    }

    private function getAccessToken() {
        if (!isset($this->accessToken)) {
            $this->oAuthControllerTest = new OAuthControllerTest();
            $this->accessToken = $this->oAuthControllerTest->create();
        }

        return $this->accessToken->access_token;
    }

    public function testDiscounts()
    {
        $discounts = $this->getDiscountManager()->find();
        $client = $this->getClient();

        $crawler = $client->request('GET', '/api/discount', array(
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertEquals(count($discounts), count($response));

        $first = $response[0];

        $crawler = $client->request('GET', '/api/discount/'.$first->id, array(
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertEquals($first->id, $response->id);

    }

    public function testDiscountUpdate()
    {
        $discounts = $this->getDiscountManager()->find();
        $discount = $discounts[0];

        $client = $this->getClient();

        $crawler = $client->request('POST', '/api/discount/'.$discount->getId(), array(
            'code' => 'code',
            'description' => 'description',
            'expires' => 101,
            'uses' => 101,
            'max_uses' => 1001,
            'value' => 3,
            'percent' => 1,
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertEquals('code', $response->code);
        $this->assertEquals('description', $response->description);
        $this->assertEquals(101, $response->expires);
        $this->assertEquals(101, $response->uses);
        $this->assertEquals(1001, $response->max_uses);
        $this->assertEquals(3, $response->value);
        $this->assertEquals(1, $response->percent);

    }

    public function testTransactions()
    {
        $client = $this->getClient();

        $crawler = $client->request('GET', '/api/transactions', array(
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertEquals(10, count($response->items));

        $first = $response->items[0];

        $crawler = $client->request('GET', '/api/transaction/'.$first->id, array(
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertEquals($first->id, $response->id);

        $crawler = $client->request('POST', '/api/transaction/'.$first->id, array(
            'processed' => true,
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertTrue($response->processed);

        $crawler = $client->request('POST', '/api/transaction/'.$first->id, array(
            'processed' => false,
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertFalse($response->processed);

    }

}
