<?php

namespace CDE\ContentBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use CDE\ContentBundle\Form\Type\PageSortSingleType;

class PageSortType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('pages', 'collection', array(
                'type' => new PageSortSingleType(),
            ))
            ;
    }
    
    public function getName()
    {
        return 'cde_page';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\ContentBundle\Entity\PageCollection',
        );
    }
}
