<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/roles', [UserController::class, 'roles']);
Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus']);

Route::get('/orders/track', [OrderController::class, 'track']);
Route::get('/orders/invoice/{invoice}', [OrderController::class, 'getByInvoice']);
Route::get('/orders/archived', [OrderController::class, 'archived']);

Route::apiResource('users', UserController::class);
Route::apiResource('orders', OrderController::class);