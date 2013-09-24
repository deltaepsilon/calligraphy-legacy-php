<?php

namespace CDE\ContentBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

class RestController extends FOSRestController
{
    /**
     * Private Methods
     */
    private function setCommentParameters(Request $request, $comment)
    {
        $text = $request->request->get('comment');
        $marked = $request->request->get('marked');
        if (!isset($text)) {
            $text = $request->query->get('comment');
        }
        if (!isset($marked)) {
            $marked = $request->query->get('marked');
        }
        if (isset($text)) {
            $comment->setComment($text);
        }

        if (isset($marked)) {
            if (strtolower($marked) === 'true') {
                $marked = true;
            } else if (strtolower($marked) === 'false') {
                $marked = false;
            }
            $comment->setMarked($marked);
        }
        $this->getCommentManager()->update($comment);
        return $comment;
    }

    private function setGalleryParameters(Request $request, $gallery)
    {
        $marked = $request->query->get('marked');
        if (!isset($marked)) {
            $marked = $request->request->get('marked');
        }

        if (isset($marked)) {
            if (strtolower($marked) === 'true') {
                $marked = true;
            } else if (strtolower($marked) === 'false') {
                $marked = false;
            }
            $gallery->setMarked($marked);
        }
        $this->getGalleryManager()->update($gallery);
        return $gallery;
    }

    /**
     * Managers
     */
    protected function getCommentManager()
    {
        return $this->get('cde_content.manager.comment');
    }

    protected function getGalleryManager()
    {
        return $this->get('cde_content.manager.gallery');
    }

    /**
     * Comments
     */
    public function createCommentAction($id, Request $request)
    {
        $user = $this->getUser();
        $gallery = $this->getGalleryManager()->findAbsolute($id);

        if (!isset($user)) {
            $view = $this->view(array('error' => 'User not found. Action not permitted.'), 401)->setFormat('json');
        } else if (!isset($gallery)) {
            $view = $this->view(array('id' => $id, 'error' => 'Gallery not found'), 404)->setFormat('json');
        } else {
            $comment = $this->getCommentManager()->create();
            $comment->setGallery($gallery);
            $comment->setUser($user);
            $comment->setGalleryuser($gallery->getUser());
            $this->setCommentParameters($request, $comment);
            $galleryUser = $gallery->getUser();
            if (( $user->getId() != $galleryUser->getId() ) && $galleryUser->getCommentEmail()) {
                $admin = $this->container->getParameter('admin');
                $message = \Swift_Message::newInstance()
                    ->setSubject($this->container->getParameter('site_name').': New Gallery Comment')
                    ->setFrom($admin['no_reply_email'])
                    ->setTo($comment->getGalleryuser()->getEmail())
                    ->setBody($this->renderView('CDEContentBundle:Mail:newcomment.txt.twig', array(
                        'comment' => $comment
                    )))
                    ->setContentType("text/html");
                $this->get('mailer')->send($message);
            }



            $view = $this->view($comment, 200)->setFormat('json');
        }

        return $this->handleView($view);

    }

    public function getCommentAction($id)
    {
        $comment = $this->getCommentManager()->find($id);
        $view = $this->view($comment, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function updateCommentAction($id, Request $request)
    {
        $comment = $this->getCommentManager()->find($id);

        $this->setCommentParameters($request, $comment);
        $view = $this->view($comment, 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function deleteCommentAction($id)
    {
        $comment = $this->getCommentManager()->find($id);
        if (isset($comment)) {
            $this->getCommentManager()->remove($comment);
            $view = $this->view(array('id' => $id), 200)->setFormat('json');
        } else {
            $view = $this->view(array('id' => $id, 'error' => 'Entity not found'), 404)->setFormat('json');
        }


        return $this->handleView($view);
    }

    public function getCommentsAction($page = 1, $limit = 10)
    {
        $comments = $this->getCommentManager()->findByPage($page, $limit);
        $view = $this->view($comments->getItems(), 200)->setFormat('json');
        return $this->handleView($view);
    }

    /**
     * Galleries
     */
    public function getGalleriesAction($page = 1, $limit = 10, Request $request)
    {
//        $all = $request->all();
        $params = $request->query->all();
        $queryFilter = array();
        foreach ($params as $k => $v) {
            $filter = explode(':', $k);
            if (count($filter) === 2) {
                $queryFilter[$filter[1]] = $v;
            }
        }

        $pagination = $this->getGalleryManager()->findByPage($page, $limit, $queryFilter);
        $view = $this->view($pagination->getItems(), 200)->setFormat('json');
        return $this->handleView($view);
    }

    public function getGalleryAction($id)
    {
        $gallery = $this->getGalleryManager()->find($this->getUser(), $id);
        if (!isset($gallery)) {
            $view = $this->view(array('id' => $id, 'error' => 'Gallery not found'), 404)->setFormat('json');
        } else {
            $view = $this->view($gallery, 200)->setFormat('json');
        }
        return $this->handleView($view);
    }

    public function updateGalleryAction($id, Request $request)
    {
        $gallery = $this->getGalleryManager()->find($this->getUser(), $id);

        $this->setGalleryParameters($request, $gallery);
        $view = $this->view($gallery, 200)->setFormat('json');
        return $this->handleView($view);
    }

}
