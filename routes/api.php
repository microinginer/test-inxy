<?php

use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;


Route::middleware('api')->group(function () {

    Route::post('/users/{id}/deposit', [TransactionController::class, 'deposit']);
    Route::post('/users/{id}/transfer', [TransactionController::class, 'transfer']);

    Route::put('/users/{id}', [UserController::class, 'update']);
});
