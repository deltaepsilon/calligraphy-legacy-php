<?php

namespace CDE\StripeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CDEStripeBundle:Default:index.html.twig', array('name' => $name));
    }
}
