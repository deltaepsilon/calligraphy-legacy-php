<?php

namespace CDE\AffiliateBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class AffiliateType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('affiliate')
            ;
    }
    
    public function getName()
    {
        return 'cde_affiliate';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\AffiliateBundle\Entity\Affiliate',
        );
    }
}
