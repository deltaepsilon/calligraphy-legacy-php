<?php

namespace CDE\CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class StoreController extends Controller
{
    protected function getProductManager()
    {
        return $this->get('cde_cart.manager.product');
    }

	protected function getCartManager()
	{
		return $this->get('cde_cart.manager.cart');
	}
    
    public function indexAction()
    {
		$user = $this->getUser();
        $products = $this->getProductManager()->findActive();
		$resultProducts = $products;
		try {
			$cart = $this->getCartManager()->find($user);
			$cartProducts = $cart->getProducts();
			$resultProducts = array();
			foreach ($products as $product) {
				$resultProducts[] = $this->getProductManager()->setTempAvailable($product, $cartProducts);
			}
		} catch (\Symfony\Component\Config\Definition\Exception\Exception $e) {

		}

		return $this->render('CDECartBundle:Store:index.html.twig', array(
            'products' => $resultProducts,
        ));
    }

    public function viewAction($slug)
    {
		$user = $this->getUser();
        $product = $this->getProductManager()->findActiveBySlug($slug);
		$cart = $this->getCartManager()->find($user);
		$product = $this->getProductManager()->setTempAvailable($product, $cart->getProducts());
        return $this->render('CDECartBundle:Product:view.html.twig', array(
            'product' => $product,
        ));
    }

}
