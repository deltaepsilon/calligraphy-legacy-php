<?php

namespace CDE\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class EmailController extends Controller
{
    public function viewAction($name)
    {
        if (!$name) {
            return $this->redirect($this->generateUrl('CDEUtilityBundle_index'));
        }
        return $this->render("CDEUtilityBundle:Email:$name.html.twig");
    }
}
