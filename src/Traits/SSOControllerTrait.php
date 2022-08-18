<?php

namespace Lysice\LaravelSSO\Traits;

use Lysice\LaravelSSO\Events\ApiSSOLoginEvent;
use Lysice\LaravelSSO\Events\WeChatSSOLoginEvent;
use Illuminate\Http\Request;
use Lysice\LaravelSSO\LaravelSSOServer;
use Lysice\LaravelSSO\Resources\UserResource;
use Lysice\SimpleSSO\Exceptions\SSOServerException;

trait SSOControllerTrait
{
    /**
     * custom query login method
     * @param Request $request
     * @param LaravelSSOServer $server
     * @return mixed|string
     */
    public function customizeQuery(Request $request, LaravelSSOServer $server)
    {
        $validator = config('laravel-sso.validators.customizeQuery');
        $data = $request->all();
        if (isset($validator) && is_callable($validator)) {
            try {
                $data = $validator($request);
            } catch (SSOServerException $exception) {
                return $server->returnJson(['error' => $exception->getMessage(), 'code' => $exception->getCode()]);
            }
        }

        return $server->loginQuery(
            $data,
            $request->get('extendData', [])
        );
    }

    public function loginQuery(Request $request, LaravelSSOServer $server)
    {
        return $server->loginQuery(
            $request->get('data', []),
            $request->get('extendData', null)
        );
    }

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
            $request->get('key', null),
            $request->get('extendData', null)
        );
    }

    /**
     * json response return
     * @param string $data
     * @param int $error_code
     * @param string $error_message
     * @return \Illuminate\Http\JsonResponse
     */
    private function response($data = '', $error_code = 0, $error_message = '')
    {
        return response()->json([
            'code' => 200,
            'error_message' => $error_message,
            'error_code' => $error_code,
            'data' => empty($data) ? new \StdClass() : $data,
            'message' => ''
        ]);
    }
}
