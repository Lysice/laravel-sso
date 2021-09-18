<?php

namespace Lysice\LaravelSSO\Traits;

use Illuminate\Http\Request;
use Lysice\LaravelSSO\LaravelSSOServer;

trait SSOControllerTrait
{
    /**
     * @param LaravelSSOServer $server
     * @return string
     */
    public function userInfoMulti(LaravelSSOServer $server)
    {
        return $server->userInfoMulti();
    }

    /**
     * @param Request $request
     * @param LaravelSSOServer $server
     *
     * @return mixed
     */
    public function loginMulti(Request $request, LaravelSSOServer $server)
    {
        $key = $request->get('key');

        return $server->loginMulti(
            $request->get($key, null),
            $request->get('password', null),
            $request->get('key', null)
        );
    }
}
