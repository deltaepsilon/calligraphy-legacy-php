<?php

namespace CDE\ContentBundle\Entity;

use CDE\ContentBundle\Model\PageManagerInterface;
use CDE\SubscriptionBundle\Entity\Subscription;
use Doctrine\ORM\EntityManager;
use CDE\ContentBundle\Entity\Page;
use CDE\ContentBundle\Model\PageInterface;
use CDE\UserBundle\Entity\User;

class PageManager implements PageManagerInterface
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
        $page = new Page();
        $page->setActive(TRUE);
        return $page;
    }
    
    public function add(PageInterface $page)
    {
        $this->saveTags($page);
        $this->em->persist($page);
        $this->em->flush();
    }
    
    public function update(PageInterface $page)
    {
        $this->saveTags($page);
        $this->em->persist($page);
        $this->em->flush();
    }
    
    public function remove(PageInterface $page)
    {
        $this->em->remove($page);
        $this->em->flush();
    }
    
    public function find($id = NULL)
    {
        if ($id) {
            $page = $this->repo->find($id);
        } else {
            $query = $this->em->createQuery('
                select l, m
                from CDEContentBundle:Page l
                join l.tags m
                order by l.sort asc
            ');
            $page = $query->getResult();
        }
        return $page;
    }
    
    public function findBySlug($slug)
    {
        $page = $this->repo->findBySlug($slug);
        return $page;
    }

    public function findParentTag(PageInterface $page)
    {
        foreach ($page->getTags() as $tag) {
            if ($tag->getLvl() === 0) {
                return $tag;
            }
        }
        return $tag;
    }
    
    public function findByUser(User $user)
    {
        $query = $this->em->createQuery('
                select l, m, n, o
                from CDEContentBundle:Page l
                join l.tags m
                join m.products n
                join n.subscriptions o
                where o.user = :id
                 and o.expires > :now
            ')
            ->setParameter('id', $user->getId())
            ->setParameter('now', new \DateTime())
            ;
        $pages = $query->getResult();
        return $pages;
    }
    
    protected function saveTags(PageInterface $page) {
        /**
         *  Forms tend to not to respect array collections...
         * I'm using an entity field with a single select drop down in one of my forms, 
         * and entity types like to return single objects in that situation.  This function
         * requires an array collection, so I need to treat objects differently.  The 
         * addTag function treats objects correctly.
         */
        $tags = $page->getTags();
        if (is_object($tags)) {
            // When adding subtags, you get a PersistentCollection of tags, not a Tag
            if (get_class($tags) === 'Doctrine\ORM\PersistentCollection') {
                foreach ($tags->getValues() as $tag) {
                    // $page->addTag($tag);
                }
            } else {
                $page->addTag($tags);
            }
        }
    }
    
    public function validatePage(PageInterface $page, User $user)
    {
        $today = new \DateTime();
        $query = $this->em->createQuery('
            select count(l)
            from CDEContentBundle:Page l
            join l.tags m
            join m.products n
            join n.subscriptions o
            where o.user = :user
             and l.id = :page
             and o.expires >= :date
        ')
        ->setParameter('page', $page->getId())
        ->setParameter('user', $user->getId())
        ->setParameter('date', $today);
        $countArray = $query->getResult();
        $count = intval($countArray[0][1]);
        if ($count) {
            return TRUE;
        }
        return FALSE;
    }
    
    public function findBySort()
    {
        $pages = $this->repo->findBy(
            array(),
            array('sort' => 'ASC')
        );
        return $pages;
    }

    public function findBySubscription(Subscription $subscription)
    {
        $query = $this->em->createQuery('
                select l, m, n, o
                from CDEContentBundle:Page l
                join l.tags m
                join m.products n
                join n.subscriptions o
                where o.user = :userId
                 and o.expires > :now
                order by l.sort
            ')
            ->setParameter('userId', $subscription->getUser()->getId())
            ->setParameter('now', new \DateTime())
        ;
        $pages = $query->getResult();
        return $pages;
    }

}
