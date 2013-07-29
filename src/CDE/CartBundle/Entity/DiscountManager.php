<?php

namespace CDE\CartBundle\Entity;

use CDE\CartBundle\Model\DiscountManagerInterface;
use Doctrine\ORM\EntityManager;
use CDE\CartBundle\Entity\Discount;
use CDE\CartBundle\Model\DiscountInterface;

class DiscountManager implements DiscountManagerInterface
{
    protected $em;
    protected $class;
    protected $repo;
    
    public function __construct(EntityManager $em, $class){
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
    }
    
    public function create()
    {
        $discount = new Discount();
        return $discount;
    }
    
    public function add(DiscountInterface $discount)
    {
        $this->em->persist($discount);
        $this->em->flush();
    }
    
    public function update(DiscountInterface $discount)
    {
        $this->em->persist($discount);
        $this->em->flush();
    }
    
    public function remove(DiscountInterface $discount)
    {
        $this->em->remove($discount);
        $this->em->flush();
    }

    public function find($id = NULL)
    {
        if ($id) {
            $discount = $this->repo->find($id);
        } else {
            $discount = $this->repo->findBy(
                array(),
                array('id' => 'ASC')
            );
        }
        return $discount;
    }
    
    public function findByCode($code)
    {
        $discount = $this->repo->findBy(
                array('code' => $code)
            );
        if (count($discount) === 0) {
            return NULL;
        }
        return $discount[0];
    }

}
