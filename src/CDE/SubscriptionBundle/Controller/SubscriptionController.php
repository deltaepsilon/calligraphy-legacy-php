<?php

namespace CDE\SubscriptionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\SubscriptionBundle\Form\Type\SubscriptionType;
use CDE\SubscriptionBundle\Form\Type\SubscriptionNewType;
use Symfony\Component\HttpFoundation\Request;


class SubscriptionController extends Controller
{
    protected function getSubscriptionManager()
    {
        return $this->get('cde_subscription.manager.subscription');
    }
    
    public function indexAction()
    {
        $subscriptions = $this->getSubscriptionManager()->find();
        return $this->render('CDESubscriptionBundle:Subscription:index.html.twig', array(
            'subscriptions' => $subscriptions,
        ));
    }

    public function viewAction($id)
    {
        $subscription = $this->getSubscriptionManager()->find($id);
        return $this->render('CDESubscriptionBundle:Subscription:view.html.twig', array(
            'subscription' => $subscription,
        ));
    }
    
    public function createAction(Request $request)
    {
        $subscription = $this->getSubscriptionManager()->create();
        $form = $this->createForm(new SubscriptionNewType(), $subscription);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                // Add subscription to existing subscription if possible
                $existing = $this->getSubscriptionManager()->checkExisiting($subscription);
                if ($existing) {
                    $existing = $existing->addDays($subscription->getProduct()->getDays());
                    
                    // Doctrine refuses to recognize the change in date... so I'm forcing the issue
                    // This is the second time this has hit me.  I hate doing this.
                    $expires = $existing->getExpires();
                    $existing->setExpires(new \DateTime());
                    $this->getSubscriptionManager()->update($existing);
                    $existing->setExpires($expires);
                    $this->getSubscriptionManager()->update($existing);
                    
                    return $this->redirect($this->generateUrl('CDESubscriptionBundle_view', array('id' => $existing->getId())));
                } else {
                    $this->getSubscriptionManager()->add($subscription);
                    return $this->redirect($this->generateUrl('CDESubscriptionBundle_view', array('id' => $subscription->getId())));
                }
                
            }
        }
        // Render form
        return $this->render('CDESubscriptionBundle:Subscription:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $subscription = $this->getSubscriptionManager()->find($id);
        $form = $this->createForm(new SubscriptionType(), $subscription);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getSubscriptionManager()->update($subscription);
                return $this->redirect($this->generateUrl('CDESubscriptionBundle_view', array('id' => $subscription->getId())));
            }
        }
        // Render form
        return $this->render('CDESubscriptionBundle:Subscription:update.html.twig', array(
            'form' => $form->createView(),
            'subscription' => $subscription,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $subscription = $this->getSubscriptionManager()->find($id);
        $form = $this->createFormBuilder($subscription)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($subscription, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$subscription->__toString());
                $this->getSubscriptionManager()->remove($subscription);
                return $this->redirect($this->generateUrl('CDESubscriptionBundle_index'));
            }
        }
        // Render form
        return $this->render('CDESubscriptionBundle:Subscription:delete.html.twig', array(
            'form' => $form->createView(),
            'subscription' => $subscription,
        ));
    }

}
