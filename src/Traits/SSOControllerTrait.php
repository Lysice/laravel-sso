<?php

namespace Lysice\LaravelSSO\Traits;

use Lysice\LaravelSSO\LaravelSSOServer;

trait SSOControllerTrait
{
    public function userInfoMulti(LaravelSSOServer $server)
    {
        return $server->userInfoMulti();
    }
}
