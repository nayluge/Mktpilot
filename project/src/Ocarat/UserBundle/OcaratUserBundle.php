<?php

namespace Ocarat\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class OcaratUserBundle extends Bundle
{

    public function getParent()
    {
        return 'FOSUserBundle';
    }

}
