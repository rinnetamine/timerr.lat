<?php

use App\Http\Controllers\JobController;
use App\Http\Controllers\JobSubmissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// home page route
Route::view('/', 'home');

// contact form routes
Route::get('/contact', [ContactController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);

// job listing routes
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/create', [JobController::class, 'create']);
Route::post('/jobs', [JobController::class, 'store'])->middleware('auth');
Route::get('/jobs/{job}', [JobController::class, 'show']);

// job edit route with authorization
Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])
    ->middleware('auth')
    ->can('edit', 'job');

// job update and delete routes
Route::patch('/jobs/{job}', [JobController::class, 'update']);
Route::delete('/jobs/{job}', [JobController::class, 'destroy']);

// authentication routes
Route::get('/register', [RegisteredUserController::class, 'create']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);
Route::post('/logout', [SessionController::class, 'destroy']);

// profile route
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth')->name('profile');

// job submission routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    // job claiming routes
    Route::post('/job-submissions/claim', [JobSubmissionController::class, 'claim'])->name('job-submissions.claim');
    
    // job application completion route
    Route::post('/job-submissions/complete', [JobSubmissionController::class, 'complete']);
    
    // job cancellation routes
    Route::get('/job-submissions/{submission}/cancel', [JobSubmissionController::class, 'cancel']);
    Route::post('/job-submissions/{submission}/cancel', [JobSubmissionController::class, 'cancel']);
    
    // submissions listing route
    Route::get('/submissions', [JobSubmissionController::class, 'index']);
    
    // single submission view route
    Route::get('/submissions/{submission}', [JobSubmissionController::class, 'show'])->middleware('auth');
    // export submission as html
    Route::get('/submissions/{submission}/export', [JobSubmissionController::class, 'exportHtml'])->middleware('auth')->name('submissions.export');
    
    // file download route
    Route::get('/files/{file}/download', [JobSubmissionController::class, 'downloadFile'])->middleware('auth')->name('file.download');

    // export transactions PDF/HTML
    Route::get('/transactions/export', [TransactionController::class, 'exportPdf'])->name('transactions.export');

    // export transactions PDF/HTML download route
    Route::get('/transactions/download', [TransactionController::class, 'download'])->name('transactions.download');

    // admin routes
    Route::get('/admin/contact', [AdminController::class, 'contactMessages'])->middleware('auth');
    Route::post('/admin/submissions/{submission}/approve', [AdminController::class, 'approveSubmission'])->middleware('auth');
    Route::post('/admin/submissions/{submission}/reject', [AdminController::class, 'rejectSubmission'])->middleware('auth');
    
    // submission approval and rejection routes
    Route::post('/submissions/{submission}/approve', [JobSubmissionController::class, 'approve']);
    Route::post('/submissions/{submission}/decline', [JobSubmissionController::class, 'decline']);
});
