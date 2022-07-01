<?php

namespace Lysice\LaravelSSO\Traits;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Lysice\LaravelSSO\Events\SSOLoginEvent;
use Lysice\SimpleSSO\Constants;
use Lysice\SimpleSSO\Exceptions\SSOServerException;

trait SSOServerTrait {
    /**
     * @param null|string $username
     * @param null|string $password
     * @param null|string $key
     * @param null | array $extendData
     *
     * @return string
     */
    public function loginMulti(?string $keyValue, ?string $password, ?string $key, ?array $extendData)
    {
        try {
            $this->startBrokerSession();

            if (!$keyValue || !$password) {
                $this->fail('No keyVale and/or password provided.', false, Constants::CODE_NO_USERNAME_OR_PASSWORD_PROVIDED);
            }

            if (!$userId = $this->authenticateMulti($keyValue, $password, $key)) {
                $this->fail('User authentication failed.', false, Constants::CODE_AUTH_FAILED);
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        $this->setSessionData('sso_user', $userId);
        // hack
        event(new SSOLoginEvent($userId, $extendData));
        return $this->userInfoMulti();
    }

    /**
     * Returning user info for the broker.
     *
     * @return string
     */
    public function userInfoMulti()
    {
        try {
            $this->startBrokerSession();

            $userId = $this->getSessionData('sso_user');

            if (!$userId) {
                $this->fail('User not authenticated. Session ID: ' . $this->getSessionData('id'), false, Constants::CODE_USER_NOT_LOGIN);
            }

            if (!$user = $this->getUserInfoMulti($userId)) {
                $this->fail('User not found.', false, Constants::CODE_USER_NOT_FOUND);
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage()]);
        }

        return $this->returnUserInfo($user);
    }

    /**
     * Get the information about a user
     *
     * @param string $userId
     *
     * @return array|object|null
     */
    protected function getUserInfoMulti(string $userId)
    {
        try {
            $where = false;
            if (config('laravel-sso.userInfoWhereEnabled')) {
                $where = config('laravel-sso.userWhere');
            }

            $user = config('laravel-sso.usersModel')::where('id', $userId)
                ->when($where, function ($query) use ($where) {
                    foreach ($where as $key => $value) {
                        $query->where($key, $value);
                    }
                    return $query;
                })
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        }

        return $user;
    }

    protected function authenticateMulti(string $value, string $password, string $key)
    {
        $where = config('laravel-sso.userWhere');
        $where = array_merge($where, [$key => $value, 'password' => $password]);
        if(!Auth::attempt($where)) {
            return false;
        }

        $sessionId = $this->getBrokerSessionId();
        $savedSessionId = $this->getBrokerSessionData($sessionId);
        $this->startSession($savedSessionId);

        return Auth::id();
    }
}
