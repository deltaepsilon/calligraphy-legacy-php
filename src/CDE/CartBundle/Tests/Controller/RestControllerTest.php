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

        $crawler = $client->request('GET', 'api/discount', array(
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertEquals(count($discounts), count($response));

        $first = $response[0];

        $crawler = $client->request('GET', 'discount/'.$first->id, array(
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

        $crawler = $client->request('GET', 'api/discount/'.$discount->getId(), array(
            'code' => 'code',
            'description' => 'description',
            'expires' => '101',
            'uses' => 101,
            'max_uses' => 1001,
            'value' => 3,
            'percent' => 0.5,
            'token_type' => 'bearer',
            'access_token' => $this->getAccessToken(),
        ));
        $response = $this->getJSONResponse($client);
        $this->assertEquals($discount->getCode(), $response->code);
        $this->assertEquals($discount->getDescription(), $response->description);
        $this->assertEquals($discount->getExpires(), $response->expires);
        $this->assertEquals($discount->getUses(), $response->uses);
        $this->assertEquals($discount->getMaxUses(), $response->max_uses);
        $this->assertEquals($discount->getValue(), $response->value);
        $this->assertEquals($discount->getPercent(), $response->percent);


        $this->getDiscountManager()->update($discount);

    }

}
