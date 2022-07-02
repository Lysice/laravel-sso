<?php

namespace Lysice\LaravelSSO\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Lysice\LaravelSSO\LaravelSSOBroker;

class SSOAutoLogin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $broker = new LaravelSSOBroker();
        $response = $broker->handleGetUserInfo();

        // If client is logged out in SSO server but still logged in broker.
        if (!isset($response['data']) && !auth()->guest()) {
            return $this->logout($request, $broker);
        }

        // If there is a problem with data in SSO server, we will re-attach client session.
        if (isset($response['error']) && strpos($response['error'], 'There is no saved session data associated with the broker session id') !== false) {
            return $this->clearSSOCookie($request);
        }

        // If client is logged in SSO server and didn't logged in broker...
        if (isset($response['data']) && (auth()->guest() || auth()->user()->id != $response['data']['id'])) {
            // ... we will authenticate our client.
            auth()->loginUsingId($response['data']['id']);
        }

        return $next($request);
    }

    /**
     * Clearing SSO cookie so broker will re-attach SSO server session.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function clearSSOCookie(Request $request)
    {
        return redirect($request->fullUrl())->cookie(cookie('sso_token_' . config('laravel-sso.brokerName')));
    }

    /**
     * Logging out authenticated user.
     * Need to make a page refresh because current page may be accessible only for authenticated users.
     * @param Request $request
     * @param LaravelSSOBroker $broker
     * @param bool $redirect
     * @param bool $regenerate
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function logout(Request $request, LaravelSSOBroker $broker, $redirect = true, $regenerate = true)
    {
        $broker->logoutWithCookie($request);
        Auth::guard()->logout();
        if ($regenerate) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($redirect) {
            return redirect($request->fullUrl());
        }
    }

    /**
     * Logging out authenticated user.
     * Need to return your own response
     * @param Request $request
     * @param LaravelSSOBroker $broker
     * @param bool $regenerate
     * @param Closure|null $returnHandler
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    protected function apiLogout(Request $request, LaravelSSOBroker $broker, $regenerate = true, \Closure $returnHandler = null) {
        $broker->logoutWithCookie($request);
        Auth::guard()->logout();
        if ($regenerate) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if (is_null($returnHandler)) {
            return response()->json([
                'error_code' => 401,
                'error_message' => '您已退出登录',
                'data' => [],
                'message' => '您已退出登录',
                'code' => 200
            ]);
        }

        return $returnHandler();
    }
}
