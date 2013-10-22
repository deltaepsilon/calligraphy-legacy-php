<?php

namespace CDE\UtilityBundle\Controller;

use CDE\UserBundle\Controller\UserController;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Tests\RequestTest;
use Symfony\Component\Validator\Constraints\Email;

class AngularController extends FOSRestController
{
    /**
     * Services
     */
    protected function getValidator() {
        return $this->get('validator');
    }

    /**
     * Managers
     */
    protected function getAwsManager() {
        return $this->get('cde_utility.manager.aws');
    }

    protected function getAddressManager()
    {
        return $this->get('cde_user.manager.address');
    }

    public function getUserManager()
    {
        return $this->get('cde_user.manager.user');
    }

    public function getTransactionManager()
    {
        return $this->get('cde_cart.manager.transaction');
    }

    public function getProductManager()
    {
        return $this->get('cde_cart.manager.product');
    }

    public function getCartManager()
    {
        return $this->get('cde_cart.manager.cart');
    }
    protected function getDiscountManager()
    {
        return $this->get('cde_cart.manager.discount');
    }
    protected function getTokenManager()
    {
        return $this->get('cde_stripe.manager.token');
    }
    protected function getSubscriptionManager()
    {
        return $this->get('cde_subscription.manager.subscription');
    }
    protected function getPageManager()
    {
        return $this->get('cde_content.manager.page');
    }
    protected function getGalleryManager()
    {
        return $this->get('cde_content.manager.gallery');
    }
    protected function getCommentManager()
    {
        return $this->get('cde_content.manager.comment');
    }

    /**
     * Convenience Methods
     */
    protected function getParameter($request, $name, $normalize = false) {
        $params = $request->request->all();
        if (isset($params[$name])) {
            if ($normalize) {
                if (strtolower($params[$name]) === 'true') {
                    $params[$name] = true;
                } else if (strtolower($params[$name]) === 'false') {
                    $params[$name] = false;
                }
            }
            return $params[$name];
        } else {
            return null;
        }
    }

    protected function getParameters($request, $names) {
        $params = $request->request->all();
        foreach ($names as $name) {
            if (!isset($params[$name])) {
                $result[$name] = null;
            }
        }
        return $params;

    }

    /**
     * Actions
     */
    public function loginAction()
    {
        $userController = new UserController();
        $userController->container = $this->container;
        return $userController->loginPartialAction();
    }

    public function registerAction(Request $request)
    {
        $userController = new UserController();
        $userController->container = $this->container;
        return $userController->registerPartialAction($request);
    }

    public function resetAction()
    {
        $userController = new UserController();
        $userController->container = $this->container;
        return $userController->resetPartialAction();
    }

    public function listImagesAction($prefix)
    {
        $response = $this->getAwsManager()->listImages($prefix);
        $view = $this->view($response, 200)
            ->setHeader('Expires', gmdate('D, d M Y H:i:s', strtotime('+1 days')) . ' GMT')
            ->setHeader('Cache-Control', 'max-age=86400, public')
            ->setFormat('json');
        return $this->handleView($view);
    }

    public function userAction()
    {
        $user = $this->getUser();
        $view = $this->view($user, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function userUpdateAction(Request $request) {
        $user = $this->getUser();
        if (!$user) {
            $view = $this->view(array('error' => 'Not found'), 401)->setFormat('json');
        } else {
            $password = $this->getParameter($request, 'password');
            $verification = $this->getParameter($request, 'verification');
            $oldPassword = $this->getParameter($request, 'oldpassword');
            $email = $this->getParameter($request, 'email');
            $commentEmail = $this->getParameter($request, 'comment_email', true);

            if ( isset($password) && ($password !== $verification) ) {
                $view = $this->view(array('error' => 'Passwords do not match', 'field' => 'verification'), 200)->setFormat('json');
            } else if (isset($password) && strlen($password) < 3) {
                $view = $this->view(array('error' => 'Password is too short', 'field' => 'password'), 200)->setFormat('json');

            } else if (isset($email) && count($this->getUserManager()->validateEmail($email))) {
                $view = $this->view(array('error' => 'Invalid email', 'field' => 'email'), 200)->setFormat('json');

            } else if (!$this->getUserManager()->checkPassword($user, $oldPassword)) {
                $view = $this->view(array('error' => 'Bad password', 'field' => 'oldpassword'), 200)->setFormat('json');

            } else {
                if (isset($email)) {
                    $user->setEmail($email);
                }
                if (isset($password)) {
                    $user->setPlainPassword($password);
                    $this->getUserManager()->updatePassword($user);
                }

                if (isset($commentEmail)) {
                    $user->setCommentEmail($commentEmail);
                }

                $this->getUserManager()->update($user);
                $view = $this->view($user, 200)->setFormat('json');
            }

        }
        return $this->handleView($view);
    }

    public function addressAction()
    {
        $user = $this->getUser();
        $view = $this->view($user->getAddress(), 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function addressUpdateAction(Request $request) {
        $user = $this->getUser();
        if (!$user) {
            $view = $this->view(array('error' => 'Not found'), 401)->setFormat('json');
        } else {
            $params = $request->request->all();
            if (!isset($params['first'])) {
                $view = $this->view(array('error' => 'First name is required', 'field' => 'first'), 200)->setFormat('json');
            } else if (!isset($params['last'])) {
                $view = $this->view(array('error' => 'Last name is required', 'field' => 'last'), 200)->setFormat('json');
            } else {
                $params = $this->getParameters($request, array('first', 'last', 'phone', 'line1', 'line2', 'line3', 'city', 'state', 'code', 'country', 'instructions'));
                $address = $user->getAddress();
                if (!isset($address)) {
                    $address = $this->getAddressManager()->create();
                    $address->setUser($user);
                }
                $address->setFirst($params['first']);
                $address->setLast($params['last']);
                $address->setPhone($params['phone']);
                $address->setLine1($params['line1']);
                $address->setLine2($params['line2']);
                $address->setLine3($params['line3']);
                $address->setCity($params['city']);
                $address->setState($params['state']);
                $address->setCode($params['code']);
                $address->setCountry($params['country']);
                $address->setInstructions($params['instructions']);

                $this->getAddressManager()->update($address);

                $view = $this->view($address, 200)->setFormat('json');
            }

        }
        return $this->handleView($view);
    }

    public function transactionAction($id = null)
    {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(401)->setFormat('json');
        } else {
            if (isset($id)) {
                $transaction = $this->getTransactionManager()->findByUser($this->getUser(), $id);
            } else {
                $transaction = $user->getTransactions()->getValues();
            }

            $view = $this->view($transaction, 200)->setFormat('json');
        }


        return $this->handleView($view);
    }

    public function productAction($id = null)
    {
        if (isset($id)) {
            $product = $this->getProductManager()->find($id);
        } else {
            $product = $this->getProductManager()->findActive();
        }

        $view = $this->view($product, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function cartAction() {
        $view = $this->view($this->getCartManager()->find($this->getUser()), 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function addToCartAction(Request $request) {
        $quantity = intval($this->getParameter($request, 'quantity'));
        $productId = intval($this->getParameter($request, 'product_id'));

        if (!isset($productId) || $productId === 0) {
            $view = $this->view(array('error' => 'Product not found', 'field' => 'product_id'), 200)->setFormat('json');
        } else if (!isset($quantity) || $quantity < 1) {
            $view = $this->view(array('error' => 'Quantity not positive'), 200)->setFormat('json');
        } else {
            $user = $this->getUser();
            $product = $this->getProductManager()->findActive($productId);

            if (!isset($product) || !isset($product[0])) {
                $view = $this->view(array('error' => 'Product not found', 'field' => 'product_id'), 200)->setFormat('json');
            } else {
                $product = $product[0];
                $cart = $this->getCartManager()->find($user);
                $available = $product->getAvailable();

                if (!isset($cart)) {
                    $view = $this->view(array('error' => 'Cart not found'), 200)->setFormat('json');
                } else if (isset($available) && $available < $quantity) {
                    $view = $this->view(array('error' => 'Requested quantity not available', 'field' => 'quantity'), 200)->setFormat('json');
                } else {
                    $this->getCartManager()->addProduct($product, $user, intval($quantity));
                    $view = $this->view($this->getCartManager()->find($user), 200)->setFormat('json');
                }
            }
        }


        return $this->handleView($view);

    }

    public function updateCartAction(Request $request)
    {
        $quantity = intval($this->getParameter($request, 'quantity'));
        $productId = intval($this->getParameter($request, 'product_id'));

        if (!isset($quantity)) {
            $quantity = 0;
        }
        if (!isset($productId)) {
            $productId = 0;
        }

        $user = $this->getUser();
        $cart = $this->getCartManager()->find($user);
        $product = $this->getProductManager()->find($productId);

        if ($productId === 0 || !isset($product)) {
            $this->getCartManager()->clear($cart, $user, true);
        } else if ($quantity === 0) {
            foreach ($cart->getProducts() as $productToRemove) {
                if ($productToRemove->getSlug() === $product->getSlug()) {
                    $this->getCartManager()->removeProduct($product, $user, $productToRemove->getQuantity());
                }
            }


        } else {
            $this->getCartManager()->setQuantity($cart, $user, $product->getId(), $quantity);
        }

        $view = $this->view($this->getCartManager()->find($user), 200)->setFormat('json');
        return $this->handleView($view);

    }

    public function discountAction(Request $request) {
        $user = $this->getUser();
        $cart = $this->getCartManager()->find($user);
        $code = $this->getParameter($request, 'code');

        if (!isset($code) || $code === 0) {
            $cart->setDiscount(null);
            $this->getCartManager()->update($cart, $user);
            $view = $this->view($this->getCartManager()->find($user), 200)->setFormat('json');
        } else {
            $discount = $this->getDiscountManager()->findByCode($code);
            if (isset($discount)) {
                $now = time();
                $date = $discount->getExpiresDate()->format('U');
                $expiration = intval($date);
            }

            if (!isset($discount)) {
                $view = $this->view(array('error' => 'Discount not found'), 200)->setFormat('json');
            } else if ($discount->getUses() >= $discount->getMaxUses()) {
                $view = $this->view(array('error' => 'Discount has exceeded maximum uses'), 200)->setFormat('json');
            } else if ($now > $expiration) {
                $view = $this->view(array('error' => 'Discount has expired'), 200)->setFormat('json');
            } else {
                $cart->setDiscount($discount);
                $this->getCartManager()->update($cart, $user);
                $view = $this->view($this->getCartManager()->find($user), 200)->setFormat('json');
            }
        }

        return $this->handleView($view);

    }

    public function paramsAction() {
        $params = array(
            'stripePK' => $this->container->getParameter('stripePK'),
            'stripeSK' => 'THAT IS A SECRET DUMMY'
        );
        $view = $this->view($params, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function tokenAction() {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
        } else {
            $view = $this->view($user->getToken(), 200)->setFormat('json');
        }
        return $this->handleView($view);
    }

    public function tokenCreateAction(Request $request) {
        $stripe_id = $this->getParameter($request, 'id');
        $livemode = $this->getParameter($request, 'livemode');
        $created = $this->getParameter($request, 'created');
        $used = $this->getParameter($request, 'used');
        $type = $this->getParameter($request, 'type');
        $card = $this->getParameter($request, 'card');

        $user = $this->getuser();

        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
        } else if (isset($stripe_id) && isset($livemode) && isset($created) && isset($used) && isset($type) && isset($card)) {
            $token = $user->getToken();
            if (!isset($token)) {
                $token = $this->getTokenManager()->create();
                $user->setToken($token);
            }

            $token->setStripeId($stripe_id);
            $token->setLivemode($livemode);
            $token->setCreated($created);
            $token->setUsed($used);
            $token->setType($type);
            $token->setCard($card);
            $token->setUser($user);

            $this->getTokenManager()->add($token);

            $view = $this->view($user->getToken(), 200)->setFormat('json');

        } else {
            $view = $this->view(array('error' => 'Token parameter missing'), 200)->setFormat('json');
        }

        return $this->handleView($view);
    }

    public function stripeCheckoutAction() {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $cart = $user->getCart();
        $products = $cart->getProducts();
        if (!isset($products) || count($products) < 1) {
            $view = $this->view(array('error' => 'Cart is empty'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $token = $user->getToken();
        if (!isset($token)) {
            $view = $this->view(array('error' => 'Credit card not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $transaction = $this->getTransactionManager()->newStripeTransaction($cart, $token);

        $view = $this->view($transaction, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function subscriptionAction($id = null) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        if (!isset($id)) {
            $subscriptions = $this->getSubscriptionManager()->findByUser($user);
            $view = $this->view($subscriptions, 200)->setFormat('json');

        } else {
            $subscription = $this->getSubscriptionManager()->find($id);
            if (!isset($subscription) || $user->getId() !== $subscription->getUser()->getId()) {
                $view = $this->view('Subscription not found', 200)->setFormat('json');
            } else {
                $view = $this->view($subscription, 200)->setFormat('json');
            }

        }
        return $this->handleView($view);

    }

    public function subscriptionResetAction($id) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $subscription = $this->getSubscriptionManager()->find($id);
        if (!isset($subscription) || $user->getId() !== $subscription->getUser()->getId()) {
            $view = $this->view('Subscription not found', 200)->setFormat('json');
        } else {
            $this->getSubscriptionManager()->resetSubscription($subscription);
            $view = $this->view($subscription, 200)->setFormat('json');
        }

        return $this->handleView($view);
    }

    public function contentAction($slug) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        // Make sure that the user actually has an active subscription to this product.
        $subscriptions = $this->getSubscriptionManager()->findByUser($user);
        foreach ($subscriptions as $subscription) {
            if ($subscription->getProduct()->getSlug() === $slug) {
                $product = $subscription->getProduct();
            }
        }

        if (!isset($product)) {
            $view = $this->view(array('error' => 'Product not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        if (!$subscription->getReset()) { // Force a reset on this subscription if it hasn't had one already.
            $this->getSubscriptionManager()->resetSubscription($subscription);
        } else if ($this->getSubscriptionManager()->isExpired($subscription)) {
            // Bail if expired
            $view = $this->view(array('error' => 'Subscription has expired'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        // Get pages
        $pages = $this->getPageManager()->findBySubscription($subscription);
        $view = $this->view($pages, 200)->setFormat('json');




        return $this->handleView($view);
    }

    public function pageAction($slug) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $pages = $this->getPageManager()->findByUser($user);
        foreach ($pages as $prospect) {
            if ($prospect->getSlug() === $slug) {
                $page = $prospect;
            }
        }

        if (!isset($page)) {
            $view = $this->view(array('error' => 'Page not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $page = $this->getAwsManager()->signPageUrls($page);
        $view = $this->view($page, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function galleryAction($id = null) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        if (isset($id)) {
            $gallery = $this->getGalleryManager()->findAbsolute($id);
            if (!isset($gallery) || $gallery->getUser()->getId() !== $user->getId()) {
                $view = $this->view(array('error' => 'Gallery not found'), 200)->setFormat('json');
            } else {
                $signedUri = $this->getAwsManager()->getSignedUriByFilename($gallery->getFilename());
                $gallery->setSignedUri($signedUri);
                $view = $this->view($gallery, 200)->setFormat('json');
            }

        } else {
            $galleries = $this->getGalleryManager()->findByUser($user);
            //DO NOT SET SIGNED URLS. Only go through that by request
            $view = $this->view($galleries, 200)->setFormat('json');

        }

        return $this->handleView($view);
    }

    public function galleryCreateAction(Request $request) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $file = $request->files->get('file');
        $title = $this->getParameter($request, 'title');
        $description = $this->getParameter($request, 'description');

        $gallery = $this->getGalleryManager()->create();
        $gallery->setTitle($title);
        $gallery->setDescription($description);
        $gallery->setUser($user);

        $filename = $user->getUsername().'-'.uniqid().'.'.$file->guessExtension();
        $aws_folder = $this->container->getParameter('aws_gallery_folder');
        $file->move('../app/gallery', $filename);
        $destination = $aws_folder.'/'.$filename;
        $this->getAwsManager()->copyGalleryFile($destination, '../app/');
        $gallery->setFilename($destination);
        $this->getGalleryManager()->add($gallery);

        $view = $this->view($gallery, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function galleryDeleteAction($id) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $gallery = $this->getGalleryManager()->findAbsolute($id);
        if (!isset($gallery) || $gallery->getUser()->getId() !== $user->getId()) {
            $view = $this->view(array('error' => 'Gallery not found'), 200)->setFormat('json');
        } else {
            $this->getGalleryManager()->remove($gallery);
            $view = $this->view(array('message' => 'Gallery deleted', 'id' => $id), 200)->setFormat('json');
        }

        return $this->handleView($view);
    }

    public function commentAction($id = null) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }


        if (isset($id)) {
            $comment = $this->getCommentManager()->findAbsolute($id);
            if (!isset($comment) || $comment->getGalleryuser()->getId() !== $user->getId()) {
                $view = $this->view(array('error' => 'Comment not found'), 200)->setFormat('json');
            } else {
                $view = $this->view($comment, 200)->setFormat('json');
            }

        } else {
            $comments = $this->getCommentManager()->findByGalleryUser($user);
            $view = $this->view($comments, 200)->setFormat('json');
        }

        return $this->handleView($view);
    }

    public function commentCreateAction($id, Request $request) {
        $user = $this->getUser();
        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found'), 200)->setFormat('json');
            return $this->handleView($view);
        }

        $gallery = $this->getGalleryManager()->findAbsolute($id);
        if (!isset($gallery) || $gallery->getUser()->getId() !== $user->getId()) {
            $view = $this->view(array('error' => 'Gallery not found'), 200)->setFormat('json');
        } else {
            $commentText = $request->request->get('comment');

            if (!isset($commentText)) {
                $view = $this->view(array('error' => 'Comment empty'), 200)->setFormat('json');
            } else {
                $comment = $this->getCommentManager()->create();
                $comment->setGallery($gallery);
                $comment->setComment($commentText);
                $comment->setGalleryuser($user);
                $this->getCommentManager()->add($comment);

                $view = $this->view($comment, 200)->setFormat('json');
            }


        }

        return $this->handleView($view);
    }


}
