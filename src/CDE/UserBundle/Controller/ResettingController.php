<?php
/**
 * Created by JetBrains PhpStorm.
 * User: christopheresplin
 * Date: 8/10/12
 * Time: 11:56 PM
 * To change this template use File | Settings | File Templates.
 */

namespace CDE\UserBundle\Controller;

use FOS\UserBundle\Model\UserInterface;

class ResettingController extends \FOS\UserBundle\Controller\ResettingController
{
	protected function getRedirectionUrl(UserInterface $user)
	{
		return $this->container->get('router')->generate('CDEUtilityBundle_index');
	}

}
