<?php

if (!function_exists('set_locale_from_session')) {
    function set_locale_from_session()
    {
        if (session()->has('locale')) {
            app()->setLocale(session()->get('locale'));
        }
    }
}