<?php

namespace CDE\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\UserBundle\Form\Type\AddressType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class AddressController extends Controller
{
    protected function getAddressManager()
    {
        return $this->get('cde_user.manager.address');
    }
    
    protected function getAddress($id) {
        $user = $this->getUser();
        if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            $address = $this->getAddressManager()->find($id);
        } else {
            $address = $user->getAddress();
        }
        if (!$address) {
            throw new NotFoundHttpException();
        }
        return $address;
    }
    
    public function indexAction()
    {
        $addresses = $this->getAddressManager()->find();
        return $this->render('CDEUserBundle:Address:index.html.twig', array(
            'addresses' => $addresses,
        ));
    }

    public function viewAction($id)
    {
        $address = $this->getAddress($id);
        return $this->render('CDEUserBundle:Address:view.html.twig', array(
            'address' => $address,
        ));
    }
    
    public function createAction(Request $request)
    {
        $address = $this->getAddressManager()->create();
        $address->setUser($this->getUser());
        $form = $this->createForm(new AddressType(), $address);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getAddressManager()->update($address);
                return $this->redirect($this->generateUrl('CDEUserBundle_address_view', array('id' => $address->getId())));
            }
        }
        // Render form
        return $this->render('CDEUserBundle:Address:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $address = $this->getAddress($id);
        $form = $this->createForm(new AddressType(), $address);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getAddressManager()->add($address);
                return $this->redirect($this->generateUrl('CDEUserBundle_address_view', array('id' => $address->getId())));
            }
        }
        // Render form
        return $this->render('CDEUserBundle:Address:update.html.twig', array(
            'form' => $form->createView(),
            'address' => $address,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $address = $this->getAddress($id);
        $form = $this->createFormBuilder($address)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($address, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$address->__toString());
                $this->getAddressManager()->remove($address);
                return $this->redirect($this->generateUrl('CDEUserBundle_account_view'));
            }
        }
        // Render form
        return $this->render('CDEUserBundle:Address:delete.html.twig', array(
            'form' => $form->createView(),
            'address' => $address,
        ));
    }

}
