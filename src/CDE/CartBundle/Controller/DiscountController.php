<?php

namespace CDE\CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\CartBundle\Form\Type\DiscountType;
use Symfony\Component\HttpFoundation\Request;


class DiscountController extends Controller
{
    protected function getDiscountManager()
    {
        return $this->get('cde_cart.manager.discount');
    }
    
    public function indexAction()
    {
        $discounts = $this->getDiscountManager()->find();
        return $this->render('CDECartBundle:Discount:index.html.twig', array(
            'discounts' => $discounts,
        ));
    }

    public function viewAction($id)
    {
        $discount = $this->getDiscountManager()->find($id);
        return $this->render('CDECartBundle:Discount:view.html.twig', array(
            'discount' => $discount,
        ));
    }
    
    public function createAction(Request $request)
    {
        $discount = $this->getDiscountManager()->create();
        $form = $this->createForm(new DiscountType(), $discount);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getDiscountManager()->add($discount);
                return $this->redirect($this->generateUrl('CDECartBundle_discount_view', array('id' => $discount->getId())));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Discount:create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $discount = $this->getDiscountManager()->find($id);
        $form = $this->createForm(new DiscountType(), $discount);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getDiscountManager()->update($discount);
                return $this->redirect($this->generateUrl('CDECartBundle_discount_view', array('id' => $discount->getId())));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Discount:update.html.twig', array(
            'form' => $form->createView(),
            'discount' => $discount,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $discount = $this->getDiscountManager()->find($id);
        $form = $this->createFormBuilder($discount)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($discount, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$discount->__toString());
                $this->getDiscountManager()->remove($discount);
                return $this->redirect($this->generateUrl('CDECartBundle_discount_index'));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Discount:delete.html.twig', array(
            'form' => $form->createView(),
            'discount' => $discount,
        ));
    }

}
