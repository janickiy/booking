<?php

function getSetting($key = '')
{
    $setting = \App\Models\Settings::where('name', $key)->first();

    if ($setting) {
        return $setting->value;
    } else {
        return '';
    }
}