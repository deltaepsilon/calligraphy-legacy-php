<?php

namespace CDE\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OAuthController extends Controller
{
    protected  function getClientManager() {
        return $this->get('cde_oauth.manager.client');
    }
    public function indexAction($page = 1)
    {
        if (isset($_GET['code'])) {
            return $this->redirect($this->generateUrl('CDEOAuthBundle_oauth_token', array(
                'code' => $_GET['code'],
            )));
        }

        $clients = $this->getClientManager()->findByPage($page, 50);
        return $this->render('CDEOAuthBundle:Clients:index.html.twig', array(
            'clients' => $clients
        ));
    }

    public function createAction()
    {
        $redirectURI = $this->generateUrl('CDEOAuthBundle_oauth_client');

        $client = $this->getClientManager()->create();
        $client->setRedirectUris(array($redirectURI));
        $client->setAllowedGrantTypes(array('token', 'authorization_code'));
        $this->getClientManager()->add($client);

        return $this->redirect($this->generateUrl('fos_oauth_server_authorize', array(
            'client_id'     => $client->getPublicId(),
            'redirect_uri'  => $redirectURI,
            'response_type' => 'code'
        )));
    }

    public function deleteAction($id)
    {
        $client = $this->getClientManager()->find($id);
        $this->getClientManager()->remove($client);
        $this->get('session')->getFlashBag()->add('notice', "Deleted ".$id);
        return $this->redirect($this->generateUrl('CDEOAuthBundle_oauth_client'));
    }

    public function tokenAction() {
        $code = $_GET['code'];
        $authCode = $this->getClientManager()->findByCode($code);
        $client = $authCode->getClient();
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        } else {
            $proto = 'http';
        }
        $json = file_get_contents($proto.'://'.$_SERVER['HTTP_HOST'].$this->generateUrl('fos_oauth_server_token', array(
            'client_id' => $client->getId().'_'.$client->getRandomId(),
            'client_secret' => $client->getSecret(),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->generateUrl('CDEOAuthBundle_oauth_client'),
        )));
        $token = json_decode($json);
        return $this->redirect($this->generateUrl('CDEOAuthBundle_oauth_client', (array) $token));
    }
}
