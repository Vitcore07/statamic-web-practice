<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Entry;

Route::get('/', function () {
    return Entry::findOrFail('31fb0e30-69f5-44ce-9694-5ba2078e23e9');
});
