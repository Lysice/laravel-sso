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
