<?php

namespace CDE\CartBundle\Entity;

use CDE\CartBundle\Controller\CartController;
use CDE\CartBundle\Model\TransactionManagerInterface;
use CDE\StripeBundle\Entity\Token;
use CDE\StripeBundle\Entity\TokenManager;
use CDE\UserBundle\Entity\User;
use CDE\UserBundle\Entity\UserManager;
use Doctrine\ORM\EntityManager;
use CDE\CartBundle\Entity\Transaction;
use CDE\CartBundle\Model\TransactionInterface;
use CDE\SubscriptionBundle\Entity\SubscriptionManager;
use CDE\CartBundle\Entity\DiscountManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TransactionManager implements TransactionManagerInterface
{
    protected $em;
    protected $mailer;
    protected $subscriptionManager;
    protected $discountManager;
    protected $awsManager;
    protected $productManager;
    protected $cartManager;
    protected $tokenManager;
    protected $userManager;
    protected $class;
    protected $repo;
    protected $paginator;
    protected $templating;
    protected $stripeSK;
    protected $admin;
    protected $deliverAll;

    public function __construct(EntityManager $em, $mailer, SubscriptionManager $subscriptionManager, DiscountManager $discountManager, $awsManager, ProductManager $productManager, CartManager $cartManager, TokenManager $tokenManager, UserManager $userManager, $class, $paginator, $templating, $stripeSK, $admin, $deliverAll){
        $this->em = $em;
        $this->mailer = $mailer;
        $this->subscriptionManager = $subscriptionManager;
        $this->discountManager = $discountManager;
        $this->awsManager = $awsManager;
        $this->productManager = $productManager;
        $this->cartManager = $cartManager;
        $this->tokenManager = $tokenManager;
        $this->userManager = $userManager;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
        $this->paginator = $paginator;
        $this->templating = $templating;
        $this->stripeSK = $stripeSK;
        $this->admin = $admin;
        $this->deliverAll = $deliverAll;

        \Stripe::setApiKey($this->stripeSK);
        
    }
    
    public function create()
    {
        $transaction = new Transaction();
        return $transaction;
    }
    
    public function add(TransactionInterface $transaction)
    {
        $this->em->persist($transaction);
        $this->em->flush();
    }
    
    public function update(TransactionInterface $transaction)
    {
        $products = $transaction->getProducts();
        
        // This is super dirty, but Doctrine does not persist changes in array collections!
        $empty = new ArrayCollection();
        $transaction->setProducts($empty);
        $this->em->persist($transaction);
        $this->em->flush();
        $transaction->setProducts($products);
        $this->em->flush();
    }
    
    public function remove(TransactionInterface $transaction)
    {
        $this->em->remove($transaction);
        $this->em->flush();
    }

    public function find($id = NULL)
    {
        if ($id) {
            $transaction = $this->repo->find($id);
        } else {
            $transaction = $this->repo->findBy(
                array(),
                array('created' => 'DESC')
            );
        }
        return $transaction;
    }

    public function findByPage($page = 1, $limit = 10, $queryFilter = array())
    {
        $queryText = '
            select l, m
            from CDECartBundle:Transaction l
            join l.user m
        ';

        $counter = 0;
        $params = array();
        foreach($queryFilter as $k => $v) {
            $k = preg_replace('/_/', '.', $k);
            $counter += 1;
            if (strtolower($v) === 'false') {
                $v = false;
            } else if (strtolower($v) === 'true') {
                $v = true;
            }
            $params['param'.$counter] = $v;
            $queryText .= 'where '.$k.' = :param'.$counter;
        }

        $query = $this->em->createQuery($queryText);

        foreach($params as $k => $v) {
            $query = $query->setParameter($k, $v);
        }

        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $pagination;
    }

    public function findByUser($user, $id = NULL)
    {
        if ($id) {
            $transaction = $this->repo->findBy(
                array('id' => $id, 'user' => $user->getId())
            );
            if (count($transaction) > 0) {
                $transaction = $transaction[0];
            } else {
                $transaction = array();
            }

        } else {
            $transaction = $this->repo->findBy(
                array('user' => $user->getId()),
                array('created' => 'DESC')
            );
        }
        if (!$transaction) {
            throw new NotFoundHttpException();
        }
        return $transaction;
    }
    
    public function sendProducts($transaction, $email = TRUE)
    {
        /**
         * Physical products get emailed in batch to shipping email
         * Digital products get emailed in batch to user (after URI gets signed)
         * Gift products get emailed individually to user (After getting created)
         * Subscription products get their tags added to the user's tags
         */
        $user = $transaction->getUser();
        $updatedProducts = new ArrayCollection();
        foreach ($transaction->getProducts() as $tempProduct) {
            // Find the original product to avoid persisting new products
            $product = $this->productManager->find($tempProduct->getId());
            $available = $product->getAvailable();
            if (isset($available) && $available > 0) { // Decrement available if applicable
                $product->setAvailable($available - 1);
                $this->productManager->update($product);
            }
            if ($product->getType() === 'subscription') {
                for ($i=0; $i < $tempProduct->getQuantity(); $i++) {
                    $subscription = $this->subscriptionManager->create();
                    $subscription->setUser($user);
                    $subscription->setProduct($product);
                    
                    //Add time to existing subscriptions instead of duplicating them
                    $existing = $this->subscriptionManager->checkExisiting($subscription);
                    if ($existing) {
                        $existing = $existing->addDays($subscription->getProduct()->getDays());
                        
                        // Doctrine refuses to recognize the change in date... so I'm forcing the issue
                        // This is the second time this has hit me.  I hate doing this.
                        $expires = $existing->getExpires();
                        $existing->setExpires(new \DateTime());
                        $this->subscriptionManager->update($existing);
                        $existing->setExpires($expires);
                        $this->subscriptionManager->update($existing);
                    } else {
                        $this->subscriptionManager->add($subscription);
                    }
                }
            } else if ($product->getType() === 'gift') {
                // Enabled product.quantity > 1
                for ($i=0; $i < $tempProduct->getQuantity(); $i++) { 
                    $discount = $this->discountManager->create();
                    $transaction->setDiscount($discount);
                    $discount->setValue($product->getDiscountValue());
                    $discount->setPercent($product->getDiscountPercent());
                    $discount->setMaxUses(1);
                    $discount->setExpires($product->getExpiration());
                    $discount->setProduct($product);
                    $product->addDiscountCode($discount->getCode());
                    $product->setDiscountExpiration($discount->getExpiresDate());
                    $this->discountManager->add($discount);
                }
            } else if ($product->getType() === 'digital') {
                $signedUri = $this->awsManager->getSignedUri($product->getUri(), 7);
                $product->setSignedUri($signedUri);
            }
            //Return updated products... otherwise the signed URIs will be lost
			$product->setQuantity($tempProduct->getQuantity());
            $updatedProducts->add($product);
        }
        return $updatedProducts;
    }

    public function newStripeTransaction(User $user, Cart $cart, Token $token, $survey = array()) {
        //Set up transaction
        $transaction = $this->create();
        $transaction->setUser($user);
        $transaction->setSurvey($survey);

        //Sum up products
        $total = 0;
        $products = $cart->getProducts()->getValues();
        $transaction->setProducts($products);
        foreach ($products as $product) {
            $total += $product->getPrice() * $product->getQuantity();
        }

        //Apply discounts
        $discount = $cart->getDiscount();
        $discountTotal = 0;

        if (isset($discount)) {


            $percent = $discount->getPercent();
            $value = $discount->getValue();
            if (isset($percent) && $percent > 0) {
                $discountTotal += max(0, $total * $percent);
            }

            if (isset($value) && $value > 0) {
                $discountTotal += max(0, $value);
            }
            $total = max(0, $total - $discountTotal);

            $transaction->setDiscount($discount);
            $transaction->setDiscountApplied($discountTotal);

        } else {
            $transaction->setDiscountApplied(0);
        }

        $transaction->setAmount($total);
        if ($total === 0) {
            $transaction->setStatus('Free Checkout');
            $transaction->setDetails(array('code' => $transaction->getDiscount()->getCode()));
            $this->add($transaction);
        } else {
            //Charge via Stripe
            $stripeResponse = \Stripe_Charge::create(array(
                'amount' => round($total * 100),
                'currency' => 'usd',
                'card' => $token->getStripeId(),
                'description' => 'Charge for '.$token->getUser()->getEmail(),
                'capture' => true
            ));
            if ($stripeResponse['failure_code']) {
                return array('error' => $stripeResponse['failure_message']);
            } else {
                $user->removeToken();
                $this->userManager->update($user);
                $stripeResponse = $stripeResponse->__toArray();
                $stripeResponse['card'] = $stripeResponse['card']->__toArray();
            }
            $transaction->setStatus('Completed');
            $transaction->setDetails($stripeResponse);
            $this->add($transaction);
        }

        $updatedProducts = $this->sendProducts($transaction);
        $transaction->setProducts($updatedProducts);

//        $cartController = new CartController();
//        $cartController->sendEmail($transaction, $this->admin);

        $this->sendEmail($transaction);

        // Increment discount code uses before clearing cart
        $discount = $cart->getDiscount();
        if ($discount) {
            $dbDiscount = $this->discountManager->findByCode($discount->getCode());
            $dbDiscount->incrementUses();
            $this->discountManager->update($dbDiscount);
        }

        // Decrement products available where possible. Must use the original cart products ($products)
        $this->cartManager->clear($cart, $user); // cartManager->clear() will re-increment the products as if they're going back on the shelves.

        $this->update($transaction);
        return $transaction;


    }

    public function getCartTotal(Cart $cart) {
        $total = 0;
        $products = $cart->getProducts()->getValues();
        foreach ($products as $product) {
            $total += $product->getPrice() * $product->getQuantity();
        }

        $discount = $cart->getDiscount();
        $discountTotal = 0;

        if (isset($discount)) {
            $percent = $discount->getPercent();
            $value = $discount->getValue();
            if (isset($percent) && $percent > 0) {
                $discountTotal += max(0, $total * $percent);
            }

            if (isset($value) && $value > 0) {
                $discountTotal += max(0, $value);
            }
            $total = max(0, $total - $discountTotal);

        }

        return $total;

    }

    protected function sendEmail($transaction)
    {
        $admin = $this->admin;

        $bcc = $this->deliverAll;
        $primaryMessage = \Swift_Message::newInstance()
            ->setSubject($admin['email_from_name'].': Order #'.$transaction->getId())
            ->setFrom($admin['admin_email'])
            ->setTo($transaction->getUser()->getEmail())
            ->addBcc($bcc)
            ->setBody($this->templating->render('CDECartBundle:Mail:angular.neworder.txt.twig', array(
                'transaction' => $transaction
            )));
        $this->mailer->send($primaryMessage);

        foreach ($transaction->getProducts() as $product) {
            if ($product->getType() === 'gift') {
                $giftMessage = \Swift_Message::newInstance()
                    ->setSubject($admin['email_from_name'].': Your gift code')
                    ->setFrom($admin['admin_email'])
                    ->setTo($transaction->getUser()->getEmail())
                    ->setBody($this->templating->render('CDECartBundle:Mail:angular.gift.txt.twig', array(
                        'product' => $product
                    )));
                $this->mailer->send($giftMessage);
            }
        }
    }

}
