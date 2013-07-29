<?php

namespace CDE\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class AdminController extends Controller
{
    
    public function indexAction()
    {
        return $this->render('CDEAdminBundle:Admin:index.html.twig', array(
        
        ));
    }
}
