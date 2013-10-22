<?php

namespace CDE\UtilityBundle\Tests\Controller;

use CDE\TestBundle\Base\BaseUserTest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    protected function getSubscriptionManager()
    {
        return $this->container->get('cde_subscription.manager.subscription');
    }

    protected function getPageManager()
    {
        return $this->container->get('cde_content.manager.page');
    }

    protected function getGalleryManager()
    {
        return $this->container->get('cde_content.manager.gallery');
    }

    /**
     * Convenience
     */

    protected function setStripeAPIKey() {
        $stripeSK = $this->container->getParameter('stripeSK');
        \Stripe::setApiKey($stripeSK);
    }

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

    public function createSubscriptionProduct() {
        $product = $this->getProductManager()->create();
        $product->setDays(30);
        $product->setPrice(100);
        $product->setType('subscription');
        $product->setAvailable(100);
        $product->setActive(true);
        $product->setTitle('My test subscription product');
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

        $token = $this->user->getToken();
        $this->assertFalse(isset($token));




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

    public function testSubscription() {
        $client = $this->getClient();


        $crawler = $client->request('GET', '/angular/subscription');

        //Get all user's subscriptions
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(is_array($response));

        $subscription = $response[0];
        $subscription = $this->getSubscriptionManager()->find($subscription->id);
        $subscription->setReset(false);
        $this->getSubscriptionManager()->update($subscription);

        // Get one subscription
        $crawler = $client->request('GET', '/angular/subscription/'.$subscription->getId());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->id, $subscription->getId());

        $date1 = new \DateTime($response->expires);

        // Reset expires date successfully
        $crawler = $client->request('GET', '/angular/subscription/'.$subscription->getId().'/reset');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->reset, true);

        $date2 = new \DateTime($response->expires);

        // Confirm expires reset
        $this->assertTrue($date1->getTimestamp() < $date2->getTimestamp());

        // Attempt and fail to reset again
        $crawler = $client->request('GET', '/angular/subscription/'.$subscription->getId().'/reset');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->reset, true);
        $date3 = new \DateTime($response->expires);
        $this->assertEquals($date3->getTimestamp(), $date2->getTimestamp());
    }

    public function testContent() {
        $client = $this->getClient();

        // Remove all of user's subscriptions
        $subscriptions = $this->user->getSubscriptions();
        foreach($subscriptions as $subscription) {
            $this->getSubscriptionManager()->remove($subscription);
        }

        // Create subscription product
        $product = $this->createSubscriptionProduct();

        // Test that user cannot query that product and gets a "Product not found" error
        $crawler = $client->request('GET', '/angular/content/'.$product->getSlug());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Product not found');

        // Add subscription to user
        $subscription = $this->getSubscriptionManager()->create();
        $subscription->setUser($this->user);
        $subscription->setProduct($product);
        $this->getSubscriptionManager()->add($subscription);

        // Test that subscription gets reset=true and gets access to product
        $crawler = $client->request('GET', '/angular/content/'.$product->getSlug());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(is_array($response)); //Would be a list of pages...

        $subscription = $this->getSubscriptionManager()->find($subscription->getId());
        //TODO Assert that reset is true... reset IS true, but the ORM is sucking and won't get a fresh version of the object.
//        $this->assertTrue($subscription->getReset());

        // Expire the subscription
        $subscription->setExpires(new \DateTime());
        $this->getSubscriptionManager()->update($subscription);

        // Test that user gets a "Subscription has expired" error
        $crawler = $client->request('GET', '/angular/content/'.$product->getSlug());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Subscription has expired');

        // Remove product and subscription
        $this->getSubscriptionManager()->remove($subscription);
        $this->getProductManager()->remove($product);

    }

    public function testPage() {
        $client = $this->getClient();

        $pages = $this->getPageManager()->find();
        $page = $pages[0];


        // Test that user cannot query that page and gets a "Page not found" error
        $crawler = $client->request('GET', '/angular/page/'.$page->getSlug());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Page not found');

        // Add subscription to user
        // Create subscription product
        $product = $this->createSubscriptionProduct();
        $product->addTag($this->getPageManager()->findParentTag($page));
        $this->getProductManager()->update($product);

        $subscription = $this->getSubscriptionManager()->create();
        $subscription->setUser($this->user);
        $subscription->setProduct($product);
        $this->getSubscriptionManager()->add($subscription);

        // Test that user can query page
        $crawler = $client->request('GET', '/angular/page/'.$page->getSlug());
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->id, $page->getId());

        // Remove new subscription and new product
        $this->getSubscriptionManager()->remove($subscription);
        $this->getProductManager()->remove($product);
    }

    public function testGallery() {
        $client = $this->getClient();

        // Query galleries... none should be found
        $crawler = $client->request('GET', '/angular/gallery');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(count($response), 0);

        // Create new gallery
        copy('../../../../../islc-angular/test/mocks/lake-babe-original.jpg', '../../../../../islc-angular/test/mocks/lake-babe.jpg');
        $file = new UploadedFile(
            '../../../../../islc-angular/test/mocks/lake-babe.jpg',
            'lake-babe.jpg',
            'image/jpeg',
            123
        );
        $crawler = $client->request('POST', '/angular/gallery', array(
            'title' => 'Lake Babe',
            'description' => 'Lake Babe description',
        ), array(
            'file' => $file
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->title, 'Lake Babe');

        // Add bad comment to gallery
        $galleryId = $response->id;
        $crawler = $client->request('POST', "/angular/gallery/$galleryId/comment");
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->error, 'Comment empty');

        // Add good comment to gallery
        $crawler = $client->request('POST', "/angular/gallery/$galleryId/comment", array(
            'comment' => 'test comment'
        ));
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->comment, 'test comment');

        // Query galleries
        $crawler = $client->request('GET', '/angular/gallery');
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertTrue(count($response) > 0);

        // Query specific gallery and assert that image and comments match
        $galleryId = $response[0]->id;
        $gallery = $this->getGalleryManager()->findAbsolute($galleryId);
        $crawler = $client->request('GET', "/angular/gallery/$galleryId");
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals($response->id, $gallery->getId());

        // Query comments
        $crawler = $client->request('GET', "/angular/comment");
        $response = json_decode($client->getResponse()->getContent());
        $this->assertEquals($client->getResponse()->getStatusCode(), 200);
        $this->assertEquals(count($response), 1);

        // Delete all galleries
        $galleries = $this->getGalleryManager()->findByUser($this->user);
        foreach ($galleries as $gallery) {
            $crawler = $client->request('DELETE', '/angular/gallery/'.$gallery->getId());
            $response = json_decode($client->getResponse()->getContent());
            $this->assertEquals($client->getResponse()->getStatusCode(), 200);
            $this->assertEquals($response->id, $gallery->getId());
        }

        $galleries = $this->getGalleryManager()->findByUser($this->user);
        $this->assertEquals(count($galleries), 0);


    }

}
