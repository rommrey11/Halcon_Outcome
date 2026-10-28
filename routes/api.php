<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;

// Rutas públicas (sin middleware auth) para pruebas en Postman
Route::get('orders/invoice/{invoice}', [OrderController::class, 'getByInvoice']);
Route::get('orders/archived', [OrderController::class, 'archived']);

Route::apiResource('users', UserController::class);
Route::apiResource('orders', OrderController::class);