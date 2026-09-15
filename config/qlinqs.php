<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dev profile slug
    |--------------------------------------------------------------------------
    |
    | Temporary stand-in for authentication: the page content/theme endpoints
    | act on this single profile's page until real auth/ownership exists.
    | Remove once auth is wired up.
    |
    */

    'dev_profile_slug' => env('QLINQS_DEV_PROFILE_SLUG', 'teste'),

];
