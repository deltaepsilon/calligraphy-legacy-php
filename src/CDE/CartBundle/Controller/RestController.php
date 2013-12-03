<?php

namespace CDE\CartBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class RestController extends FOSRestController
{
    /**
     * Convenience
     */
    protected function getEitherParam($request, $param) {
        $result = $request->query->get($param);
        if (!isset($result)) {
            $result = $request->request->get($param);
        }
        return $result;
    }

    /**
     * Managers
     */
    protected function getDiscountManager()
    {
        return $this->get('cde_cart.manager.discount');
    }

    protected function getTransactionManager()
    {
        return $this->get('cde_cart.manager.transaction');
    }

    public function discountAction($id = null)
    {
        $discounts = $this->getDiscountManager()->find($id);
        $view = $this->view($discounts, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function discountUpdateAction($id, Request $request)
    {

        $code = $this->getEitherParam($request, 'code');
        $description = $this->getEitherParam($request, 'description');
        $expires = $this->getEitherParam($request, 'expires');
        $uses = $this->getEitherParam($request, 'uses');
        $maxUses = $this->getEitherParam($request, 'max_uses');
        $value = $this->getEitherParam($request, 'value');
        $percent = $this->getEitherParam($request, 'percent');

        $discount = $this->getDiscountManager()->find($id);

        if (isset($code)) {
            $discount->setCode($code);
        }
        if (isset($description)) {
            $discount->setDescription($description);
        }
        if (isset($expires)) {
            $discount->setExpires($expires);
        }
        if (isset($uses)) {
            $discount->setUses($uses);
        }
        if (isset($maxUses)) {
            $discount->setMaxUses($maxUses);
        }
        if (isset($value)) {
            $discount->setValue($value);
        }
        if (isset($percent)) {
            $discount->setPercent($percent);
        }

        $this->getDiscountManager()->update($discount);

        $discount = $this->getDiscountManager()->find($id);

        $view = $this->view($discount, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function transactionAction($id)
    {
        $transaction = $this->getTransactionManager()->find($id);
        $view = $this->view($transaction, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function transactionsAction($page = 1, $limit = 10)
    {
        $transactions = $this->getTransactionManager()->findByPage($page, $limit);
        $view = $this->view($transactions, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function transactionUpdateAction($id, Request $request)
    {

        $processed = $this->getEitherParam($request, 'processed');

        if (isset($processed)) {
            if (strtolower($processed) === 'true') {
                $processed = true;
            } else if (strtolower($processed) === 'false') {
                $processed = false;
            }

            $transaction = $this->getTransactionManager()->find($id);
            $transaction->setProcessed($processed);
            $this->getTransactionManager()->update($transaction);
        }

        $transaction = $this->getTransactionManager()->find($id);

        $view = $this->view($transaction, 200)->setFormat('json');
        return $this->handleView($view);
    }

}
