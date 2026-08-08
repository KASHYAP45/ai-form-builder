<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\PublicFormController;
use App\Http\Controllers\SubmissionController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::any(
    '/',
    [GuestController::class, 'interviewAssessment']
)->name('interview.assessment');


// Public form - slug
Route::get(
    '/forms/{form_id}',
    [PublicFormController::class, 'show']
)->name('forms.public');


// Submit public form - slug
Route::post(
    '/forms/{slug}',
    [PublicFormController::class, 'submit']
)->name('forms.submit');


// Submissions - Form ID
Route::get(
    '/forms/{form}/submissions',
    [SubmissionController::class, 'index']
)->name('submissions.index');


// CSV export - Form ID
Route::get(
    '/forms/{form}/submissions/export',
    [SubmissionController::class, 'export']
)->name('submissions.export');