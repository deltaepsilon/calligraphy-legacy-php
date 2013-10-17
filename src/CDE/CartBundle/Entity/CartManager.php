<?php

namespace CDE\CartBundle\Entity;

use CDE\CartBundle\Model\CartManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use CDE\CartBundle\Model\CartInterface;
use CDE\CartBundle\Model\ProductInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartManager implements CartManagerInterface
{
    protected $em;
    protected $class;
    protected $repo;
	protected $sessionManager;
    protected $productManager;
    
    public function __construct(EntityManager $em, $class, $sessionManager, ProductManager $productManager){
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
        $this->sessionManager = $sessionManager;
        $this->productManager = $productManager;

    }
    
    public function create($user = NULL)
    {
        $cart = new Cart();
        if($user) {
            $cart->setUser($user);
        }
        return $cart;
    }
    
    public function add(CartInterface $cart, $user = NULL)
    {
        if ($user) {
            $this->em->persist($cart);
            $this->em->flush();
        } else {
            $this->sessionManager->set('cart', $cart);
        }
    }

    public function update(CartInterface $cart, $user = NULL)
    {
        if ($user) {
            $products = $cart->getProducts();

            //Sort products by slug for consistency
            $products = $products->toArray();
            usort($products, function($a, $b) {
                return $a->getSlug() > $b->getSlug();
            });
            $products = new ArrayCollection($products);


            /**
             * Is this little two-step ugly as sin?  Yes.  It is, and I know it
             * Unfortunately, Doctrine doesn't notice changes in array collections
             * unless they're erased and flushed first.  It's super lame and I would love
             * to find a way around it.
             */
            // Empty products array
            $this->em->persist($cart);
            $cart->setProducts();
            $this->em->flush();

            // Refill products array
            $cart->setProducts($products);
            $this->em->persist($cart);
            $this->em->flush();
        } else {
            $this->sessionManager->set('cart', $cart);
        }
    }

    
    public function clear(CartInterface $cart, $user = NULL, $increment = false)
    {
        if ($user) {
			foreach ($cart->getProducts() as $product) {
                // Increment the product availability in the DB
                if ($increment) {
                    $dbProduct = $this->productManager->find($product->getId());
                    if (isset($dbProduct)) {
                        $dbProduct->incrementAvailable($product->getQuantity());
                        $this->productManager->update($dbProduct);
                    }
                }


				$cart->removeProduct($product, 0);
			}
			$cart->setDiscount(null);

			//Can't just remove cart.  Caused a concurrency bug with another cart lookup.
			//$this->em->remove($cart);
			$this->update($cart, $user);
        } else {
            $this->sessionManager->set('cart', NULL);
        }
    }

    public function find($user = NULL)
    {
        if ($user) {
            $cart = $user->getCart();
            if (!$cart) {
                $cart = $this->create($user);
                $this->add($cart, $user);
            }
            // Add session cart items to user cart
            $sessionCart = $this->sessionManager->get('cart');
            if ($sessionCart) {
                foreach ($sessionCart->getProducts() as $product) {
                    /**
                     * addProduct increments each product quantity by one,
                     * but each product from a session cart already has its quantity,
                     * so you have to decrement the session cart by one before combining
                     */
                    $quantity = $product->getQuantity();
                    $product->setQuantity($quantity - 1);
                    $cart->addProduct($product);
                }
                $this->sessionManager->set('cart', NULL);
                unset($sessionCart);
            }
            $this->update($cart, $user);
        } else {
            $cart = $this->sessionManager->get('cart');
        }
        if (!$cart) {
            $cart = $this->create($user);
        }
        return $cart;
    }

    public function addProduct(ProductInterface $product, $user = NULL, $count = 1)
    {
        if ($user) {
            $cart = $user->getCart();
            if (!$cart) {
                $cart = $this->create($user);
            }
        } else {
            $cart  = $this->find($user);
        }
		$available = $product->getAvailable();
		if (is_null($available)) {
			$maxCount = $count;
		} else {
			$maxCount = min($available, $count);
		}
        for ($i=0; $i < $maxCount; $i++) {
            $cart->addProduct($product);
        }
        $product->decrementAvailable($maxCount);
        $this->productManager->update($product);

        $this->update($cart, $user);
		if ($maxCount > 0) {
			return true;
		}
		return false;
    }

    public function removeProduct(ProductInterface $product, $user = NULL, $count = 1)
    {
        if ($user) {
            $cart = $user->getCart();
        } else {
            $cart  = $this->find($user);
        }

        if (!$cart) {
            throw new NotFoundHttpException();
        }

        $cart->removeProduct($product, $count);

        $dbProduct = $this->productManager->find($product->getId());
        $dbProduct->incrementAvailable($count);
        $this->productManager->update($dbProduct);

        $this->update($cart, $user);
    }

    public function setQuantity(Cart $cart, $user, $id, $quantity) {
        foreach ($cart->getProducts() as $product) {
            if ($product->getId() === $id) {
                $this->removeProduct($product, $user, $product->getQuantity());
                if ($quantity > 0) {
                    $this->addProduct($product, $user, $quantity);
                }
            }
        }

        $cart = $this->find($user);
        return $cart;
    }
    
    public function getCartValue(CartInterface $cart)
    {
        $value = 0;
        foreach ($cart->getProducts() as $product) {
            $value += $product->getPrice() * $product->getQuantity();
        }
        return $value;
    }

    public function getPaypalLink(CartInterface $cart, $parameters)
    {
        $products = $cart->getProducts();
        $link = $parameters['paypal_nvp'].'?METHOD=SetExpressCheckout&PAYMENTREQUEST_0_PAYMENTACTION=Sale';
        
        // Set Params
        $linkParams = array();
        $linkParams['USER'] = $parameters['paypal_username'];
        $linkParams['PWD'] = $parameters['paypal_password'];
        $linkParams['SIGNATURE'] = $parameters['paypal_signature'];
        $linkParams['VERSION'] = $parameters['paypal_version'];
        $linkParams['RETURNURL'] = $parameters['return_url'];
        $linkParams['CANCELURL'] = $parameters['cancel_url'];
        // exit;
        $n = 0;
        $m = 0;
        $amount = 0;
        foreach ($products as $product) {
            $amount += $product->getPrice() * $product->getQuantity();
            $linkParams['L_PAYMENTREQUEST_'.$n.'_NAME'.$m] = $product->getTitle();
            $linkParams['L_PAYMENTREQUEST_'.$n.'_NUMBER'.$m] = $product->getId();
           // $linkParams['L_PAYMENTREQUEST_'.$n.'_DESC'.$m] = $product->getDescription();
            $linkParams['L_PAYMENTREQUEST_'.$n.'_AMT'.$m] = $product->getPrice();
            $linkParams['L_PAYMENTREQUEST_'.$n.'_QTY'.$m] = $product->getQuantity();
            $m ++;
        }
        
        // Apply discounts
        $discount = $cart->getDiscount();
        if ($discount) {
            $value = $discount->getValue();
            $percent = $discount->getPercent();
            if ($value) {
                if ($value >= $amount) {
                    $discountValue = $amount;
                    $amount = 0;
                } else {
                    $discountValue = $value;
                    $amount -= $discountValue;
                }
            }
            if ($percent) {
                $percentValue = $amount * $percent;
                if ($percentValue >= $amount) {
                    $discountValue += $percentValue;
                    $amount = 0;
                } else {
                    $discountValue = $percentValue;
                    $amount -= $discountValue;
                }
            }
            $linkParams['L_PAYMENTREQUEST_'.$n.'_NAME'.$m] = "Discount code";
            $linkParams['L_PAYMENTREQUEST_'.$n.'_NUMBER'.$m] = $discount->getCode();
            $linkParams['L_PAYMENTREQUEST_'.$n.'_DESC'.$m] = $discount->getDescription();
            $linkParams['L_PAYMENTREQUEST_'.$n.'_AMT'.$m] = -1 * $discountValue;
            $linkParams['L_PAYMENTREQUEST_'.$n.'_QTY'.$m] = 1;
        }
        
        $linkParams['PAYMENTREQUEST_'.$n.'_CURRENCYCODE'] = $parameters['paypal_currency_code'];
        $linkParams['PAYMENTREQUEST_'.$n.'_ITEMAMT'] = $amount;
        $linkParams['PAYMENTREQUEST_'.$n.'_AMT'] = $amount;
        $linkParams['ALLOWNOTE'] = 1;
        
        foreach ($linkParams as $key => $value) {
            $link .= '&'.$key.'='.urlencode($value);
        }
        return $link;
    }

    public function getRedirectLink($responseParams, $parameters)
    {
        $link = $parameters['paypal_webscr'].'?useraction=commit&cmd=_express-checkout';
        $linkParams = array();
        $linkParams['token'] = $responseParams['TOKEN'];
        foreach ($linkParams as $key => $value) {
            $link .= '&'.$key.'='.urlencode($value);
        }
        return $link;
    }

    public function getDetailsLink($parameters)
    {
        $link = $parameters['paypal_nvp'].'?METHOD=GetExpressCheckoutDetails';
        $linkParams['USER'] = $parameters['paypal_username'];
        $linkParams['PWD'] = $parameters['paypal_password'];
        $linkParams['SIGNATURE'] = $parameters['paypal_signature'];
        $linkParams['VERSION'] = $parameters['paypal_version'];
        $linkParams['TOKEN'] = $parameters['token'];
        foreach ($linkParams as $key => $value) {
            $link .= '&'.$key.'='.urlencode($value);
        }
        return $link;
    }
    
    public function getPaypalFinal($responseParams, $parameters)
    {
        $link = $parameters['paypal_nvp'].'?METHOD=DoExpressCheckoutPayment';
        $linkParams['USER'] = $parameters['paypal_username'];
        $linkParams['PWD'] = $parameters['paypal_password'];
        $linkParams['SIGNATURE'] = $parameters['paypal_signature'];
        $linkParams['VERSION'] = $parameters['paypal_version'];
        $linkParams['TOKEN'] = $responseParams['TOKEN'];
        $linkParams['PAYERID'] = $responseParams['PAYERID'];
        $linkParams['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';
        $linkParams['PAYMENTREQUEST_0_AMT'] = $responseParams['PAYMENTREQUEST_0_AMT'];
        foreach ($linkParams as $key => $value) {
            $link .= '&'.$key.'='.urlencode($value);
        }
        return $link; 
    }

}
