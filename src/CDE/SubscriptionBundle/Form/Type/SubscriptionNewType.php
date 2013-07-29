<?php

namespace CDE\SubscriptionBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

class SubscriptionNewType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('user', 'entity', array('class' => 'CDEUserBundle:User'))
            ->add('product', 'entity', array(
                'class' => 'CDECartBundle:Product',
                'label' => 'Product',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('l')->where("l.type = 'subscription'");
                },
                ))
            ;
    }
    
    public function getName()
    {
        return 'cde_subscription';
    }
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class'=>'CDE\SubscriptionBundle\Entity\Subscription',
        );
    }
}
