<?php

namespace CDE\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\ContentBundle\Entity\Page;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ClassController extends Controller
{
    protected function getPageManager()
    {
        return $this->get('cde_content.manager.page');
    }
    protected function getTagManager()
    {
        return $this->get('cde_content.manager.tag');
    }
    protected function getSubscriptionManager()
    {
        return $this->get('cde_subscription.manager.subscription');
    }
    protected function getAwsManager()
    {
        return $this->get('cde_utility.manager.aws');
    }

    public function indexAction()
    {
        $user = $this->getUser();
        $tags = $this->getTagManager()->findByUser($user);
        $subscriptions = $this->getSubscriptionManager()->findByUser($user);
        return $this->render('CDEUtilityBundle:Class:index.html.twig', array(
            'tags' => $tags, 
            'subscriptions' => $subscriptions
        ));
    }
    
    public function viewAction($tag, $slug)
    {
        $user = $this->getUser();
        $pages = $this->getPageManager()->findBySlug($slug);
        $page = $pages[0];
        $validation = $this->getPageManager()->validatePage($page, $user);
        
        $tag = $this->getTagManager()->findBySlug($tag);
        $toc = $this->getTagManager()->getToc($tag[0]);
        foreach ($toc as $key => $tocPage) {
            if ($page->getId() === $tocPage->getId()) {
                $currentKey = $key;
            }
        }
        $previous = NULL;
        if (!empty($toc[$currentKey - 1])) {
            $previous = $toc[$currentKey - 1];
        }
        $next = NULL;
        if (!empty($toc[$currentKey + 1])) {
            $next = $toc[$currentKey + 1];
        }
        $this->getAwsManager()->signPageUrls($page);
        if (!$validation && !$user->hasRole('ROLE_ADMIN') && !$user->hasRole('ROLE_SUPER_ADMIN')) {
            throw new AccessDeniedHttpException();
        }
        $parentTag = $this->getPageManager()->findParentTag($page);
        return $this->render('CDEUtilityBundle:Class:view.html.twig', array(
            'page' => $page,
            'previous' => $previous,
            'next' => $next,
            'parentTag' => $parentTag,
            'tag' => $tag[0],
            'toc' => $toc,
        ));
    }
    
    public function tocAction($slug)
    {
        $tag = $this->getTagManager()->findBySlug($slug);
        $toc = $this->getTagManager()->getToc($tag[0]);
        return $this->render('CDEUtilityBundle:Class:toc.html.twig', array(
            'toc' => $toc,
            'tag' => $tag[0],
        ));
    }
}
