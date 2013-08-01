<?php

namespace CDE\UserBundle\Entity;

use CDE\UserBundle\Model\AddressManagerInterface;
use Doctrine\ORM\EntityManager;
use CDE\UserBundle\Entity\Address;
use CDE\UserBundle\Model\AddressInterface;

class AddressManager implements AddressManagerInterface
{
    protected $em;
    protected $class;
    protected $repo;
    
    public function __construct(EntityManager $em, $class, $paginator){
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
        $this->paginator = $paginator;
    }
    
    public function create()
    {
        $address = new Address();
        return $address;
    }
    
    public function add(AddressInterface $address)
    {
        $this->em->persist($address);
        $this->em->flush();
    }
    
    public function update(AddressInterface $address)
    {
        $this->em->persist($address);
        $this->em->flush();
    }
    
    public function remove(AddressInterface $address)
    {
        $this->em->remove($address);
        $this->em->flush();
    }
    
    public function find($id = NULL)
    {
        if ($id) {
            $address = $this->repo->find($id);
        } else {
            $address = $this->repo->findBy(
                array(),
                array('last' => 'ASC')
            );
        }
        return $address;
    }

}
