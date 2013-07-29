<?php

namespace CDE\ContentBundle\Entity;

use CDE\ContentBundle\Model\TagManagerInterface;
use Doctrine\ORM\EntityManager;
use CDE\ContentBundle\Entity\Tag;
use CDE\ContentBundle\Model\TagInterface;
use CDE\UserBundle\Entity\User;

class TagManager implements TagManagerInterface
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
        $tag = new Tag();
        $tag->setActive(TRUE);
        return $tag;
    }
    
    public function add(TagInterface $tag)
    {
        $this->em->persist($tag);
        $this->em->flush();
    }
    
    public function update(TagInterface $tag)
    {
        $this->em->persist($tag);
        $this->em->flush();
    }
    
    public function remove(TagInterface $tag)
    {
        $this->em->remove($tag);
        $this->em->flush();
    }
    
    public function find($id = NULL)
    {
        if ($id) {
            $tag = $this->repo->find($id);
        } else {
            $tag = $this->repo->findBy(
                array()
            );
        }
        return $tag;
    }
    
    public function findBySlug($slug)
    {
        $tag = $this->repo->findBySlug($slug);
        return $tag;
    }
    
    public function getTreeArray()
    {
        return $this->repo->childrenHierarchy();
    }

    public function findByUser(User $user) {
        $query = $this->em->createQuery('
            select l, m, n
            from CDEContentBundle:Tag l
            join l.products m
            join m.subscriptions n
            where n.user = :user
        ')->setParameter('user', $user->getId());
        $tags = $query->getResult();
        return $tags;
    }
    
    public function getToc(TagInterface $tag)
    {
         $query = $this->em->createQuery('
            select l
            from CDEContentBundle:Page l
            join l.tags m
            where m.id = :id
            order by l.sort asc
        ')->setParameter('id', $tag->getId());
        $pages = $query->getResult();
        return $pages;
    }
}
