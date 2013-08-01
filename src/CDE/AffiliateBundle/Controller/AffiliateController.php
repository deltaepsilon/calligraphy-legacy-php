<?php

namespace CDE\AffiliateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CDE\AffiliateBundle\Form\Type\AffiliateType;

class AffiliateController extends Controller
{
	protected function getAffiliateManager()
	{
		return $this->get('cde_affiliate.manager.affiliate');
	}

	public function indexAction()
	{
		$affiliates = $this->getAffiliateManager()->find();
		return $this->render('CDEAffiliateBundle:Affiliate:index.html.twig', array(
			'affiliates' => $affiliates,
		));
	}

	public function viewAction($id)
	{
		$affiliates = $this->getAffiliateManager()->find($id);
		return $this->render('CDEAffiliateBundle:Affiliate:view.html.twig', array(
			'affiliate' => $affiliates[0],
			'affiliates' => $affiliates,
		));
	}
    
    public function createAction($name)
    {
		$affiliate = $this->getAffiliateManager()->create();
		$affiliate->setAffiliate($name);
		$affiliate->setIp($_SERVER['REMOTE_ADDR']);
		$this->getAffiliateManager()->add($affiliate);
		return $this->redirect($this->generateUrl('CDEUtilityBundle_index'));
    }

	public function updateAction(Request $request, $id)
	{
		$affiliate = $this->getAffiliateManager()->find($id);
		$affiliate = $affiliate[0];
		$form = $this->createForm(new AffiliateType(), $affiliate);
		// Process form
		if ($request->getMethod() === 'POST') {
			$form->bind($request);
			if($form->isValid()) {
				$this->getAffiliateManager()->update($affiliate);
				return $this->redirect($this->generateUrl('CDEAffiliateBundle_view', array('id' => $affiliate->getId())));
			}
		}
		// Render form
		return $this->render('CDEAffiliateBundle:Affiliate:update.html.twig', array(
			'form' => $form->createView(),
			'affiliate' => $affiliate,
		));
	}
	public function deleteAction(Request $request, $id)
	{
		$affiliate = $this->getAffiliateManager()->find($id);
		$affiliate = $affiliate[0];
		$form = $this->createFormBuilder($affiliate)->add('id', 'hidden')->getForm();
		// Process form
		if($request->getMethod() === 'POST') {
			if ($this->get('validator')->validate($affiliate, array('csrf_only'))) {
				$this->get('session')->getFlashBag()->add('notice', "Deleted ".$affiliate->__toString());
				$this->getAffiliateManager()->remove($affiliate);
				return $this->redirect($this->generateUrl('CDEAffiliateBundle_index'));
			}
		}
		// Render form
		return $this->render('CDEAffiliateBundle:Affiliate:delete.html.twig', array(
			'form' => $form->createView(),
			'affiliate' => $affiliate,
		));
	}
}
