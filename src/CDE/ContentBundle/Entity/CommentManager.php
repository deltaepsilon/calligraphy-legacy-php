<?php

namespace CDE\ContentBundle\Entity;

use CDE\ContentBundle\Model\CommentManagerInterface;
use Doctrine\ORM\EntityManager;
use CDE\ContentBundle\Entity\Comment;
use CDE\ContentBundle\Model\CommentInterface;
use CDE\UserBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentManager implements CommentManagerInterface
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
        $comment = new Comment();
        return $comment;
    }
    
    public function add(CommentInterface $comment)
    {
        $this->em->persist($comment);
        $this->em->flush();
    }
    
    public function update(CommentInterface $comment)
    {
        $this->em->persist($comment);
        $this->em->flush();
    }
    
    public function remove(CommentInterface $comment)
    {
        $this->em->remove($comment);
        $this->em->flush();
    }
    
    public function find($id = NULL)
    {
        if ($id) {
            $comment = $this->repo->find($id);
        } else {
            $comment = $this->repo->findBy(
                array(),
                array('created' => 'DESC'),
		150,
		0
            );
        }
        return $comment;
    }

    public function findByPage($page = 1, $limit = 10, $queryFilter = array())
    {
        $queryText = '
            select l, m
            from CDEContentBundle:Comment l
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

        return $pagination;
    }

    public function findAbsolute($id)
    {
        return $this->repo->find($id);
    }

    public function findByUser(User $user)
    {
        $comment = $this->repo->findBy(
            array('user' => $user->getId()),
            array('created' => 'DESC')
        );
        return $comment;
    }

    public function findByGalleryUser(User $user)
    {
        $comment = $this->repo->findBy(
            array('galleryuser' => $user->getId()),
            array('created' => 'DESC')
        );
        return $comment;
    }

}
