<?php
/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 10/9/13
 * Time: 1:54 PM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\UserBundle\Handler;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface {
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $redirect = $request->request->get('origin');
        if (!isset($redirect)) {
            $redirect = 'login';
        }
        $final = $redirect;
        if (preg_match('/http/', $redirect) === 0) {
            $final = 'http://'.$_SERVER['HTTP_HOST'].'/'.$redirect.'?error='.$exception->getMessage();
        }

        return new RedirectResponse($final);
    }
}