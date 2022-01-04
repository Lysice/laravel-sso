<?php

namespace Lysice\LaravelSSO\Traits;

use Illuminate\Http\Request;

trait SSOBrokerTrait {
    public function logoutWithCookie(Request $request)
    {
        $cookies = $request->cookie();
        $this->makeRequest('POST', 'logout', [], $cookies);
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
     *
     * @return bool
     */
    public function loginMulti(string $keyValue, string $password, string $key)
    {
        $this->userInfo = $this->makeRequest('POST', 'loginMulti', [
            $key => $keyValue,
            'password' => $password,
            'key' => $key
        ]);

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
     * @return bool
     */
    public function handleLogin($credentialUuid, $credentialPassword, $loginKey = '')
    {
        if(config('laravel-sso.multi_enabled')) {
            return $this->loginMulti($credentialUuid, $credentialPassword, $loginKey);
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
        } else {
            return $this->getUserInfo();
        }
    }
}
