<?php

use App\Http\Controllers\JobController;
use App\Http\Controllers\JobSubmissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home');
Route::view('/contact', 'contact');

Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/create', [JobController::class, 'create']);
Route::post('/jobs', [JobController::class, 'store'])->middleware('auth');
Route::get('/jobs/{job}', [JobController::class, 'show']);

Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])
    ->middleware('auth')
    ->can('edit', 'job');

Route::patch('/jobs/{job}', [JobController::class, 'update']);
Route::delete('/jobs/{job}', [JobController::class, 'destroy']);

// Auth
Route::get('/register', [RegisteredUserController::class, 'create']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);
Route::post('/logout', [SessionController::class, 'destroy']);

// Profile
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth')->name('profile');

// Job Submissions
Route::middleware('auth')->group(function () {
    // Step 1: Claim a job
    Route::post('/job-submissions/claim', [JobSubmissionController::class, 'claim']);
    
    // Step 2: Complete application for a claimed job
    Route::post('/job-submissions/complete', [JobSubmissionController::class, 'complete']);
    
    // Cancel a claimed job
    Route::get('/job-submissions/{submission}/cancel', [JobSubmissionController::class, 'cancel']);
    Route::post('/job-submissions/{submission}/cancel', [JobSubmissionController::class, 'cancel']);
    
    // View all submissions (sent and received)
    Route::get('/submissions', [JobSubmissionController::class, 'index']);
    
    // View a specific submission
    Route::get('/submissions/{submission}', [JobSubmissionController::class, 'show'])->middleware('auth');
    
    // File downloads
    Route::get('/files/{file}/download', [JobSubmissionController::class, 'downloadFile'])->middleware('auth')->name('file.download');

    // Admin routes
    Route::post('/admin/submissions/{submission}/approve', [AdminController::class, 'approveSubmission'])->middleware('auth');
    Route::post('/admin/submissions/{submission}/reject', [AdminController::class, 'rejectSubmission'])->middleware('auth');
    
    // Approve or decline a submission
    Route::post('/submissions/{submission}/approve', [JobSubmissionController::class, 'approve']);
    Route::post('/submissions/{submission}/decline', [JobSubmissionController::class, 'decline']);
});
