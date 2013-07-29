<?php

namespace CDE\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CDEUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
