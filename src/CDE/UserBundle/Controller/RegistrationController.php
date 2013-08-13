<?php

/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 8/25/12
 * Time: 11:40 AM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\UserBundle\Controller;

use FOS\UserBundle\Controller\RegistrationController as BaseController;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;


class RegistrationController extends BaseController
{
	public function getUserManager()
	{
		return $this->container->get('cde_user.manager.user');
	}
        
         public function registerAction(Request $request)
        {
          /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
          $formFactory = $this->container->get('fos_user.registration.form.factory');
          /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
          $userManager = $this->container->get('fos_user.user_manager');
          /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
          $dispatcher = $this->container->get('event_dispatcher');

          $user = $userManager->createUser();
          $user->setEnabled(true);

          $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, new UserEvent($user, $request));

          $form = $formFactory->createForm();
          $form->setData($user);

          if ('POST' === $request->getMethod()) {
              $form->bind($request);

              if ($form->isValid()) {
                  $event = new FormEvent($form, $request);
                  $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                  $this->getUserManager()->setIp($user);
                  $userManager->updateUser($user);

                  if (null === $response = $event->getResponse()) {
                      $url = $this->container->get('router')->generate('fos_user_registration_confirmed');
                      $response = new RedirectResponse($url);
                  }

                  $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                  return $response;
              }
          }

          return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
              'form' => $form->createView(),
          ));
	}
}
