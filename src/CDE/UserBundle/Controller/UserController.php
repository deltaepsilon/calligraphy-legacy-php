<?php

namespace CDE\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CDE\UserBundle\Form\Type\UserType;
use CDE\UserBundle\Form\Type\UserUpdateType;
use CDE\UserBundle\Form\Type\UserUpdateAccountType;
use CDE\UserBundle\Form\Model\UpdateUser;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use \Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function getUserManager()
    {
        return $this->get('cde_user.manager.user');
    }

    public function getAddressManager()
    {
        return $this->get('cde_user.manager.address');
    }
    
    public function getTransactionManager()
    {
        return $this->get('cde_cart.manager.transaction');
    }

    protected function getTagManager()
    {
        return $this->get('cde_content.manager.tag');
    }
    
    public function indexAction($page = 1)
    {
        $users = $this->getUserManager()->findByPage($page, 10);
        return $this->render('CDEUserBundle:User:index.html.twig', array(
            'users' => $users,
        ));
    }
    public function viewAction($id)
    {
        $user = $this->getUserManager()->find($id);
        return $this->render('CDEUserBundle:User:view.html.twig', array(
            'user' => $user,
        ));
    }
    
    public function viewAccountAction()
    {
        $user = $this->getUser();
        $transactions = $user->getTransactions();
        $address = $user->getAddress();
        return $this->render('CDEUserBundle:User:view.account.html.twig', array(
            'user' => $user,
            'transactions' => $transactions,
            'address' => $address,
        ));
    }
    
    public function createAction(Request $request)
    {
        $user = $this->getUserManager()->create();
        $this->getUserManager()->setDefaultExpires($user);
        $form = $this->createForm(new UserType(), $user);
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $this->getUserManager()->add($user);
                return $this->redirect($this->generateUrl('CDEUserBundle_view', array('id' => $user->getId())));
            }
        }
        // Render form
        return $this->render('CDEUserBundle:User:create.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    public function updateAccountAction(Request $request)
    {
        $user = $this->getUser();
        $this->getUserManager()->setDefaultExpires($user);
        $form = $this->createForm(new UserUpdateAccountType(), new UpdateUser($user));
        // Process form
        if ($request->getMethod() === 'POST') {
            $form->bind($request);
            if($form->isValid()) {
                $validator = $this->get('validator');
                $data = $form->getData();
                $user->setEmail($data->email);
                $user->setCommentEmail($data->commentEmail);
                
                // Validate user password
                $violations = $validator->validateValue($data->current, new UserPassword());
                foreach ($violations as $violation) {
                    $template = $violation->getMessageTemplate();
                    $parameters = $violation->getMessageParameters();
                    $error = new FormError($template, $parameters);
                    $form->addError($error);
                }
                
                if (count($violations) === 0) {
                    $user->setPlainPassword($data->new);
                    $this->getUserManager()->update($user);
                    return $this->redirect($this->generateUrl('CDEUserBundle_account_view'));
                }
            }
            
        }
        // Render form
        return $this->render('CDEUserBundle:User:update.account.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }
    
    public function updateAction(Request $request, $id)
    {
        $user = $this->getUserManager()->find($id);
        $this->getUserManager()->setDefaultExpires($user);
        $form = $this->createForm(new UserUpdateType(), $user);
        // Process form
        if ($request->getMethod() === 'POST') {
            $data = $request->request->get('cde_user');
            $form->bind($request);
            
            // Form is not respecting roles... this is a little hack to add them back in
            $roles = $data['roles'];
            $rolesMap = array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN');
            foreach ($rolesMap as $role) {
              $user->removeRole($role);
            }
            if ($roles) {
                foreach ($roles as $value) {
                  $user->addRole($rolesMap[intval($value)]);
                }
            }
            
            // Save form as usual
            if($form->isValid()) {
                $this->getUserManager()->update($user);
                return $this->redirect($this->generateUrl('CDEUserBundle_view', array('id' => $id)));
            }
            
        }
        // Render form
        return $this->render('CDEUserBundle:User:update.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }
    public function deleteAction(Request $request, $id)
    {
        $user = $this->getUserManager()->find($id);
        $form = $this->createFormBuilder($user)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            $this->get('session')->getFlashBag()->add('notice', "Deleted ".$user->__toString());
            $this->getUserManager()->remove($user);
            return $this->redirect($this->generateUrl('CDEUserBundle_index'));
        }
        // Render form
        return $this->render('CDEUserBundle:User:delete.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }

    public function deleteAccountAction(Request $request)
    {
        $user = $this->getUser();
        $form = $this->createFormBuilder($user)->add('id', 'hidden')->getForm();
        // Process form
        if($request->getMethod() === 'POST') {
            $this->get('session')->getFlashBag()->add('notice', "Deleted ".$user->__toString());
            $user->setEnabled(FALSE);
            $this->getUserManager()->update($user);
            return $this->redirect($this->generateUrl('fos_user_security_logout'));
        }
        // Render form
        return $this->render('CDEUserBundle:User:delete.account.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
        ));
    }
    
    public function transactionAccountAction($id)
    {
        $user = $this->getUser();
        $transaction = $this->getTransactionManager()->findByUser($user, $id);
        return $this->render('CDECartBundle:Transaction:view.html.twig', array(
            'transaction' => $transaction,
            'user' => $user,
        ));
    }
    
    public function loginPartialAction()
    {
        /**
         * This function is nearly identical to FOSUserBundle:Security:login;
         * however, it returns partial.login.html.twig, which does not extend base.html.twig.
         * This enables the login form to be embedded in another template that extends
         * base.html.twig, without creating an infinite loop
         */
        
        $request = $this->container->get('request');
        /* @var $request \Symfony\Component\HttpFoundation\Request */
        $session = $request->getSession();
        /* @var $session \Symfony\Component\HttpFoundation\Session */

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);

        $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');

        if (getenv('ISLC_ANGULAR') === 'true') {
            $template = 'CDEUtilityBundle:Angular:login.html.twig';
        } else {
            $template = 'FOSUserBundle:Security:partial.login.html.'.$this->container->getParameter('fos_user.template.engine');
        }

        return $this->container->get('templating')->renderResponse($template, array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'csrf_token' => $csrfToken,
        ));
    }

    public function tocPartialAction()
    {
        $toc = null;
        $user = $this->getUser();
        $tag = $this->getUserManager()->getFirstTag($user);
        if ($tag) {
            $toc = $this->getTagManager()->getToc($tag);
        } else {
            return new Response('');
        }

        return $this->render('CDEUtilityBundle:Class:partial.toc.html.twig', array(
            'toc' => $toc,
            'tag' => $tag
        ));
    }

}
