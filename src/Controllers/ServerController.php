<?php

namespace Lysice\LaravelSSO\Controllers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Lysice\LaravelSSO\Events\ApiSSOLogoutEvent;
use Lysice\LaravelSSO\LaravelSSOServer;
use Lysice\LaravelSSO\Traits\SSOControllerTrait;

class ServerController extends BaseController
{
    use SSOControllerTrait;
    /**
     * @param Request $request
     * @param LaravelSSOServer $server
     *
     * @return void
     */
    public function attach(Request $request, LaravelSSOServer $server)
    {
        $server->attach(
            $request->get('broker', null),
            $request->get('token', null),
            $request->get('checksum', null)
        );
    }

    /**
     * @param Request $request
     * @param LaravelSSOServer $server
     *
     * @return mixed
     */
    public function login(Request $request, LaravelSSOServer $server)
    {
        return $server->login(
            $request->get('username', null),
            $request->get('password', null)
        );
    }

    /**
     * @param LaravelSSOServer $server
     *
     * @return string
     */
    public function logout(Request $request, LaravelSSOServer $server)
    {
        /**@var JsonResource */
        $result = $server->logout();
        if(config('laravel-sso.api.enabled')) {
            $res = $result->getData(true);
            // logout event invocked
            if (isset($res['success'])) {
                event(new ApiSSOLogoutEvent($request));
            }
        }

        return $result;
    }

    /**
     * @param LaravelSSOServer $server
     *
     * @return string
     */
    public function userInfo(LaravelSSOServer $server)
    {
        return $server->userInfo();
    }
}
