<?php

/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 8/25/12
 * Time: 11:40 AM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\UserBundle\Controller;

use CDE\UserBundle\Form\Type\RegistrationFormType;
use FOS\UserBundle\Controller\RegistrationController as BaseController;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;


class RegistrationController extends Controller
{
	public function getUserManager()
	{
		return $this->container->get('cde_user.manager.user');
	}

    private function getErrorMessages(Form $form) {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }

    private function translateRequestParams(Request $request) {
        $username =$request->request->get('username');
        $originalForm = $request->request->get('fos_user_registration_form');
        $form = array(
            'username' => $username,
            'email' => $request->request->get('email'),
            'plainPassword' => array(
                'first' => $request->request->get('password'),
                'second' => $request->request->get('verification')
            ),
            '_token' => $originalForm['_token']
        );



        if (isset($username)) {
            $request->request->set('fos_user_registration_form', $form);
        }

        return $request;
    }
        
    public function registerAction(Request $request)
    {
      /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
      $formFactory = $this->container->get('fos_user.registration.form.factory');
      /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
      $userManager = $this->container->get('fos_user.user_manager');
      /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
      $dispatcher = $this->container->get('event_dispatcher');

      $user = $this->getUserManager()->create();
      $user->setEnabled(true);

      $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, new UserEvent($user, $request));

      $form = $formFactory->createForm();
//      $form = $this->createForm(new RegistrationFormType('CDE\UserBundle\Entity\User'));
      $form->setData($user);

       $request = $this->translateRequestParams($request);
      if ('POST' === $request->getMethod()) {
          $form->bind($request);

          if ($form->isValid()) {
              $event = new FormEvent($form, $request);
              $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

              $userManager->updateUser($user);

              if (null === $response = $event->getResponse()) {
//                  $url = $this->container->get('router')->generate('fos_user_registration_confirmed');
                  $url = $this->container->get('router')->generate('CDECartBundle_cart_index');

                  $redirect = $request->request->get('redirect');
                  if (isset($redirect) && preg_match('/http/', $redirect) === 0) {
                      $redirect = 'http://'.$_SERVER['HTTP_HOST'].$redirect;
                      $response = new RedirectResponse($redirect);
                  } else {
                      $response = new RedirectResponse($url);
                  }


              }

              $this->getUserManager()->sendWelcomeEmail($user);

              $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

              return $response;
          }
      }

      $redirect = $request->request->get('origin');
      if (isset($redirect)) {
          $errors = $this->getErrorMessages($form);
          $error = 'Registration failed.';

          foreach ($errors as $errorMessage) {
              $error .= ' '.$errorMessage[0].'.';
          }

          $final = $redirect.'?error='.$error;
          if (preg_match('/http/', $redirect) === 0) {
              $final = 'http://'.$_SERVER['HTTP_HOST'].'/'.$redirect.'?error='.$error;
          }
          return new RedirectResponse($final);
      } else {
          return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.twig', array(
              'form' => $form->createView(),
          ));
      }


	}
}
