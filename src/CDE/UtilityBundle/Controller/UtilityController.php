<?php

namespace CDE\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer;

class UtilityController extends Controller
{
    protected function getGalleryManager()
    {
        return $this->get('cde_content.manager.gallery');
    }
    
    protected function getSerializer()
    {
        return $this->get('serializer');
    }
    
    public function indexAction()
    {
        return $this->render('CDEUtilityBundle:Utility:index.html.twig', array(
            
        ));
    }
    
    public function secureDistributionAction()
    {
        $s3 = $this->get('aws_s3');
        $response = $this->get('cde_utility.manager.aws')->secureDistribution();
        return $this->render('CDEUtilityBundle:Utility:secure.html.twig', array(
            'response' => $response,
        ));
    }
    
    public function signedUriAction(Request $request)
    {
        $signedUri = NULL;
        $form = $this->createFormBuilder()->add('url')->getForm();
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $data = $form->getData();
                $uri = $data['url'];
                $signedUri = $this->get('cde_utility.manager.aws')->getSignedUri($uri);
            }
        }
        // Render form
        return $this->render('CDEUtilityBundle:Utility:signed.uri.html.twig', array(
            'form' => $form->createView(),
            'signedUri' => $signedUri,
        ));
    }
    
    public function distroInfoAction()
    {
        $response = $this->get('cde_utility.manager.aws')->getDistroInfo();
        return $this->render('CDEUtilityBundle:Utility:distro.info.html.twig', array(
            'response' => $response,
        ));
    }
    
    public function galleryAction($name) {
        $manifest = $this->get('cde_utility.manager.aws')->getGalleryManifest($name);
        return new Response(json_encode($manifest));
    }
    
    public function jsonSignedUriAction(Request $request) 
    {
        $uri = $request->get('uri');
        $signedUri = $this->get('cde_utility.manager.aws')->getSignedUriByFilename($uri);
        return new Response(json_encode(array('uri' => $signedUri)));
    }

    public function galleryDataAction()
    {
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException();
        }
        $galleries = $this->getGalleryManager()->findByUser($user);
//        These serialization methods fail, because they try to serialize the user.  This needs to be wicked fast and not include excess data.
//        $serializer = new \Symfony\Component\Serializer\Serializer(array((new \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer())), array('json' => new \Symfony\Component\Serializer\Encoder\JsonEncoder()));
//        $serialized = $this->getSerializer()->serialize($galleries, 'json');
//        $serialized = $serializer->serialize($galleries, 'json');
        $serialized = array();
        foreach ($galleries as $gallery) {
            $newGallery = array(
                'id' => $gallery->getId(),
                'title' => $gallery->getTitle(),
                'description' => $gallery->getDescription(),
                'filename' => $gallery->getFilename(),
                'comments' => array()

            );
            foreach($gallery->getComments() as $comment) {
                $newGallery['comments'][] = array(
                    'comment' => $comment->getComment(),
                    'created' => $comment->getCreated(),
                    'user' => array(
                        'username' => $comment->getUser()->getUsername()
                    )
                );
            }
            $serialized[] = $newGallery;
        }
        return new Response(json_encode($serialized));
    }
}
