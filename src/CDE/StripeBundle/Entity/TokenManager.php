<?php
/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 10/14/13
 * Time: 2:44 PM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\StripeBundle\Entity;


use CDE\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class TokenManager {
    protected $em;
    protected $class;
    protected $repo;

    public function __construct(EntityManager $em, $class) {
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
    }

    public function create() {
        return new Token();

    }

    public function add(Token $token) {
        $this->em->persist($token);
        $this->em->flush();
    }

    public function update(Token $token) {
        $this->em->persist($token);
        $this->em->flush();
    }

    public function remove(Token $token) {
        $this->em->remove($token);
        $this->em->flush();
    }



}