<?php

namespace CDE\SubscriptionBundle\Entity;

use CDE\SubscriptionBundle\Model\SubscriptionManagerInterface;
use Doctrine\ORM\EntityManager;
use CDE\CartBundle\Entity\Product;
use CDE\SubscriptionBundle\Model\SubscriptionInterface;
use CDE\UserBundle\Entity\User;

class SubscriptionManager implements SubscriptionManagerInterface
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
        $subscription = new Subscription();
        $subscription->setExpires(new \DateTime());
        return $subscription;
    }
    
    public function add(SubscriptionInterface $subscription)
    {
        $date = new \DateTime();
        $days = $subscription->getProduct()->getDays();
        $date->add(new \DateInterval('P'.$days.'D'));
        $subscription->setExpires($date);
        $this->em->persist($subscription);
        $this->em->flush();
    }
    
    public function update(SubscriptionInterface $subscription)
    {
        $this->em->persist($subscription);
        $this->em->flush();
    }
    
    public function remove(SubscriptionInterface $subscription)
    {
        $this->em->remove($subscription);
        $this->em->flush();
    }
    
    public function find($id = NULL)
    {
        if ($id) {
            $subscription = $this->repo->find($id);
        } else {
            $query = $this->em->createQuery('
                select l, m, n
                from CDESubscriptionBundle:Subscription l
                join l.product m
                join l.user n
                order by l.created desc
            ');
            $subscription = $query->getResult();
        }
        return $subscription;
    }

    public function findByPage($page = 1, $limit = 10)
    {
        $query = $this->em->createQuery('
            select l
            from CDESubscriptionBundle:Subscription l
        ');

        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $pagination;
    }
    
    public function checkExisiting(SubscriptionInterface $subscription)
    {
        $query = $this->em->createQuery('
                select l
                from CDESubscriptionBundle:Subscription l
                where l.user = :user
                 and l.product = :product
            ')
            ->setParameter('user', $subscription->getUser()->getId())
            ->setParameter('product', $subscription->getProduct()->getId());
        try {
            $existing = $query->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return NULL;
        }
        return $existing;
    }

    public function findByUser(User $user)
    {
        $query = $this->em->createQuery('
            select l, m
            from CDESubscriptionBundle:Subscription l
            join l.product m
            where l.user = :user
            order by l.created desc
        ')->setParameter('user', $user->getId());
        $subscription = $query->getResult();
        return $subscription;
    }
}
