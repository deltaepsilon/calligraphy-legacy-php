<?php

namespace CDE\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class FrontController extends Controller
{
    protected function getProductManager()
    {
        return $this->get('cde_cart.manager.product');
    }
    protected function getCartManager()
    {
        return $this->get('cde_cart.manager.cart');
    }

    protected function getRedis() {
        return $this->get('snc_redis.default');
    }
    
    public function indexAction(Request $request)
    {
        if (getenv('ISLC_ANGULAR') === 'true') {
            $fragment = $request->get('_escaped_fragment_');
            if (isset($fragment)) {
                $phantomParams = $this->container->getParameter('phantomjs');
                $redis = $this->getRedis();
                $index = $redis->get($fragment);
                if (!isset($index)) {
                    $index = file_get_contents('http://127.0.0.1:8888/?phantomjs=true&_escaped_fragment_='.$fragment);
                    $redis->set($fragment, $index);
                    $redis->expire($fragment, $phantomParams['cache']);
                }

            } else {
                $index = file_get_contents(getenv('ISLC_ANGULAR_ROOT').'/index.html');

            }

            $response = new Response($index, 200, array(
                'content-type' => 'text/html'
            ));
            $response->prepare($request);
            return $response;
        } else {
            return $this->render('CDEUtilityBundle:Front:index.html.twig', array(
                'user' => $this->getUser(),
            ));
        }

    }
    
    public function metaAction($name)
    {
        return $this->render('CDEUtilityBundle:Front:'.$name.'.html.twig', array(
            'user' => $this->getUser(),
        ));
    }
    
    public function purchaseAction($slug)
    {
        $user = $this->getUser();
        $product = $this->getProductManager()->findActiveBySlug($slug);
        $this->getCartManager()->addProduct($product, $user);

        if (getenv('ISLC_ANGULAR') === 'true') {
            return $this->redirect('/#!/cart');
        } else {
            return $this->redirect($this->generateUrl('CDECartBundle_cart_index'));
        }

    }
}
