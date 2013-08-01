<?php

namespace CDE\CartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\CartBundle\Form\Type\TransactionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class TransactionController extends Controller
{
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

    public function indexAction($page = 1)
    {
        $transactions = $this->getTransactionManager()->findByPage($page, 25);
        // foreach ($transactions as $transaction) {
            // $products = $transaction->getProducts();
            // var_dump($products);
        // }
        // exit;
        
        return $this->render('CDECartBundle:Transaction:index.html.twig', array(
            'transactions' => $transactions,
        ));
    }

    public function customerIndexAction()
    {
        $transactions = $this->getTransactionManager()->findByUser($this->getUser());
        return $this->render('CDECartBundle:Transaction:index.html.twig', array(
            'transactions' => $transactions,
        ));
    }

    public function viewAction($id)
    {
        $transaction = $this->getTransactionManager()->find($id);
        return $this->render('CDECartBundle:Transaction:view.html.twig', array(
            'transaction' => $transaction,
        ));
    }

    public function customerViewAction($id)
    {
        $transaction = $this->getTransactionManager()->findByUser($this->getUser(), $id);
        return $this->render('CDECartBundle:Transaction:view.html.twig', array(
            'transaction' => $transaction,
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $transaction = $this->getTransactionManager()->find($id);
        $form = $this->createForm(new TransactionType(), $transaction);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getTransactionManager()->update($transaction);
                return $this->redirect($this->generateUrl('CDECartBundle_transaction_view', array('id' => $transaction->getId())));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Transaction:update.html.twig', array(
            'form' => $form->createView(),
            'transaction' => $transaction,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $transaction = $this->getTransactionManager()->find($id);
        $form = $this->createFormBuilder($transaction)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($transaction, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$transaction->__toString());
                $this->getTransactionManager()->remove($transaction);
                return $this->redirect($this->generateUrl('CDECartBundle_transaction_index'));
            }
        }
        // Render form
        return $this->render('CDECartBundle:Transaction:delete.html.twig', array(
            'form' => $form->createView(),
            'transaction' => $transaction,
        ));
    }

	public function processAction($id)
    {
        $transaction = $this->getTransactionManager()->find($id);
        $transaction->setProcessed(TRUE);
		$this->getTransactionManager()->update($transaction);
        return $this->redirect($this->generateUrl('CDECartBundle_transaction_view', array('id' => $transaction->getId())));
    }

    public function emailNewOrderAction($id, $send = NULL)
    {
        $transaction = $this->getTransactionManager()->find($id);
        // Send email
        if ($send) {
            $admin = $this->container->getParameter('admin');
            $primaryMessage = \Swift_Message::newInstance()
                ->setSubject($admin['email_from_name'].': Order #'.$transaction->getId())
                ->setFrom($admin['admin_email'])
                ->setTo($transaction->getUser()->getEmail())
                ->setBody($this->renderView('CDECartBundle:Mail:neworder.txt.twig', array(
                    'transaction' => $transaction
                    )));
            $this->getMailer()->send($primaryMessage);
        }
        return $this->render('CDECartBundle:Mail:neworder.txt.twig', array(
            'transaction' => $transaction,
        ));
    }

    public function emailGiftAction($id, $productId, $send = TRUE)
    {
        $productId = intval($productId);
        $transaction = $this->getTransactionManager()->find($id);
        $products = $transaction->getProducts();
        foreach ($products as $product) {
            if ($product->getId() === $productId) {
                $productFound = $product;
                break;
            }
        }
        $matchingProducts = $products->filter(
            function ($element) use ($productId) {
                if($element->getId() === $productId) {
                    return TRUE;
                }
            }
        );
        // This should only ever match one product...
        $product = $matchingProducts->first();
        if (!$product) {
            throw new NotFoundHttpException();
        }
        if ($send) {
            // Send email
            $admin = $this->container->getParameter('admin');
            $giftMessage = \Swift_Message::newInstance()
                        ->setSubject($admin['email_from_name'].': Your gift code')
                        ->setFrom($admin['admin_email'])
                        ->setTo($transaction->getUser()->getEmail())
                        ->setBody($this->renderView('CDECartBundle:Mail:gift.txt.twig', array(
                            'product' => $product
                            )));
            $this->getMailer()->send($giftMessage);
        }
        return $this->render('CDECartBundle:Mail:gift.txt.twig', array(
            'transaction' => $transaction,
            'product' => $product,
        ));
        
    }

}
