<?php

namespace CDE\CartBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class RestController extends FOSRestController
{
    /**
     * Managers
     */
    protected function getDiscountManager()
    {
        return $this->get('cde_cart.manager.discount');
    }

    public function discountAction($id = null)
    {
        $discounts = $this->getDiscountManager()->find($id);
        $view = $this->view($discounts, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function discountUpdateAction($id, Request $request)
    {
        $code = $request->query->get('code');
        $description = $request->query->get('description');
        $expires = $request->query->get('expires');
        $uses = $request->query->get('uses');
        $maxUses = $request->query->get('max_uses');
        $value = $request->query->get('value');
        $percent = $request->query->get('percent');

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

}
