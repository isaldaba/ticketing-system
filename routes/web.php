<?php

use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AccomplishmentReportController;
use App\Http\Controllers\GuestTicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StaffTicketController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});
Route::post('/guest/tickets', [GuestTicketController::class, 'store'])->name('guest.tickets.store');

Route::get('/dashboard', function () {
    return auth()->user()->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('staff.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminTicketController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/tickets', [AdminTicketController::class, 'index'])->name('admin.tickets.index');
    Route::get('/admin/staff', [AdminStaffController::class, 'index'])->name('admin.staff.index');
    Route::get('/admin/staff/accomplishment-report', [AccomplishmentReportController::class, 'adminMulti'])->name('admin.staff.accomplishment-report-multi');
    Route::get('/admin/staff/{user}/accomplishment-report', [AccomplishmentReportController::class, 'admin'])->name('admin.staff.accomplishment-report')->where('user', '[0-9]+');
    Route::post('/admin/tickets', [AdminTicketController::class, 'store'])->name('admin.tickets.store');
    Route::patch('/admin/tickets/{ticket}/publish', [AdminTicketController::class, 'publishGuestTicket'])->name('admin.tickets.publish');
    Route::patch('/admin/tickets/{ticket}/reject', [AdminTicketController::class, 'rejectGuestTicket'])->name('admin.tickets.reject');
    Route::patch('/admin/tickets/{ticket}/approve', [AdminTicketController::class, 'approve'])->name('admin.tickets.approve');
    Route::patch('/admin/tickets/{ticket}/report-dates', [AdminTicketController::class, 'updateReportDates'])->name('admin.tickets.report-dates.update');
    Route::patch('/admin/tickets/{ticket}/return', [AdminTicketController::class, 'returnToUser'])->name('admin.tickets.return');
    Route::post('/admin/tickets/{ticket}/notifications/review/read', [AdminTicketController::class, 'markReviewNotificationRead'])->name('admin.tickets.notifications.review.read');
    Route::post('/admin/notifications/read', [AdminTicketController::class, 'markNotificationsRead'])->name('admin.notifications.read');
});

Route::middleware(['auth', 'verified', 'role:staff'])->group(function () {
    Route::get('/staff/dashboard', [StaffTicketController::class, 'dashboard'])->name('staff.dashboard');
    Route::get('/staff/accomplishment-report', [AccomplishmentReportController::class, 'staff'])->name('staff.accomplishment-report');
    Route::patch('/staff/tickets/{ticket}/claim', [StaffTicketController::class, 'claim'])->name('staff.tickets.claim');
    Route::match(['post', 'patch'], '/staff/tickets/{ticket}/submit', [StaffTicketController::class, 'submitForReview'])->name('staff.tickets.submit');
    Route::post('/staff/tickets/{ticket}/notifications/return/read', [StaffTicketController::class, 'markReturnNotificationRead'])->name('staff.tickets.notifications.return.read');
    Route::post('/staff/notifications/read', [StaffTicketController::class, 'markNotificationsRead'])->name('staff.notifications.read');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
