<?php
/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 8/10/12
 * Time: 11:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\UserBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Controller\ResettingController as BaseResettingController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResettingController extends BaseResettingController
{

    /**
     * Convenience methods
     */
    private function getRoot() {
        return 'https://'.$_SERVER['HTTP_HOST'];
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = 'FOSUserBundle:Resetting:email.txt.twig';
        $url = $this->getRoot().'/#!/reset/form/'.$user->getConfirmationToken();
//        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->container->get('templating')->render($template, array(
            'user' => $user,
            'confirmationUrl' => $url
        ));
        $this->sendEmailMessage($rendered, $this->container->getParameter('email_email'), $user->getEmail());
    }

    /**
     * @param string $renderedTemplate
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body);

        $this->container->get('mailer')->send($message);
    }

    /**
     * Request reset user password: submit form and send email
     */
    public function customEmailAction(Request $request)
    {

        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return new RedirectResponse($this->getRoot().'/#!/reset?error=Username or email not found.');
        }

//        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
//            return new RedirectResponse($this->getRoot().'/#!/reset?error=The password for this user has already been requested in the last 24 hours.');
//        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set(static::SESSION_EMAIL, $this->getObfuscatedEmail($user));
//        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $this->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        $email = $user->getEmail();
        $emailParts = explode('@', $email);
        $email = '**********'.substr($emailParts[0], 0, -2).'@'.$emailParts[1];

        return new RedirectResponse($this->getRoot()."/#!/?notification=Check your email address ($email) for the reset password");
    }

    /**
     * Reset user password
     */
    public function resetAction(Request $request, $token = null)
    {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.resetting.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_INITIALIZE, $event);

//        if (null !== $event->getResponse()) {
//            return $event->getResponse();
//        }

        //reset form input
        $formParams = $request->request->get('fos_user_resetting_form');
        $csrfToken = '';
        if (isset($formParams)) {
            $csrfToken = $formParams['_token'];
        }
        $inputs = array(
            '_token' => $csrfToken,
            'plainPassword' => array(
                'first' => $request->request->get('password'),
                'second' => $request->request->get('verification'),
            ),
        );

        $request->request->set('fos_user_resetting_form', $inputs);

        $form = $formFactory->createForm();
        $form->setData($user);

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_SUCCESS, $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
//                    $url = $this->container->get('router')->generate('fos_user_profile_show');
                    $url = $this->getRoot().'/#!/account';
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::RESETTING_RESET_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }
        }

        return $this->container->get('templating')->renderResponse('CDEUtilityBundle:Angular:resetting.html.twig', array(
            'form' => $form->createView(),
            'token' => $token,
        ));
//        return new RedirectResponse($this->getRoot().'/#!/reset/form?error=Password reset failed');
    }
}
