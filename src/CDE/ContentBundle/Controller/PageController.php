<?php

namespace CDE\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\ContentBundle\Entity\PageCollection;
use CDE\ContentBundle\Form\Type\PageType;
use CDE\ContentBundle\Form\Type\PageUpdateType;
use CDE\ContentBundle\Form\Type\PageSortType;
use CDE\ContentBundle\Form\Type\AddTagsType;
use Symfony\Component\HttpFoundation\Request;


class PageController extends Controller
{
    protected function getPageManager()
    {
        return $this->get('cde_content.manager.page');
    }
    
    public function indexAction()
    {
        $pages = $this->getPageManager()->find();
        return $this->render('CDEContentBundle:Page:index.html.twig', array(
            'pages' => $pages,
        ));
    }

    public function viewAction($id)
    {
        $page = $this->getPageManager()->find($id);
        $parentTag = $this->getPageManager()->findParentTag($page);
        return $this->render('CDEContentBundle:Page:view.html.twig', array(
            'page' => $page,
            'parentTag' => $parentTag,
        ));
    }
    
    public function createAction(Request $request)
    {
        $page = $this->getPageManager()->create();
        $form = $this->createForm(new PageType(), $page);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getPageManager()->add($page);
                return $this->redirect($this->generateUrl('CDEContentBundle_view', array('id' => $page->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Page:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function updateAction(Request $request, $id)
    {
        
        $page = $this->getPageManager()->find($id);
        $parentTag = $this->getPageManager()->findParentTag($page);
        $form = $this->createForm(new PageUpdateType(), $page);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getPageManager()->update($page);
                return $this->redirect($this->generateUrl('CDEContentBundle_view', array('id' => $page->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Page:update.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'parentTag' => $parentTag,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $page = $this->getPageManager()->find($id);
        $form = $this->createFormBuilder($page)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($page, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$page->__toString());
                $this->getPageManager()->remove($page);
                return $this->redirect($this->generateUrl('CDEContentBundle_index'));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Page:delete.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
        ));
    }

    public function tagAction(Request $request, $id)
    {
        $page = $this->getPageManager()->find($id);
        $parentTag = $this->getPageManager()->findParentTag($page);
        $form = $this->createForm(new AddTagsType(), $page, array('parentTag' => array('tag' => $parentTag)));
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getPageManager()->update($page);
                return $this->redirect($this->generateUrl('CDEContentBundle_view', array('id' => $page->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Page:tag.html.twig', array(
            'form' => $form->createView(),
            'page' => $page,
            'parentTag' => $parentTag,
        ));
    }

    public function sortAction(Request $request)
    {
        $pages = $this->getPageManager()->findBySort();
        $pageCollection = new PageCollection();
        $pageCollection->setPages($pages);
        $form = $this->createForm(new PageSortType(), $pageCollection);
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            foreach ($pageCollection->getPages() as $page) {
                if (is_int($page->getSort())) {
                    $this->getPageManager()->update($page);
                }
            }
            $this->get('session')->getFlashBag()->add('notice', "Update sort orders");
            return $this->redirect($this->generateUrl('CDEContentBundle_index'));
        }
        return $this->render('CDEContentBundle:Page:sort.html.twig', array(
            'form' => $form->createView(),
        ));
    }

}
