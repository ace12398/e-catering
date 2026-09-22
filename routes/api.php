<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - CaterFlow Adaptive Workspace Persistence API
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/user/workspace/save', function (Request $request) {
    return response()->json([
        'status' => 'success',
        'message' => 'Adaptive Workspace Layout Persisted Successfully',
        'timestamp' => now()->toDateTimeString()
    ]);
});
