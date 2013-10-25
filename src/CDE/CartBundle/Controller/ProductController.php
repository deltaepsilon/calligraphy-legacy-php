<?php

namespace CDE\CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\CartBundle\Form\Type\ProductSubscriptionType;
use CDE\CartBundle\Form\Type\ProductPhysicalType;
use CDE\CartBundle\Form\Type\ProductDigitalType;
use CDE\CartBundle\Form\Type\ProductGiftType;
use CDE\CartBundle\Form\Type\ProductAddTagsType;
use Symfony\Component\HttpFoundation\Request;


class ProductController extends Controller
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
        $products = $this->getProductManager()->find();
        return $this->render('CDECartBundle:Product:index.html.twig', array(
            'products' => $products,
        ));
    }

    public function viewAction($slug)
    {
        $product = $this->getProductManager()->findBySlug($slug);
        $user = $this->getUser();
		$cart = $this->getCartManager()->find($user);
        // Get signed url if user is admin and there is a url to sign
        if($user) {
            if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
                $uri = $product->getUri();
                if ($uri) {
                    $signedUri = $this->get('cde_utility.manager.aws')->getSignedUri($uri);
                    $product->setSignedUri($signedUri);
                }
            }
        }
		//Update product with cart quantities
        if (isset($product)) {
            $product = $this->getProductManager()->setTempAvailable($product, $cart->getProducts());
        }

		return $this->render('CDECartBundle:Product:view.html.twig', array(
            'product' => $product
        ));
    }
    
    public function createAction(Request $request, $type)
    {
        $categoryMap = array(
            'subscription' => 'workshop',
            'digital' => 'download',
            'gift' => 'gift',
            'physical' => 'physical'
        );
        $product = $this->getProductManager()->create();
        $class = 'CDE\CartBundle\Form\Type\Product'.ucwords($type).'Type';
        $product->setType($type);
        $product->setCategory($categoryMap[$type]);
        $product->setActive(TRUE);
        $form = $this->createForm(new $class(), $product);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getProductManager()->add($product);
                return $this->redirect($this->generateUrl('CDECartBundle_product_view', array('slug' => $product->getSlug())));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Product:create.html.twig', array(
            'form' => $form->createView(),
            'type' => $type,
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $product = $this->getProductManager()->find($id);
        $type = $product->getType();
        $class = 'CDE\CartBundle\Form\Type\Product'.ucwords($type).'Type';
        $form = $this->createForm(new $class(), $product);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getProductManager()->update($product);
                return $this->redirect($this->generateUrl('CDECartBundle_product_view', array('slug' => $product->getSlug())));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Product:update.html.twig', array(
            'form' => $form->createView(),
            'product' => $product,
            'type' => $type,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $product = $this->getProductManager()->find($id);
        $form = $this->createFormBuilder($product)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($product, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$product->__toString());
                $this->getProductManager()->remove($product);
                return $this->redirect($this->generateUrl('CDECartBundle_product_index'));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Product:delete.html.twig', array(
            'form' => $form->createView(),
            'product' => $product,
        ));
    }

    public function tagAction(Request $request, $id)
    {
        $product = $this->getProductManager()->find($id);
        $form = $this->createForm(new ProductAddTagsType(), $product);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getProductManager()->update($product);
                return $this->redirect($this->generateUrl('CDECartBundle_product_view', array('slug' => $product->getSlug())));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Product:tag.html.twig', array(
            'form' => $form->createView(),
            'product' => $product,
        ));
    }

}
