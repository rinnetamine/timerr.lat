<?php

use App\Http\Controllers\JobController;
use App\Http\Controllers\JobSubmissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\DisputeController;
use Illuminate\Support\Facades\Route;

// home page route
use App\Http\Controllers\HomeController;
Route::get('/', [HomeController::class, 'index']);

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
Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->middleware('auth')->name('profile.change-password');

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
    Route::get('/submissions/{submission}', [JobSubmissionController::class, 'show'])->middleware('auth')->name('submissions.show');
    // export submission as html
    Route::get('/submissions/{submission}/export', [JobSubmissionController::class, 'exportHtml'])->middleware('auth')->name('submissions.export');
    
    // file download route
    Route::get('/files/{file}/download', [JobSubmissionController::class, 'downloadFile'])->middleware('auth')->name('files.download');

    // export transactions PDF/HTML
    Route::get('/transactions/export', [TransactionController::class, 'exportPdf'])->name('transactions.export');

    // export transactions PDF/HTML download route
    Route::get('/transactions/download', [TransactionController::class, 'download'])->name('transactions.download');

    // export transactions CSV download route
    Route::get('/transactions/csv', [TransactionController::class, 'exportCsv'])->name('transactions.csv');

    // export transactions Excel download route
    Route::get('/transactions/excel', [TransactionController::class, 'exportExcel'])->name('transactions.excel');

    // admin routes
    Route::get('/admin', [AdminController::class, 'index'])->middleware('auth')->name('admin.dashboard');
    Route::get('/admin/contact', [AdminController::class, 'contactMessages'])->middleware('auth')->name('admin.contact');
    // mark contact messages read/unread
    Route::post('/admin/contact/{message}/mark-read', [AdminController::class, 'markContactRead'])->middleware('auth')->name('admin.contact.mark-read');
    Route::post('/admin/contact/{message}/mark-unread', [AdminController::class, 'markContactUnread'])->middleware('auth')->name('admin.contact.mark-unread');
    Route::delete('/admin/contact/{message}', [AdminController::class, 'deleteContact'])->middleware('auth')->name('admin.contact.delete');
    Route::post('/admin/submissions/{submission}/approve', [AdminController::class, 'approveSubmission'])->middleware('auth');
    Route::post('/admin/submissions/{submission}/reject', [AdminController::class, 'rejectSubmission'])->middleware('auth');
    
    // submission approval and rejection routes
    Route::post('/submissions/{submission}/approve', [JobSubmissionController::class, 'approve']);
    Route::post('/submissions/{submission}/decline', [JobSubmissionController::class, 'decline']);
    // reviews
    Route::post('/submissions/{submission}/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);

    // dispute routes
    Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/submissions/{submission}/dispute', [DisputeController::class, 'create'])->name('disputes.create');
    Route::post('/submissions/{submission}/dispute', [DisputeController::class, 'store'])->name('disputes.store');
    Route::get('/disputes/{submission}', [DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{submission}/resolve', [DisputeController::class, 'resolve'])->name('disputes.resolve');
});

// people listing and profile 
Route::get('/people', [PeopleController::class, 'index'])->name('people.index');
Route::get('/people/{user}', [PeopleController::class, 'show'])->name('people.show');

// admin user management routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/users/{user}/manage', [PeopleController::class, 'manage'])->name('admin.users.manage');
    Route::post('/admin/users/{user}/adjust-credits', [PeopleController::class, 'adjustCredits'])->name('admin.users.adjust-credits');
    Route::post('/admin/users/{user}/ban', [PeopleController::class, 'ban'])->name('admin.users.ban');
    Route::post('/admin/users/{user}/unban', [PeopleController::class, 'unban'])->name('admin.users.unban');
});

// messaging
Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessagesController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessagesController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages', [MessagesController::class, 'store'])->name('messages.store');
});
