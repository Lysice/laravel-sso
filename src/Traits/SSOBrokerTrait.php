<?php

namespace Lysice\LaravelSSO\Traits;
use Illuminate\Http\Request;
trait SSOBrokerTrait {
    public function customizeQuery(array $data = [], array $extendData = [])
    {
        $this->userInfo = $this->makeRequest('POST', 'customizeQuery', [
            'data' => $data,
            'extendData' => $extendData
        ]);

        if (config('laravel-sso.loginReturnType') == 'array') {
            return $this->userInfo;
        }

        if (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id'])) {
            return true;
        }

        return false;
    }

    public function logoutWithCookie(Request $request)
    {
        $cookies = $request->cookie();
        $this->makeRequest('POST', 'logout', []);
    }
    /**
     * api check login. support for social sso check.
     * @param string $flag
     * @return mixed
     */
    public function check($flag = '')
    {
        $this->userInfo = $this->makeRequest('POST', 'check', [
            'flag' => $flag
        ]);

        return $this->userInfo;
    }

    /**
     * Login client to SSO server with user credentials.
     *
     * @param string $username
     * @param string $password
     * @param string $key
     * @param array $extendData
     * @return bool
     */
    public function loginMulti(string $keyValue, string $password, string $key, array $extendData)
    {
        $this->userInfo = $this->makeRequest('POST', 'loginMulti', [
            $key => $keyValue,
            'password' => $password,
            'key' => $key,
            'extendData' => $extendData
        ]);

        if (config('laravel-sso.loginReturnType') == 'array') {
            return $this->userInfo;
        }

        if (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Getting user info from SSO based on client session.
     *
     * @return array
     */
    public function getUserInfoMulti()
    {
        if (!isset($this->userInfo) || !$this->userInfo) {
            $this->userInfo = $this->makeRequest('GET', 'userInfoMulti');
        }

        return $this->userInfo;
    }

    /**
     * @param array $credentials
     * @param string $loginKey
     * @param string $loginKey
     * @param array $extendData
     * @return bool
     */
    public function handleLogin($credentialUuid, $credentialPassword, $loginKey = '', $extendData = [])
    {
        if(config('laravel-sso.multi_enabled')) {
            return $this->loginMulti($credentialUuid, $credentialPassword, $loginKey, $extendData);
        }

        return $this->login($credentialUuid, $credentialPassword);
    }

    /**
     * @return mixed
     */
    public function handleGetUserInfo()
    {
        if (config('laravel-sso.multi_enabled')) {
            return $this->getUserInfoMulti();
        }
        if (config('laravel-sso.query_enabled')){
            return $this->getUserInfoMulti();
        }
        return $this->getUserInfo();
    }

    /**
     * Login client to SSO server with user credentials.
     * @param array $data
     * @param array $extendData
     * @return bool
     */
    public function loginQuery(array $data = [], array $extendData = [])
    {
        $this->userInfo = $this->makeRequest('POST', 'loginQuery', [
            'data' => $data,
            'extendData' => $extendData
        ]);
        if (config('laravel-sso.loginReturnType') == 'array') {
            return $this->userInfo;
        }

        if (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Login client to SSO server with user credentials.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function login(string $username, string $password)
    {
        $this->userInfo = $this->makeRequest('POST', 'login', compact('username', 'password'));

        if (config('laravel-sso.loginReturnType') == 'array') {
            return $this->userInfo;
        }
        if (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id'])) {
            return true;
        }

        return false;
    }
}
