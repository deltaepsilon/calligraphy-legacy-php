<?php
/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 9/10/13
 * Time: 3:14 PM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\OAuthBundle\Entity;


use Doctrine\ORM\EntityManager;

class ClientManager {
    protected $em;
    protected $class;
    protected $clientManager;
    protected $paginator;

    public function __construct(EntityManager $em, $class, $codeClass, $clientManager, $paginator) {
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->codeRepo = $this->em->getRepository($codeClass);
        $this->class = $class;
        $this->clientManager = $clientManager;
        $this->paginator = $paginator;
    }

    public function create()
    {
        $client = $this->clientManager->createClient();
        return $client;
    }

    public function add(Client $client)
    {
        $this->clientManager->updateClient($client);

    }

    public function remove(Client $client) {
        $this->em->remove($client);
        $this->em->flush();
    }

    public function find($id) {
        return $client = $this->repo->find($id);
    }

    public function findByCode($code) {
        return $client = $this->codeRepo->findOneByToken($code);
    }

    public function findByPage($page = 1, $limit = 10) {
        $query = $this->em->createQuery('
            select l, m
            from CDEOAuthBundle:Client l
            join l.authCode m
        ');

        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $pagination;
    }

}