<?php

use Illuminate\Support\Facades\Route;


Route::view('/criteria', 'criteria_config.index')->name('criteria_config.index');
Route::view('/criteria-config', 'criteria_config.create')->name('criteria_config.create');
require __DIR__.'/report.php';