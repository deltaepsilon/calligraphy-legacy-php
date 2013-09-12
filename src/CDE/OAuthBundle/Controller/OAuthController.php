<?php

namespace CDE\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OAuthController extends Controller
{
    private function getCode() {
        if (isset($_GET['response_type']) && $_GET['response_type'] === 'code') {
            $inputs = explode('_', $_GET['client_id']);
            $clientID = $inputs[0];
            $clientRandomId = $inputs[1];
            $client = $this->getClientManager()->find($clientID);
            foreach ($client->getAuthCode() as $authCode) {
                $code = $authCode->getToken();
            }
            if (isset($code) && $clientRandomId === $client->getRandomId()) {
                return $code;
            }
        }
        if (isset($_GET['code'])) {
            return $_GET['code'];
        }
        return false;
    }

    protected  function getClientManager() {
        return $this->get('cde_oauth.manager.client');
    }
    public function clientsAction($page = 1)
    {
        $code = $this->getCode();
        if (isset($code) && $code !== false) {
            return $this->redirect($this->generateUrl('CDEOAuthBundle_client_token', array(
                'code' => $code,
            )));
        }

        $clients = $this->getClientManager()->findByPage($page, 50);
        return $this->render('CDEOAuthBundle:Clients:index.html.twig', array(
            'clients' => $clients
        ));
    }

    public function clientCreateAction()
    {
        $redirectURI = $this->generateUrl('CDEOAuthBundle_client_index');

        $client = $this->getClientManager()->create();
        $client->setRedirectUris(array($redirectURI));
        $client->setAllowedGrantTypes(array('token', 'authorization_code', 'refresh_token'));
        $this->getClientManager()->add($client);

        return $this->redirect($this->generateUrl('fos_oauth_server_authorize', array(
            'client_id'     => $client->getPublicId(),
            'redirect_uri'  => $redirectURI,
            'response_type' => 'code'
        )));
    }

    public function clientDeleteAction($id)
    {
        $client = $this->getClientManager()->find($id);
        $this->getClientManager()->remove($client);
        $this->get('session')->getFlashBag()->add('notice', "Deleted ".$id);
        return $this->redirect($this->generateUrl('CDEOAuthBundle_client_index'));
    }

    public function tokenAction() {
        $code = $this->getCode();

        $authCode = $this->getClientManager()->findByAuthCode($code);
        $client = $authCode->getClient();
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        } else {
            $proto = 'http';
        }
        $url = $this->generateUrl('fos_oauth_server_token', array(
            'client_id' => $client->getId().'_'.$client->getRandomId(),
            'client_secret' => $client->getSecret(),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->generateUrl('CDEOAuthBundle_client_index'),
        ));
        if (isset($_SERVER['HTTP_HOST'])) {
            $json = file_get_contents($proto.'://'.$_SERVER['HTTP_HOST'].$url);
            $token = json_decode($json);
            return $this->redirect($this->generateUrl('CDEOAuthBundle_client_index', (array) $token));
        }
        return $this->redirect($url);

    }

    public function refreshAction($token) {
        $refreshToken = $this->getClientManager()->findByRefreshToken($token);
        $client = $refreshToken->getClient();
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'];
        } else {
            $proto = 'http';
        }
        $accessTokens = $client->getAccessToken();
        foreach ($accessTokens as $accessToken) {
            $this->getClientManager()->removeAccessToken($accessToken);
        }

        $json = file_get_contents($proto.'://'.$_SERVER['HTTP_HOST'].$this->generateUrl('fos_oauth_server_token', array(
            'client_id' => $client->getId().'_'.$client->getRandomId(),
            'client_secret' => $client->getSecret(),
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
            'redirect_uri' => $this->generateUrl('CDEOAuthBundle_client_index'),
        )));
        $token = json_decode($json);
        $this->get('session')->getFlashBag()->add('notice', "Refreshed ".$client->getId());
        return $this->redirect($this->generateUrl('CDEOAuthBundle_client_index', (array) $token));
    }

    public function forwardAction() {
        $url = parse_url($_GET['redirect']);
        $redirect = $url['scheme'].'://'.$url['host'];
        $token = $this->getClientManager()->getToken($this->getUser());
        return $this->redirect($redirect.'?access_token='.$token->getToken());
    }

}
