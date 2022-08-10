<?php

namespace Lysice\LaravelSSO\Traits;

use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Lysice\LaravelSSO\Events\SSOLoginEvent;
use Lysice\SimpleSSO\Constants;
use Lysice\SimpleSSO\Exceptions\SSOServerException;

trait SSOServerTrait {
    /**
     * @param array $data
     * @param $extendData
     * @return string
     */
    public function loginQuery($data = [], $extendData = []) {
        try {
            $this->startBrokerSession();

            if (config('laravel-sso.usingUserId', false)) {
                if (!$userId = $this->authenticateQuery($data, true)) {
                    $this->fail('User authentication failed.', false, Constants::CODE_AUTH_FAILED);
                }
            } else {
                if (!$userId = $this->authenticateQuery($data)) {
                    $this->fail('User authentication failed.', false, Constants::CODE_AUTH_FAILED);
                }
            }
        } catch (SSOServerException $e) {
            return $this->returnJson(['error' => $e->getMessage(), 'code' => $e->getCode()]);
        }

        
        $this->setSessionData('sso_user', $userId);
        // hack
        event(new SSOLoginEvent($userId, $extendData));
        return $this->userInfoMulti();
    }

    protected function authenticateQuery($data = [], $usingUserId = false)
    {
        $where = [];
        $userWhereQueryEnabled = config('laravel-sso.userWhereQueryEnabled');
        foreach ($data as $key => $val) {
            if ($userWhereQueryEnabled && $key == 'or') {
                $where[] = function ($query) use ($val) {
                    $first = true;
                    foreach ($val as $i => $item) {
                        if ($first) {
                            $query->where($i, $item);
                            $first = false;
                        } else {
                            $query->orWhere($i, $item);
                        }
                    }
                };
            } else {
                $where[$key] = $val;
            }
        }

        $commonWhere = config('laravel-sso.userWhere', $where);
        $where = array_merge($commonWhere, $where);

        // validate before login attempt
        $before = config('laravel-sso.before.query');
        if (is_callable($before)) {
            $before($where);
        }

        if ($usingUserId) {
            $this->loginUsingUserId($where);
        } else {
            // attempt login user
            if(!Auth::attempt($where)) {
                return false;
            }
        }

        $sessionId = $this->getBrokerSessionId();
        $savedSessionId = $this->getBrokerSessionData($sessionId);
        $this->startSession($savedSessionId);

        return Auth::id();
    }

    /**
     * loginUsingUserId
     * @param array $where
     * @return bool
     * @throws SSOServerException
     */
    private function loginUsingUserId($where = []) {
        $model = config('laravel-sso.usersModel');

        $password = '';
        if (isset($where['password'])) {
            $password = $where['password'];
            unset($where['password']);
        }

        // select user by query
        $user = $this->getUserByQuery($model, $where);
        // validate if the user is empty
        if (empty($user)) {
            throw new SSOServerException("user not found", Constants::CODE_USER_NOT_FOUND);
        }
        // there is a password parameter provided in where condition but it does't match the user's password
        if (!empty($password) && !Hash::check($password, $user->password)) {
            throw new SSOServerException("password does't match.", Constants::CODE_PASSWORD_ERROR);
        }

        Auth::loginUsingId($user->id);
        return true;
    }

    /**
     * @param mixed $model userModel
     * @param array $where
     * @return mixed
     * @throws SSOServerException
     */
    private function getUserByQuery($model = '', $where = []) {
        if ($model == '') {
            throw new SSOServerException("usersModel is't config");
        }
        $query = $model::query();
        foreach ($where as $key => $val) {
            if (is_array($val)) {
                $query->whereIn($key, $val);
            } else if (is_callable($val)) {
                $val($query);
            } else {
                $query->where($key, $val);
            }
        }

        return $query->first();
    }

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
            return $this->returnJson(['error' => $e->getMessage(), 'code' => $e->getCode()]);
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
            return $this->returnJson(['error' => $e->getMessage(), 'code' => $e->getCode()]);
        }

        debug("userInfo", $user ? $user->toArray() : []);
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

        // validate before login attempt
        $before = config('laravel-sso.before.multi');
        if (is_callable($before)) {
            $before($where);
        }

        // attempt login user
        if(!Auth::attempt($where)) {
            return false;
        }

        $sessionId = $this->getBrokerSessionId();
        $savedSessionId = $this->getBrokerSessionData($sessionId);
        $this->startSession($savedSessionId);

        return Auth::id();
    }

    public function __call($name, $arguments)
    {
        return $this->$name(...$arguments);
    }
}
