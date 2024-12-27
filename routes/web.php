<?php

use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Route;

Scramble::registerUiRoute(path: 'docs/v1', api: 'v1');
Scramble::registerJsonSpecificationRoute(path: 'docs/v1.json', api: 'v1');
Route::redirect('/', 'docs/v1');