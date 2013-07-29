<?php

namespace CDE\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class FrontController extends Controller
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
        return $this->render('CDEUtilityBundle:Front:index.html.twig', array(
            'user' => $this->getUser(),
        ));
    }
    
    public function metaAction($name)
    {
        return $this->render('CDEUtilityBundle:Front:'.$name.'.html.twig', array(
            'user' => $this->getUser(),
        ));
    }
    
    public function purchaseAction($slug)
    {
        $user = $this->getUser();
        $product = $this->getProductManager()->findActiveBySlug($slug);
        $this->getCartManager()->addProduct($product, $user);
        return $this->redirect($this->generateUrl('CDECartBundle_cart_index'));
    }
}
