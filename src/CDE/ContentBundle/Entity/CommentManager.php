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
    
    public function __construct(EntityManager $em, $class){
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
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
