<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Entry;

Route::get('/', function () {
    $landingPage = Entry::findOrFail('31fb0e30-69f5-44ce-9694-5ba2078e23e9')->in('slovak');

    abort_if(! $landingPage, 404);

    return $landingPage;
});

Route::get('/en', function () {
    $landingPage = Entry::findOrFail('31fb0e30-69f5-44ce-9694-5ba2078e23e9')->in('default');

    abort_if(! $landingPage, 404);

    return $landingPage;
});
