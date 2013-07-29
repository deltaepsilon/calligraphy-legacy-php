<?php

namespace CDE\CartBundle\Entity;

use CDE\CartBundle\Model\TransactionManagerInterface;
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
    protected $class;
    protected $repo;
    
    public function __construct(EntityManager $em, $mailer, SubscriptionManager $subscriptionManager, DiscountManager $discountManager, $awsManager, $productManager, $class){
        $this->em = $em;
        $this->mailer = $mailer;
        $this->subscriptionManager = $subscriptionManager;
        $this->discountManager = $discountManager;
        $this->awsManager = $awsManager;
        $this->productManager = $productManager;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
        
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

    public function findByUser($user, $id = NULL)
    {
        if ($id) {
            $transaction = $this->repo->findBy(
                array('id' => $id, 'user' => $user->getId())
            );
            $transaction = $transaction[0];
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
                var_dump($signedUri);
                $product->setSignedUri($signedUri);
            }
            //Return updated products... otherwise the signed URIs will be lost
			$product->setQuantity($tempProduct->getQuantity());
            $updatedProducts->add($product);
        }
        return $updatedProducts;
    }

}
