<?php

use App\Http\Controllers\ClassRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/class-records', [ClassRecordController::class, 'index']);
Route::post('/class-records', [ClassRecordController::class, 'store']);
Route::put('/class-records/{id}', [ClassRecordController::class, 'update']);
Route::post('/class-records/score', [ClassRecordController::class, 'updateScore']);
Route::post('/class-records/total-score', [ClassRecordController::class, 'updateTotalScore']);
Route::post('/class-records/generate', [ClassRecordController::class, 'generate']);
Route::get('/class-records/teacher', [ClassRecordController::class, 'viewClassRecordTeacher']);
Route::get('/class-records/subject/{subjectLoadId}', [ClassRecordController::class, 'viewClassRecord']);
Route::get('/class-records/by-subject-load', [ClassRecordController::class, 'bySubjectLoad']);
Route::get('/class-records/export/{subjectLoadId}', [ClassRecordController::class, 'export']);
Route::get('/class-records/download/{subjectLoadId}/{fileName}', [ClassRecordController::class, 'downloadExcel']);
