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

    protected function getDiscountManager()
    {
        return $this->container->get('cde_cart.manager.discount');
    }

    protected function getTokenManager()
    {
        return $this->container->get('cde_stripe.manager.token');
    }

    protected function setStripeAPIKey() {
        $stripeSK = $this->container->getParameter('stripeSK');
        \Stripe::setApiKey($stripeSK);
    }

    /**
     * Convenience
     */

    public function clearCart() {
        $this->getCartManager()->clear($this->user->getCart(), $this->user, true);
    }

    public function getStripeToken() {
        $this->setStripeAPIKey();
        $token = \Stripe_Token::create(array('card' => array(
            'number' => '4242424242424242',
            'exp_month' => '12',
            'exp_year' => 2020,
            'cvc' => '123'
        )));
        $token = $token->__toArray();
        $token['card'] = $token['card']->__toArray();

        $client = $this->getClient();

        // Add new token to DB
        $crawler = $client->request('POST', '/angular/token', $token);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->stripe_id, $token['id']);

        return $token;
    }

    public function createPhysicalProduct() {
        $product = $this->getProductManager()->create();
        $product->setPrice(100);
        $product->setType('physical');
        $product->setAvailable(100);
        $product->setActive(true);
        $product->setTitle('My test product');
        $product->setDescription('My test product description');
        $this->getProductManager()->add($product);
        return $product;
    }

    public function createPercentDiscount() {
        $discount = $this->getDiscountManager()->create();
        $discount->setPercent(.5);
        $discount->setMaxUses(2);
        $discount->setExpires(1);
        $this->getDiscountManager()->add($discount);
        return $discount;
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

        $this->clearCart();

        // Make one product
        $product = $this->createPhysicalProduct();

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

        // Attempt to add too many too cart
        $crawler = $client->request('POST', '/angular/cart', array(
            'product_id' => $product->getId(),
            'quantity' => $product->getAvailable() + 1
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Requested quantity not available');

        // Add products to cart correctly
        $crawler = $client->request('POST', '/angular/cart', array(
            'product_id' => $product->getId(),
            'quantity' => $product->getAvailable()
        ));
        $cart = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(isset($cart->id));
        $this->assertTrue(is_array($cart->products));

        $productQuantity = null;
        foreach ($cart->products as $prosepectiveProduct) {
            if ($prosepectiveProduct->id) {
                $productQuantity = $prosepectiveProduct->quantity;
            }
        }

        $this->assertEquals($productQuantity, $product->getAvailable());

        // Remove product
        $this->getProductManager()->remove($product);

    }

    public function testUpdateCart()
    {
        $product = $this->createPhysicalProduct();
        $this->clearCart();
        $this->getCartManager()->addProduct($product, $this->user, 2);
        $cart = $this->getCartManager()->find($this->user);


        $client = $this->getClient();
        $crawler = $client->request('POST', '/angular/cart/update', array(
            'product_id' => $product->getId(),
            'quantity' => 1
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $cartProducts = (Array) $response->products;
        $this->assertEquals(count($cartProducts), 1);
        foreach ($response->products as $testProduct) {
            $this->assertEquals($testProduct->quantity, 1);
        }

        $crawler = $client->request('POST', '/angular/cart/update', array(
            'product_id' => $product->getId(),
            'quantity' => 0
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $cart = $this->user->getCart();
        $this->assertEquals(count($cart->getProducts()) - 1, count((array) $response->products));

        $crawler = $client->request('POST', '/angular/cart/update', array());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(0, count($response->products));


        // Remove product
        $this->getProductManager()->remove($product);
    }

    public function testDiscount()
    {
        $overused = $this->getDiscountManager()->create();
        $overused->setMaxUses(0);
        $overused->setExpires(1);
        $overused->setDescription('test');
        $this->getDiscountManager()->add($overused);

        $expired = $this->getDiscountManager()->create();
        $expired->setMaxUses(1);
        $expired->setExpires(0);
        $expired->setDescription('test');
        $this->getDiscountManager()->add($expired);

        $valid = $this->getDiscountManager()->create();
        $valid->setMaxUses(1);
        $valid->setExpires(1);
        $valid->setDescription('test');
        $this->getDiscountManager()->add($valid);

        $client = $this->getClient();
        $crawler = $client->request('POST', '/angular/discount', array(
            'code' => 'not a code',
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Discount not found');

        $crawler = $client->request('POST', '/angular/discount', array(
            'code' => $overused->getCode(),
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Discount has exceeded maximum uses');

        $crawler = $client->request('POST', '/angular/discount', array(
            'code' => $expired->getCode(),
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Discount has expired');

        $crawler = $client->request('POST', '/angular/discount', array(
            'code' => $valid->getCode(),
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->discount->code, $valid->getCode());

        $crawler = $client->request('POST', '/angular/discount', array());
        $response = json_decode($client->getResponse()->getContent());

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertFalse(isset($response->discount));

        $this->getDiscountManager()->remove($overused);
        $this->getDiscountManager()->remove($expired);
        $this->getDiscountManager()->remove($valid);
    }

    public function testToken() {
        $stripeToken = array(
            'id' => 'tok_2kllnCppxQPSjW',
            'livemode' => false,
            'created' => '1381775840',
            'used' => false,
            'type' => 'card',
            'card' => array('id' => 'card_2kllNB9wcCupCl')
        );

        $badToken = array(
            'id' => '1234',
            'livemode' => false,
            'created' => '1381775840',
            'used' => false,
            'type' => 'card',
        );

        $client = $this->getClient();

        $crawler = $client->request('POST', '/angular/token', $stripeToken);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->stripe_id, $stripeToken['id']);

        $crawler = $client->request('POST', '/angular/token', $badToken);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Token parameter missing');

        $stripeToken['id'] = 'tok_2lCLYhVMAcxwrW';
        $crawler = $client->request('POST', '/angular/token', $stripeToken);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->stripe_id, $stripeToken['id']);

        $crawler = $client->request('GET', '/angular/token', $stripeToken);
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->stripe_id, $stripeToken['id']);

    }

    public function testStripeCheckout() {
        // Set up new Stripe Token to charge against
        $token = $this->getStripeToken();

        // Empty the cart
        $this->clearCart();

        $client = $this->getClient();

        $crawler = $client->request('GET', '/angular/stripe/checkout');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Cart is empty');

        $cart = $this->getCartManager()->find($this->user);
        $this->clearCart();
        $products = $this->getProductManager()->findActive();

        foreach ($products as $product) {
            $cart->addProduct($product);
        }
        $this->getCartManager()->update($cart, $this->user);

        $crawler = $client->request('GET', '/angular/stripe/checkout');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(count($response->products), count($products));




    }

    public function testCartManager() {
        // Make one product
        $product = $this->createPhysicalProduct();

        // Make a percentage discount
        $discount = $this->createPercentDiscount();

        $discount = $this->getDiscountManager()->findByCode($discount->getCode());

        // Empty the cart
        $cart = $this->getCartManager()->find($this->user);
        $this->clearCart();

        $this->getCartManager()->addProduct($product, $this->user, 2);
        $cart->setDiscount($discount);
        $this->getCartManager()->update($cart, $this->user);

        // Assertions...
        // Temporary quantity available should be 98
        // Discount should be applied
        // Quantity in cart should be right.
        $cart = $this->user->getCart();
        $product = $this->getProductManager()->find($product->getId());
        $cartProductValues = $cart->getProducts()->getValues();

        $this->assertEquals(count($cart->getProducts()->getValues()), 1);
        $this->assertEquals($cartProductValues[0]->getId(), $product->getId());
        $this->assertEquals($cartProductValues[0]->getQuantity(), 2);
        $this->assertEquals($cart->getDiscount()->getId(), $discount->getId());
        $this->assertEquals($product->getAvailable(), 98);
        
        // Remove one from cart
        $this->getCartManager()->removeProduct($product, $this->user, 1);

        $cart = $this->user->getCart();
        $product = $this->getProductManager()->find($product->getId());
        $cartProductValues = $cart->getProducts()->getValues();

        $this->assertEquals($product->getAvailable(), 99);
        $this->assertEquals($cartProductValues[0]->getQuantity(), 1);


        // Empty the cart
        $this->clearCart();
        $cart = $this->user->getCart();
        $product = $this->getProductManager()->find($product->getId());
        $this->assertEquals($product->getAvailable(), 100);
        $this->assertEquals(count($cart->getProducts()->getValues()), 0);
        
        // Remove product and discount
        $this->getProductManager()->remove($product);
        $this->getDiscountManager()->remove($discount);

    }

    public function testStripeDiscount() {
        // Set up new Stripe Token to charge against
        $token = $this->getStripeToken();

        // Make one product
        $product = $this->createPhysicalProduct();

        // Make a percentage discount
        $discount = $this->createPercentDiscount();

        // Get DB Discount
        $discount = $this->getDiscountManager()->findByCode($discount->getCode());

        // Empty the cart
        $cart = $this->getCartManager()->find($this->user);
        $this->clearCart();

        $this->getCartManager()->addProduct($product, $this->user, 2);
        $cart->setDiscount($discount);
        $this->getCartManager()->update($cart, $this->user);

        // Attempt a stripe checkout
        $client = $this->getClient();

        $crawler = $client->request('GET', '/angular/stripe/checkout');
        $transaction = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($transaction->amount, 100);

        // Assert discount uses
        $this->assertEquals($transaction->discount->uses, 1);

        // Assert product available
        $product = $this->getProductManager()->find($product->getId());
        $this->assertEquals($product->getAvailable(), 98);


        // Remove transaction
        $dbTransaction = $this->getTransactionManager()->find($transaction->id);
        $this->getTransactionManager()->remove($dbTransaction);

        // Remove product and discount
        $discount = $this->getDiscountManager()->findByCode($discount->getCode());
        $this->getProductManager()->remove($product);
        $this->getDiscountManager()->remove($discount);


    }

}
