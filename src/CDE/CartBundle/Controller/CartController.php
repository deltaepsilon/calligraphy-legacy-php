<?php

namespace CDE\CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\CartBundle\Entity\Cart;
use CDE\CartBundle\Entity\Product;
use CDE\CartBundle\Form\Type\CartType;
use CDE\CartBundle\Form\Type\CartProductType;
use CDE\CartBundle\Form\Type\CartDiscountType;
use CDE\CartBundle\Entity\Transaction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CartController extends Controller
{
    protected function getCartManager()
    {
        return $this->get('cde_cart.manager.cart');
    }
    protected function getProductManager()
    {
        return $this->get('cde_cart.manager.product');
    }
    protected function getTransactionManager()
    {
        return $this->get('cde_cart.manager.transaction');
    }
    protected function getDiscountManager()
    {
        return $this->get('cde_cart.manager.discount');
    }
    protected function getMailer()
    {
        return $this->get('mailer');
    }
    public function indexAction(Request $request)
    {
        $cart = $this->getCartManager()->find($this->getUser());
        $form = $this->createForm(new CartType(), $cart);
        
        $discount = $this->getDiscountManager()->create();
        $discount->setCode(NULL);
        $codeForm = $this->createForm(new CartDiscountType(), $discount);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getCartManager()->update($cart);
                $this->get('session')->getFlashBag()->add('notice', "Updated cart.");
            }
        }
        // Render form
        return $this->render('CDECartBundle:Cart:index.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView(),
            'codeForm' => $codeForm->createView(),
        ));
    }

    public function partialAction()
    {
        $cart = $this->getCartManager()->find($this->getUser());
        $count = 0;
        foreach ($cart->getProducts() as $product) {
            $count += $product->getQuantity();
        }
        return $this->render('CDECartBundle:Cart:partial.html.twig', array(
            'cart' => $cart,
            'count' => $count,
        ));
    }

    public function addAction(Request $request, $id)
    {
        $referer = $request->headers->get('referer');
        if (!$referer) {
            $referer = $this->generateUrl('CDECartBundle_store_index');
        }
        $user = $this->getUser();
        $product = $this->getProductManager()->find($id);
        $result = $this->getCartManager()->addProduct($product, $user);
		if ($result == TRUE) {
			$this->get('session')->getFlashBag()->add('notice', "Added ".$product->__toString()." to cart");
		 } else {
			$this->get('session')->getFlashBag()->add('error', "Failed to add ".$product->__toString()." to cart. Insufficient quantity available.");
		}
        return $this->redirect($referer);
    }
    
    public function removeAction(Request $request, $id)
    {
        $user = $this->getUser();
        $referer = $request->headers->get('referer');
        if (!$referer) {
            $referer = $this->generateUrl('CDECartBundle_store_index');
        }
        $cart = $this->getCartManager()->find($user);
        $product = $this->getProductManager()->find($id);
        $this->getCartManager()->removeProduct($product, $user);
        $this->get('session')->getFlashBag()->add('notice', "Removed ".$product->__toString()." from cart");
        return $this->redirect($referer);
    }
    public function emptyAction(Request $request)
    {
        $user = $this->getUser();
        $cart = $this->getCartManager()->find($user);
        $this->getCartManager()->clear($cart, $user);
        $this->get('session')->getFlashBag()->add('notice', "Cart cleared");
        return $this->redirect($this->generateUrl('CDECartBundle_store_index'));
    }
    
    public function addCodeAction(Request $request)
    {
        
        $discount = $this->getDiscountManager()->create();
        $discount->setCode(NULL);
        $form = $this->createForm(new CartDiscountType(), $discount);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            // I'm not validating this form, because it will always fail... it's missing required fields
            $existingDiscount = $this->getDiscountManager()->findByCode($discount->getCode());
            /**
             * Validate discount code
             * I would do this in the manager... but it's harder to setFlash, and I'll never reuse this?
             * 1. It needs to exist
             * 2. I can't be used too many times
             * 3. It can't be expired
             */ 
            if (!$existingDiscount) {
                $this->get('session')->getFlashBag()->add('notice', $discount->getCode()."not found.");
            } else if ($existingDiscount->getUses() >= $existingDiscount->getMaxUses()) {
                $this->get('session')->getFlashBag()->add('notice', $discount->getCode()." has been used too many times.  It cannot be added to this cart.");
            } else {
                $now = time();
                $expirationTime = intval($existingDiscount->getExpiresDate()->format('U'));
                if ($now > $expirationTime) {
                    $this->get('session')->getFlashBag()->add('notice', $discount->getCode()." has expired.  It cannot be added to this cart.");
                } else {
                    
                    $user = $this->getUser();
                    $cart = $this->getCartManager()->find($user);
                    $cart->setDiscount($existingDiscount);
                    $this->getCartManager()->update($cart, $user);
                    $this->get('session')->getFlashBag()->add('notice', "Successfully added ".$existingDiscount->getCode());
                }
            }
        }
        return $this->redirect($this->generateUrl('CDECartBundle_cart_index'));
    }
    
    public function checkoutAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            $this->get('session')->getFlashBag()->add('notice', "Must be logged in to check out");
            return $this->redirect($this->generateUrl('CDECartBundle_store_index'));
        }
        $baseUrl = 'http://'.$request->getHttpHost();
        $parameters = $this->container->getParameter('cde_paypal');
        $parameters['return_url'] = $baseUrl.$this->generateUrl('CDECartBundle_cart_success');
        $parameters['cancel_url'] = $baseUrl.$this->generateUrl('CDECartBundle_cart_index');
        $link = $this->getCartManager()->getPaypalLink($user->getCart(), $parameters);
        $response = file_get_contents($link);
        $responseUrl = parse_url($response);
        parse_str($responseUrl['path'], $responseParams);
        if ($responseParams['ACK'] == 'Failure') {
//	    var_dump($link, $responseParams);
            throw new NotFoundHttpException();
        }
        $redirect = $this->getCartManager()->getRedirectLink($responseParams, $parameters);
        return $this->redirect($redirect);
    }

    public function freeAction(Request $request)
    {
        $user = $this->getUser();
        $cart = $user->getCart();
        $products = $cart->getProducts();
        $discount = $cart->getDiscount();
        $total = $this->getCartManager()->getCartValue($cart);
        $transaction = $this->getTransactionManager()->create();
        $transaction->setUser($user);
        $transaction->setDiscount($cart->getDiscount());
        $transaction->setProducts($products);
        // $transaction->setDetails();
        $transaction->setPayment($discount->getCode());
        $transaction->setAmount(0);
        $transaction->setStatus('Free Checkout');
        $transaction->setDiscountApplied($total);
        
        // Deliver new products, or at least queue them up
        $admin = $this->container->getParameter('admin');
        $updatedProducts = $this->getTransactionManager()->sendProducts($transaction);
        $transaction->setProducts($updatedProducts);
        $this->getTransactionManager()->update($transaction);
        $this->sendEmail($transaction, $admin);
        
        // Increment discount code uses before clearing cart
        if ($discount) {
            $discount->incrementUses();
        }

		// Decrement products available where possible. Must use the original cart products ($products)
		foreach ($products as $product) {
			$dbProduct = $this->getProductManager()->find($product->getId());
			if ($dbProduct->getAvailable()) {
				$dbProduct->decrementAvailable($product->getQuantity());
				$this->getProductManager()->update($dbProduct);
			}
		}
        $this->getCartManager()->clear($cart, $user);
        return $this->redirect($this->generateUrl('CDECartBundle_transaction_customer_view', array('id' => $transaction->getId())));
    }
    
    public function successAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException();
        }
        $parameters = $this->container->getParameter('cde_paypal');
        $parameters['token'] = $request->get('token');
        $details = $this->getCartManager()->getDetailsLink($parameters);
        $response = file_get_contents($details);
        $responseUrl = parse_url($response);
        parse_str($responseUrl['path'], $responseParams);
        if ($responseParams['ACK'] != 'Success') {
            throw new NotFoundHttpException();
        }
        $final = $this->getCartManager()->getPaypalFinal($responseParams, $parameters);
        $responseFinal = file_get_contents($final);
        $responseFinalUrl = parse_url($responseFinal);
        parse_str($responseFinalUrl['path'], $responseFinalParams);
        
        // Save transaction
        $cart = $this->getUser()->getCart();
        $products = $cart->getProducts();
        $discount = $cart->getDiscount();
        $total = $this->getCartManager()->getCartValue($cart);
        $transaction = $this->getTransactionManager()->create();
        $transaction->setUser($user);
        $transaction->setDiscount($cart->getDiscount());
        $transaction->setProducts($products);
        $transaction->setDetails($responseParams);
        $transaction->setPayment($responseFinalParams);
        $transaction->setAmount($responseFinalParams['PAYMENTINFO_0_AMT']);
        $transaction->setStatus($responseFinalParams['PAYMENTINFO_0_PAYMENTSTATUS']);
        $transaction->setDiscountApplied($total - $transaction->getAmount());
        
        // Deliver new products, or at least queue them up
        $admin = $this->container->getParameter('admin');
        $updatedProducts = $this->getTransactionManager()->sendProducts($transaction);
        $transaction->setProducts($updatedProducts);
        $this->getTransactionManager()->update($transaction);
        $this->sendEmail($transaction, $admin);
        
        // Increment discount code uses before clearing cart
        $discount = $cart->getDiscount();
        if ($discount) {
            $discount->incrementUses();
        }
		// Decrement products available where possible. Must use the original cart products ($products)
		foreach ($products as $product) {
			$dbProduct = $this->getProductManager()->find($product->getId());
			if ($dbProduct->getAvailable()) {
				$dbProduct->decrementAvailable($product->getQuantity());
				$this->getProductManager()->update($dbProduct);
			}
		}
        $this->getCartManager()->clear($cart, $user);
        return $this->redirect($this->generateUrl('CDECartBundle_transaction_customer_view', array('id' => $transaction->getId()))."?record=true");
    }

    protected function sendEmail($transaction, $admin)
    {
        $bcc = $this->container->getParameter('mailer_deliver_all');
        $primaryMessage = \Swift_Message::newInstance()
            ->setSubject($admin['email_from_name'].': Order #'.$transaction->getId())
            ->setFrom($admin['admin_email'])
            ->setTo($transaction->getUser()->getEmail())
            ->addBcc($bcc)
            ->setBody($this->renderView('CDECartBundle:Mail:neworder.txt.twig', array(
                'transaction' => $transaction
                )));
        $this->getMailer()->send($primaryMessage);
        
        foreach ($transaction->getProducts() as $product) {
            if ($product->getType() === 'gift') {
                $giftMessage = \Swift_Message::newInstance()
                    ->setSubject($admin['email_from_name'].': Your gift code')
                    ->setFrom($admin['admin_email'])
                    ->setTo($transaction->getUser()->getEmail())
                    ->setBody($this->renderView('CDECartBundle:Mail:gift.txt.twig', array(
                        'product' => $product
                        )));
                $this->getMailer()->send($giftMessage);
            }
        }
    }

}
