<?php

if (! function_exists('str_random')) {
    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     *
     * @throws \RuntimeException
     *
     * @deprecated Str::random() should be used directly instead. Will be removed in Laravel 6.0.
     */
    function str_random($length = 16)
    {
        return \Illuminate\Support\Str::random($length);
    }
}
if (!function_exists('callConfigFunction')) {
    /**
     * get data from extra config functions
     * @param $functionArr
     * @param $parameters
     * @return mixed
     */
    function callConfigFunction($functionArr, $parameters)
    {
        $callable = explode('@', $functionArr['uses']);
        return call_user_func([$callable[0], $callable[1]], $parameters);
    }
}
