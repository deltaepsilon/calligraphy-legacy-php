<?php

namespace CDE\ContentBundle\Entity;

use CDE\ContentBundle\Model\GalleryManagerInterface;
use Doctrine\ORM\EntityManager;
use CDE\ContentBundle\Entity\Gallery;
use CDE\ContentBundle\Model\GalleryInterface;
use CDE\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalleryManager implements GalleryManagerInterface
{
    protected $em;
    protected $class;
    protected $repo;
    protected $aws;
    
    public function __construct(EntityManager $em, $class, $aws, $paginator){
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
        $this->aws = $aws;
        $this->paginator = $paginator;
    }
    
    public function create()
    {
        $gallery = new Gallery();
        return $gallery;
    }
    
    public function add(GalleryInterface $gallery)
    {
        $this->em->persist($gallery);
        $this->em->flush();
    }
    
    public function update(GalleryInterface $gallery)
    {
        $this->em->persist($gallery);
        $this->em->flush();
    }
    
    public function remove(GalleryInterface $gallery)
    {
        $this->aws->deleteGalleryFile($gallery->getFilename());
        $this->em->remove($gallery);
        $this->em->flush();
    }
    
    public function find(User $user, $id = NULL)
    {
        if ($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            if ($id) {
                $query = $this->em->createQuery('
                    select l
                    from CDEContentBundle:Gallery l
                    where l.id = :id
                ')->setParameter('id', $id);
                try {
                    $gallery = $query->getSingleResult();
                } catch (\Doctrine\ORM\NoResultException $e) {
                    throw new NotFoundHttpException();
                }
            } else {
                $gallery = $this->repo->findBy(
                    array(),
                    array('created' => 'DESC'),
		    200
                );
            }
        } else {
            if ($id) {
                $query = $this->em->createQuery('
                    select l
                    from CDEContentBundle:Gallery l
                    where l.id = :id
                     and l.user = :user
                ')->setParameter('id', $id)
                //join l.comments m
                ->setParameter('user', $user->getId());
                try {
                    $gallery = $query->getSingleResult();
                } catch (\Doctrine\ORM\NoResultException $e) {
                    throw new NotFoundHttpException();
                }
            } else {
                $gallery = $this->repo->findBy(
                    array('user' => $user->getId()),
                    array('created' => 'DESC')
                );
            }            
        }
        // if (count($gallery) === 0) {
            // throw new NotFoundHttpException();
        // }
        return $gallery;
    }

    public function findByPage($page = 1, $limit = 20, $queryFilter = array())
    {
        $queryText = '
            select l
            from CDEContentBundle:Gallery l
            join l.user m
        ';

        $counter = 0;
        $params = array();
        foreach($queryFilter as $k => $v) {
            $k = preg_replace('/_/', '.', $k);
            $counter += 1;
            if (strtolower($v) === 'false') {
                $v = false;
            } else if (strtolower($v) === 'true') {
                $v = true;
            }
            $params['param'.$counter] = $v;
            $queryText .= 'where '.$k.' = :param'.$counter;
        }

        $query = $this->em->createQuery($queryText);

        foreach($params as $k => $v) {
            $query = $query->setParameter($k, $v);
        }

        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $limit
        );

        // if (count($gallery) === 0) {
        // throw new NotFoundHttpException();
        // }
        return $pagination;
    }

    public function findAbsolute($id)
    {
        return $this->repo->find($id);
    }

    public function findByUser(User $user)
    {
        $gallery = $this->repo->findBy(
            array('user' => $user->getId()),
            array('created' => 'DESC')
        );
        return $gallery;
    }

}
