<?php

use Illuminate\Support\Facades\Route;

Route::get('/chirper', function () {
    return view('chirper');
});
