<?php

// Šis fails definē publiskos, autentificētos, administratora un ziņojumu maršrutus.

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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;

// Sākumlapa rāda platformas kopsavilkumu un jaunākos sludinājumus.
use App\Http\Controllers\HomeController;
Route::get('/', [HomeController::class, 'index']);

// Publiskais glabātuves maršruts atgriež tikai failus, kas eksistē public diskā.
Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return response()->file(Storage::disk('public')->path($path));
})->where('path', '.*');

// Kontaktformas maršruti.
Route::get('/contact', [ContactController::class, 'index']);
Route::post('/contact', [ContactController::class, 'store']);

// Darba sludinājumu maršruti.
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/create', [JobController::class, 'create']);
Route::post('/jobs', [JobController::class, 'store'])->middleware('auth');
Route::get('/jobs/{job}', [JobController::class, 'show']);

// Darba labošanai vajadzīga autorizācija un edit politika.
Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])
    ->middleware('auth')
    ->can('edit', 'job');

// Darba atjaunināšanas un dzēšanas maršruti.
Route::patch('/jobs/{job}', [JobController::class, 'update']);
Route::delete('/jobs/{job}', [JobController::class, 'destroy']);

// Autentifikācijas maršruti.
Route::get('/register', [RegisteredUserController::class, 'create']);
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/login', [SessionController::class, 'store']);
Route::post('/logout', [SessionController::class, 'destroy']);

// Profila maršruti.
Route::get('/profile', [ProfileController::class, 'show'])->middleware('auth')->name('profile');
Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->middleware('auth')->name('profile.change-password');
Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->middleware('auth')->name('profile.avatar');

// Darba pieteikumu, eksportu un administratora pamatmaršruti ir pieejami tikai pieslēgtiem lietotājiem.
Route::middleware('auth')->group(function () {
    // Darba saņemšanas maršruts.
    Route::post('/job-submissions/claim', [JobSubmissionController::class, 'claim'])->name('job-submissions.claim');
    
    // Darba pieteikuma pabeigšanas maršruts.
    Route::post('/job-submissions/complete', [JobSubmissionController::class, 'complete']);
    
    // Darba saņemšanas atcelšanas maršruti.
    Route::get('/job-submissions/{submission}/cancel', [JobSubmissionController::class, 'cancel']);
    Route::post('/job-submissions/{submission}/cancel', [JobSubmissionController::class, 'cancel']);
    
    // Pieteikumu saraksta maršruts.
    Route::get('/submissions', [JobSubmissionController::class, 'index']);
    
    // Viena pieteikuma skata maršruts.
    Route::get('/submissions/{submission}', [JobSubmissionController::class, 'show'])->middleware('auth')->name('submissions.show');
    // Pieteikuma PDF eksports.
    Route::get('/submissions/{submission}/export', [JobSubmissionController::class, 'exportHtml'])->middleware('auth')->name('submissions.export');
    
    // Pieteikuma faila lejupielādes maršruts.
    Route::get('/files/{file}/download', [JobSubmissionController::class, 'downloadFile'])->middleware('auth')->name('files.download');

    // Darījumu PDF priekšskatījuma maršruts.
    Route::get('/transactions/export', [TransactionController::class, 'exportPdf'])->name('transactions.export');

    // Darījumu PDF lejupielādes maršruts.
    Route::get('/transactions/download', [TransactionController::class, 'download'])->name('transactions.download');

    // Darījumu CSV lejupielādes maršruts.
    Route::get('/transactions/csv', [TransactionController::class, 'exportCsv'])->name('transactions.csv');

    // Darījumu Excel lejupielādes maršruts.
    Route::get('/transactions/excel', [TransactionController::class, 'exportExcel'])->name('transactions.excel');

    // Administratora paneļa un kontaktziņojumu maršruti.
    Route::get('/admin', [AdminController::class, 'index'])->middleware('auth')->name('admin.dashboard');
    Route::get('/admin/contact', [AdminController::class, 'contactMessages'])->middleware('auth')->name('admin.contact');
    // Kontaktziņojumu atzīmēšana kā lasītu vai nelasītu.
    Route::post('/admin/contact/{message}/mark-read', [AdminController::class, 'markContactRead'])->middleware('auth')->name('admin.contact.mark-read');
    Route::post('/admin/contact/{message}/mark-unread', [AdminController::class, 'markContactUnread'])->middleware('auth')->name('admin.contact.mark-unread');
    Route::delete('/admin/contact/{message}', [AdminController::class, 'deleteContact'])->middleware('auth')->name('admin.contact.delete');
    Route::post('/admin/submissions/{submission}/approve', [AdminController::class, 'approveSubmission'])->middleware('auth');
    Route::post('/admin/submissions/{submission}/reject', [AdminController::class, 'rejectSubmission'])->middleware('auth');
    
    // Pieteikumu apstiprināšanas un noraidīšanas maršruti.
    Route::post('/submissions/{submission}/approve', [JobSubmissionController::class, 'approve']);
    Route::post('/submissions/{submission}/decline', [JobSubmissionController::class, 'decline']);
    // Atsauksmju saglabāšanas maršruts.
    Route::post('/submissions/{submission}/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);

    // Strīdu maršruti.
    Route::get('/disputes', [DisputeController::class, 'index'])->name('disputes.index');
    Route::get('/submissions/{submission}/dispute', [DisputeController::class, 'create'])->name('disputes.create');
    Route::post('/submissions/{submission}/dispute', [DisputeController::class, 'store'])->name('disputes.store');
    Route::get('/disputes/{submission}', [DisputeController::class, 'show'])->name('disputes.show');
    Route::post('/disputes/{submission}/resolve', [DisputeController::class, 'resolve'])->name('disputes.resolve');
});

// Cilvēku saraksta un publiskā profila maršruti.
Route::get('/people', [PeopleController::class, 'index'])->name('people.index');
Route::get('/people/{user}', [PeopleController::class, 'show'])->name('people.show');

// Administratora lietotāju pārvaldības maršruti.
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/users/{user}/manage', [PeopleController::class, 'manage'])->name('admin.users.manage');
    Route::post('/admin/users/{user}/adjust-credits', [PeopleController::class, 'adjustCredits'])->name('admin.users.adjust-credits');
    Route::post('/admin/users/{user}/ban', [PeopleController::class, 'ban'])->name('admin.users.ban');
    Route::post('/admin/users/{user}/unban', [PeopleController::class, 'unban'])->name('admin.users.unban');
});

// Privāto ziņojumu maršruti.
Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessagesController::class, 'index'])->name('messages.index');
    Route::get('/messages/new', [MessagesController::class, 'create'])->name('messages.create');
    Route::get('/messages/{message}/attachment', [MessagesController::class, 'downloadFile'])->name('messages.files.download');
    Route::get('/messages/{user}', [MessagesController::class, 'conversation'])->name('messages.conversation');
    Route::post('/messages', [MessagesController::class, 'store'])->name('messages.store');
});
