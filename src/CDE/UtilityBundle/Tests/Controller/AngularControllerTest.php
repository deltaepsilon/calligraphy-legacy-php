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

    /**
     * Managers
     */
    protected function getTransactionManager() {
        return $this->container->get('cde_cart.manager.transaction');
    }

    protected function getCartManager() {
        return $this->container->get('cde_cart.manager.cart');
    }

    protected function getProductManager() {
        return $this->container->get('cde_cart.manager.product');
    }

    /**
     * Test
     */

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
            'comment_email' => 'false',
            'oldpassword' => $oldPassword,
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->email, $newEmail);
        $this->assertEquals($response->comment_email, false);

        // Change password back
        $crawler = $client->request('POST', '/angular/user', array(
            'email' => $newEmail,
            'password' => $oldPassword,
            'verification' => $oldPassword,
            'comment_email' => true,
            'oldpassword' => $newPassword,
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->comment_email, true);

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
        $this->assertEquals('Last name is required', $response->error);
        $this->assertEquals('last', $response->field);

    }

    public function testGetAddress()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', '/angular/address');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->first, 'first');
    }

    public function testGetTransaction()
    {
        $products = $this->getProductManager()->findActive();

        $transaction1 = $this->getTransactionManager()->create();
        $transaction1->setProducts($products);
        $transaction1->setUser($this->user);
        $transaction1->setAmount('100');
        $transaction1->setStatus('yes');
        $this->getTransactionManager()->add($transaction1);

        $transaction2 = $this->getTransactionManager()->create();
        $transaction2->setProducts($products);
        $transaction2->setUser($this->user);
        $transaction2->setAmount('100');
        $transaction2->setStatus('yes');
        $this->getTransactionManager()->add($transaction2);

        $client = $this->getClient();
        $crawler = $client->request('GET', '/angular/transaction');
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $transactions = json_decode($client->getResponse()->getContent());
        $this->assertTrue(is_array($transactions));

        $crawler = $client->request('GET', '/angular/transaction/'.$transactions[0]->id);
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $transaction = json_decode($client->getResponse()->getContent());
        $this->assertEquals($transaction->id, $transactions[0]->id);


        foreach ($this->user->getTransactions() as $actualTransaction) {
            $this->getTransactionManager()->remove($actualTransaction);
        }

    }

    public function testGetProduct()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', '/angular/product');
        $products = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(is_array($products));

        $crawler = $client->request('GET', '/angular/product/'.$products[0]->id);
        $product = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($product->id, $products[0]->id);

    }

    public function testGetCart()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', '/angular/cart');
        $cart = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(isset($cart->id));
        $this->assertTrue(isset($cart->products));
    }

    public function testAddToCart()
    {

        $this->getCartManager()->clear($this->getCartManager()->find($this->user), $this->user);
        $products = $this->getProductManager()->findActive();

        foreach ($products as $prospectiveProduct) {
            if ($prospectiveProduct->getType() === 'physical') {
                $product = $prospectiveProduct;
                break;
            }
        }

        $client = $this->getClient();
        $crawler = $client->request('POST', '/angular/cart', array(
            'product_id' => 'ridiculous',
            'quantity' => $product->getAvailable()
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Product not found');

        $crawler = $client->request('POST', '/angular/cart', array(
            'product_id' => $product->getId(),
            'quantity' => 'really ridiculous'
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Quantity not positive');

        $crawler = $client->request('POST', '/angular/cart', array(
            'product_id' => $product->getId(),
            'quantity' => $product->getAvailable() + 1
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Requested quantity not available');

        $crawler = $client->request('POST', '/angular/cart', array(
            'product_id' => $product->getId(),
            'quantity' => $product->getAvailable()
        ));
        $cart = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(isset($cart->id));
        $this->assertTrue(is_array($cart->products));
        $this->assertEquals($cart->products[0]->quantity, $product->getAvailable());
    }

    public function testUpdateCart()
    {
        $products = $this->getProductManager()->findActive();
        foreach ($products as $product) {
            $this->getCartManager()->addProduct($product, $this->user, 100);
        }

        $cart = $this->getCartManager()->find($this->user);
        $product = $products[0];

        $client = $this->getClient();
        $crawler = $client->request('POST', '/angular/cart/update', array(
            'product_id' => $product->getId(),
            'quantity' => 1
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $productsArray = (array) $response->products;
        foreach ($productsArray as $tempResponseProduct) {
            if ($tempResponseProduct->id === $product->getId()) {
                $responseProduct = $tempResponseProduct;
                break;
            }
        }

        $this->assertEquals($responseProduct->quantity, 1);

        $crawler = $client->request('POST', '/angular/cart/update', array(
            'product_id' => $product->getId(),
            'quantity' => 0
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(count($cart->getProducts()) - 1, count((array) $response->products));

        $crawler = $client->request('POST', '/angular/cart/update', array());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(0, count($response->products));
    }



}
