<?php

namespace CDE\ContentBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class PageUpdateType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('active', 'checkbox', array('required' => FALSE))
            ->add('title')
            ->add('html')
            ->add('css')
            ;
    }
    
    public function getName()
    {
        return 'cde_page';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\ContentBundle\Entity\Page',
        );
    }
}
