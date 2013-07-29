<?php

namespace CDE\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\ContentBundle\Form\Type\TagType;
use Symfony\Component\HttpFoundation\Request;


class TagController extends Controller
{
    protected function getTagManager()
    {
        return $this->get('cde_content.manager.tag');
    }
    
    public function indexAction()
    {
        $tags = $this->getTagManager()->getTreeArray();
        return $this->render('CDEContentBundle:Tag:index.html.twig', array(
            'tags' => $tags,
        ));
    }

    public function viewAction($id)
    {
        $tag = $this->getTagManager()->find($id);
        return $this->render('CDEContentBundle:Tag:view.html.twig', array(
            'tag' => $tag,
        ));
    }
    
    public function createAction(Request $request)
    {
        $tag = $this->getTagManager()->create();
        $form = $this->createForm(new TagType(), $tag);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getTagManager()->add($tag);
                return $this->redirect($this->generateUrl('CDEContentBundle_tag_view', array('id' => $tag->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Tag:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $tag = $this->getTagManager()->find($id);
        $form = $this->createForm(new TagType(), $tag);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bindRequest($request);
            if($form->isValid()) {
                $this->getTagManager()->add($tag);
                return $this->redirect($this->generateUrl('CDEContentBundle_tag_view', array('id' => $tag->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Tag:update.html.twig', array(
            'form' => $form->createView(),
            'tag' => $tag,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $tag = $this->getTagManager()->find($id);
        $form = $this->createFormBuilder($tag)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($tag, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$tag->__toString());
                $this->getTagManager()->remove($tag);
                return $this->redirect($this->generateUrl('CDEContentBundle_tag_index'));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Tag:delete.html.twig', array(
            'form' => $form->createView(),
            'tag' => $tag,
        ));
    }

}
