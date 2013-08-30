<?php

namespace CDE\UserBundle\Entity;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CDE\UserBundle\Model\UserManagerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserManager extends ContainerAware implements UserManagerInterface
{
    protected $em;
    protected $repo;
    protected $class;
    protected $userProvider;
    protected $userManager;
    protected $subscriptionManager;

    public function __construct(EntityManager $em, $class, \FOS\UserBundle\Model\UserManagerInterface $userManager, UserProviderInterface $userProvider, $subscriptionManager, $affiliateManager, $paginator)
    {
        $this->em = $em;
        $this->repo = $this->em->getRepository($class);
        $this->class = $class;
        $this->userManager = $userManager;
        $this->userProvider = $userProvider;
        $this->subscriptionManager = $subscriptionManager;
        $this->affiliateManager = $affiliateManager;
        $this->paginator = $paginator;
    }
    public function create()
    {
        $user = $this->userManager->createUser();
        $this->setAffiliate($user);
        return $user;

    }
    public function add($user)
    {
        $this->userManager->updateUser($user);
    }
    public function update($user)
    {
        $this->userManager->updateUser($user);
    }
    public function remove($user)
    {
        // $subscriptions = $user->getSubscriptions();
        // foreach ($subscriptions as $subscription) {
            // $this->subscriptionManager->remove($subscription);
        // }
        $this->em->remove($user);
        $this->em->flush();
    }

    public function find($id = NULL)
    {
        if ($id) {
            $user = $this->repo->find($id);
        } else {
            $user = $this->repo->findBy(
                array(),
                array('username' => 'ASC')
            );
        }
        if (NULL === $user) {
            throw new NotFoundHttpException();
        }
        return $user;
    }

    public function findByPage($page = 1, $limit = 10)
    {
        $query = $this->em->createQuery('
            select l
            from CDEUserBundle:User l
        ');

        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $limit
        );

        return $pagination;
    }

    public function setDefaultExpires($user)
    {
        if ($user->getExpiresAt() === NULL) {
            $user->setExpiresAt(new \DateTime('+5 year'));
        }
        if ($user->getCredentialsExpireAt() === NULL) {
            $user->setCredentialsExpireAt(new \DateTime('+5 year'));
        }
    }
    public function getFirstTag($user) {
        $query = $this->em->createQuery('
                select l, m, n
                from CDESubscriptionBundle:Subscription l
                join l.user m
                join l.product n
                join n.tags o
                where m.id = :id
                order by l.created desc
            ')->setParameter('id', $user->getId());
        $subscription = $query->getResult();
        if (empty($subscription)) {
            return;
        }
        $tags = $subscription[0]->getProduct()->getTags();
//        var_dump($tags[0]); exit;
        return $tags[0];

    }
	public function setAffiliate($user) {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $affiliate = $this->affiliateManager->findOneByIp($ip);
        if(isset($affiliate)) {
            $user->setAffiliate($affiliate);
        }
		return $user;
	}

    public function updatePassword(UserInterface $user) {
        return $this->userManager->updatePassword($user);
    }

    public function loadByUsername($username) {
        return $this->userProvider->loadUserByUsername($username);
    }
}
