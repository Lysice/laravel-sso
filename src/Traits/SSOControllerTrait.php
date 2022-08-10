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

    /**
     * api check
     * @param Request $request
     * @param LaravelSSOServer $server
     * @return \Illuminate\Http\JsonResponse | string
     */
    public function check(Request $request, LaravelSSOServer $server)
    {
        $flag = $request->input('flag');

        if(empty($flag)) {
            return $this->response([], 204, 'the login key required.');
        }
        // get userId
        $userId = callConfigFunction(config('laravel-sso.api.getUserId'), ['flag' => $flag]);

        if(empty($userId)) {
            return $this->response([], 201, 'cannot find user_id or the user has not logged in.');
        }

        // get User
        $user = config('laravel-sso.usersModel')::find($userId);
        if (empty($user)) {
            return $this->response([], 202, '用户未找到');
        }

        // handle login
        $password = callConfigFunction(config('laravel-sso.api.getPassword'), $user);
        if(config('laravel-sso.multi_enabled')) {
            $result = $server->loginMulti(
                $user->username,
                $password,
                'username',
                []
            );
        } else if (config('laravel-sso.query_enabled')) {
            $result = $server->loginQuery(['username' => $user->username, 'password' => $password]);
        } else {
            $result = $server->login($user->username, $password);
        }

        // sso-login hack
        event(new ApiSSOLoginEvent($user, $flag));
        $merged = callConfigFunction(config('laravel-sso.api.getMerged'), ['flag' => $flag]);

        if($result instanceof UserResource) {
            $result = $result->toArray($request);
        }
        $result = empty($merged) ? $result : array_merge($merged, (array)$result);

        return $this->response($result);
    }
}
