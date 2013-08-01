<?php

namespace CDE\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\ContentBundle\Entity\CommentCollection;
use CDE\ContentBundle\Form\Type\CommentType;
use CDE\ContentBundle\Form\Type\CommentCollectionType;
use Symfony\Component\HttpFoundation\Request;


class CommentController extends Controller
{
    protected function getCommentManager()
    {
        return $this->get('cde_content.manager.comment');
    }

    protected function getGalleryManager()
    {
        return $this->get('cde_content.manager.gallery');
    }
    
    public function indexAction(Request $request)
    {
        $comments = $this->getCommentManager()->find();
        $commentCollection = new CommentCollection();
        $commentCollection->setComments($comments);
        $form = $this->createForm(new CommentCollectionType(), $commentCollection);
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            foreach ($commentCollection->getComments() as $comment) {
                if (is_bool($comment->getMarked())) {
                    $this->getCommentManager()->update($comment);
                }
            }
            $this->get('session')->getFlashBag()->add('notice', "Marked comments as read");
        }
        return $this->render('CDEContentBundle:Comment:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    public function createAction(Request $request, $id)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->findAbsolute($id);
        $comment = $this->getCommentManager()->create();
        $comment->setGallery($gallery);
        $comment->setUser($user);
        $comment->setGalleryuser($gallery->getUser());
        $form = $this->createForm(new CommentType(), $comment);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getCommentManager()->add($comment);
                //Send comment notification email if the user leaving the comment is not the user who owns the gallery
                $galleryUser = $gallery->getUser();
                if ($user->getId() != $galleryUser->getId() && $galleryUser->getCommentEmail()) {
                    $admin = $this->container->getParameter('admin');
                    $message = \Swift_Message::newInstance()
                        ->setSubject($this->container->getParameter('site_name').': New Gallery Comment')
                        ->setFrom($admin['no_reply_email'])
                        ->setTo($comment->getGalleryuser()->getEmail())
                        ->setBody($this->renderView('CDEContentBundle:Mail:newcomment.txt.twig', array(
                        'comment' => $comment
                    )));
                    $this->get('mailer')->send($message);
                }
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_view', array('id' => $gallery->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Comment:create.html.twig', array(
            'gallery' => $gallery,
            'form' => $form->createView()
        ));
    }
    public function updateAction(Request $request, $id)
    {
        $user = $this->getUser();
        $comment = $this->getCommentManager()->find($id);
        $gallery = $comment->getGallery();
        $form = $this->createForm(new CommentType(), $comment);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getCommentManager()->update($comment);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_view', array('id' => $gallery->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Comment:update.html.twig', array(
            'form' => $form->createView(),
            'gallery' => $gallery,
            'comment' => $comment,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $user = $this->getUser();
        $comment = $this->getCommentManager()->find($id);
        $form = $this->createFormBuilder($comment)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($comment, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$comment->__toString());
                $this->getCommentManager()->remove($comment);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_view', array('id' => $comment->getGallery()->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Comment:delete.html.twig', array(
            'form' => $form->createView(),
            'comment' => $comment,
        ));
        
    }

}
