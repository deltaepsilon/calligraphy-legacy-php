<?php

namespace CDE\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CDE\ContentBundle\Form\Type\GalleryType;
use CDE\ContentBundle\Form\Type\GalleryUpdateType;
use CDE\ContentBundle\Form\Type\GalleryAdminType;
use CDE\ContentBundle\Form\Type\CommentType;
use Symfony\Component\HttpFoundation\Request;
use CDE\ContentBundle\Entity\Comment;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class GalleryController extends Controller
{
    protected function getGalleryManager()
    {
        return $this->get('cde_content.manager.gallery');
    }

    protected function getCommentManager()
    {
        return $this->get('cde_content.manager.comment');
    }    
    
    protected function getAwsManager()
    {
        return $this->get('cde_utility.manager.aws');
    }
    
    protected function getSerializer()
    {
        return $this->get('serializer');
    }
    
    public function indexAction($page = 1)
    {
        $user = $this->getUser();
        $galleries = $this->getGalleryManager()->findByPage($page, 25);
        return $this->render('CDEContentBundle:Gallery:index.html.twig', array(
            'galleries' => $galleries,
        ));
    }

    public function indexAccountAction(Request $request, $id = NULL)
    {
        if ((int)$id === 999999999) {
            throw new NotFoundHttpException();
        }
        $user = $this->getUser();
        if ($id) {
            $gallery = $this->getGalleryManager()->find($user, $id);
        } else {
            $galleries = $this->getGalleryManager()->findByUser($user);
            if (count($galleries) === 0) {
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_account_create'));
            }
            $gallery = $galleries[0];
        }
        $signedUri = $this->getAwsManager()->getSignedUriByFilename($gallery->getFilename());
        $gallery->setSignedUri($signedUri);
        
        $comment = $this->getCommentManager()->create();
        $comment->setGallery($gallery);
        $comment->setUser($user);
        $comment->setGalleryuser($gallery->getUser());
        $form = $this->createForm(new CommentType, $comment);
        
        if ($request->getMethod() === 'POST' && $id) {
            $form->bind($request);
            if($form->isValid()) {
                $this->getCommentManager()->add($comment);
                $gallery = $this->getGalleryManager()->find($user, $gallery->getId());
            }
        }
        return $this->render('CDEContentBundle:Gallery:index.account.html.twig', array(
            'gallery' => $gallery,
            'form' => $form->createView(),
        ));
    }

    public function viewAction($id)
    {
        $user = $this->getUser();
		$gallery = $this->getGalleryManager()->findAbsolute($id);
		$comment = $this->getCommentManager()->create();
		$comment->setGallery($gallery);
		$comment->setUser($user);
		$comment->setGalleryuser($gallery->getUser());
		$form = $this->createForm(new CommentType(), $comment);



        $gallery = $this->getGalleryManager()->find($user, $id);
        $signedUri = $this->getAwsManager()->getSignedUriByFilename($gallery->getFilename());
        $gallery->setSignedUri($signedUri);
        return $this->render('CDEContentBundle:Gallery:view.html.twig', array(
            'gallery' => $gallery,
			'form' => $form->createView()
        ));
    }

    public function viewAccountAction($id)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->find($user, $id);
        $signedUri = $this->getAwsManager()->getSignedUriByFilename($gallery->getFilename());
        $gallery->setSignedUri($signedUri);
        return $this->render('CDEContentBundle:Gallery:view.account.html.twig', array(
            'gallery' => $gallery,
        ));
    }
    
    public function createAction(Request $request)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->create();
        $gallery->setUser($user);
        $form = $this->createForm(new GalleryAdminType(), $gallery);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $file = $gallery->getFilename();
                $filename = $user->getUsername().'-'.uniqid().'.'.$file->guessExtension();
                $aws_folder = $this->container->getParameter('aws_gallery_folder');
                $file->move('../web/gallery', $filename);
                $destination = $aws_folder.'/'.$filename;
                $this->getAwsManager()->copyGalleryFile($destination);
                $gallery->setFilename($destination);
                $this->getGalleryManager()->add($gallery);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_view', array('id' => $gallery->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Gallery:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function createAccountAction(Request $request)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->create();
        $gallery->setUser($user);
        $form = $this->createForm(new GalleryType(), $gallery);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $file = $gallery->getFilename();
                $filename = $user->getUsername().'-'.uniqid().'.'.$file->guessExtension();
                $aws_folder = $this->container->getParameter('aws_gallery_folder');
                $file->move('../web/gallery', $filename);
                $destination = $aws_folder.'/'.$filename;
                $this->getAwsManager()->copyGalleryFile($destination);
                $gallery->setFilename($destination);
                $this->getGalleryManager()->add($gallery);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_account_index', array('id' => $gallery->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Gallery:create.account.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function updateAction(Request $request, $id)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->find($user, $id);
        $signedUri = $this->getAwsManager()->getSignedUriByFilename($gallery->getFilename());
        $gallery->setSignedUri($signedUri);
        $form = $this->createForm(new GalleryUpdateType(), $gallery);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getGalleryManager()->update($gallery);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_view', array('id' => $gallery->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Gallery:update.html.twig', array(
            'form' => $form->createView(),
            'gallery' => $gallery,
        ));
    }
    
    public function updateAccountAction(Request $request, $id)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->find($user, $id);
        $signedUri = $this->getAwsManager()->getSignedUriByFilename($gallery->getFilename());
        $gallery->setSignedUri($signedUri);
        $form = $this->createForm(new GalleryUpdateType(), $gallery);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getGalleryManager()->update($gallery);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_account_index', array('id' => $gallery->getId())));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Gallery:update.account.html.twig', array(
            'form' => $form->createView(),
            'gallery' => $gallery,
        ));
    }
    
    public function deleteAction(Request $request, $id)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->find($user, $id);
        $gallery = $gallery;
        $form = $this->createFormBuilder($gallery)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($gallery, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$gallery->__toString());
                $this->getGalleryManager()->remove($gallery);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_index'));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Gallery:delete.html.twig', array(
            'form' => $form->createView(),
            'gallery' => $gallery,
        ));
    }

    public function deleteAccountAction(Request $request, $id)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->find($user, $id);
        $gallery = $gallery;
        $form = $this->createFormBuilder($gallery)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            if ($this->get('validator')->validate($gallery, array('csrf_only'))) {
                $this->get('session')->getFlashBag()->add('notice', "Deleted ".$gallery->__toString());
                $this->getGalleryManager()->remove($gallery);
                return $this->redirect($this->generateUrl('CDEContentBundle_gallery_account_index'));
            }
        }
        // Render form
        return $this->render('CDEContentBundle:Gallery:delete.account.html.twig', array(
            'form' => $form->createView(),
            'gallery' => $gallery,
        ));
    }

}
