<?php


namespace CDE\UserBundle\Handler;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface {

    public function onAuthenticationSuccess(Request $request, TokenInterface $token) {
        $redirect = $request->request->get('redirect');
        if (!isset($redirect)) {
            $redirect = '/';
        }
        if (preg_match('/http/', $redirect) === 0) {
            $redirect = $_SERVER['HTTP_ORIGIN'].$redirect;
        }
        return new RedirectResponse($redirect);
    }

}