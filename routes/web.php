<?php

use App\Http\Controllers\InvoicePdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportExportController;
use App\Livewire\Creditors;
use App\Livewire\Customers;
use App\Livewire\Dashboard;
use App\Livewire\DirectorAccount;
use App\Livewire\Expenses;
use App\Livewire\Invoices;
use App\Livewire\Jobs;
use App\Livewire\Lorries;
use App\Livewire\Reports;
use App\Livewire\Users;
use App\Livewire\Vehicles;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/customers', Customers\Index::class)->name('customers.index');

    Route::get('/jobs', Jobs\Index::class)->name('jobs.index');
    Route::get('/jobs/{job}', Jobs\Show::class)->name('jobs.show');

    Route::get('/invoices', Invoices\Index::class)->name('invoices.index');
    Route::get('/invoices/{invoice}', Invoices\Show::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/pdf', InvoicePdfController::class)->name('invoices.pdf');

    Route::get('/expenses', Expenses\Index::class)->name('expenses.index');
    Route::get('/vehicles', Vehicles\Index::class)->name('vehicles.index');
    Route::get('/lorries', Lorries\Index::class)->name('lorries.index');
    Route::get('/reports', Reports\Index::class)->name('reports.index');

    Route::middleware('role:gm')->group(function () {
        Route::get('/director-account', DirectorAccount\Index::class)->name('director-account.index');
        Route::get('/creditors', Creditors\Index::class)->name('creditors.index');
        Route::get('/reports/customer-profit/export', [ReportExportController::class, 'customerProfitExcel'])->name('reports.customer-profit.export');
        Route::get('/reports/management-report/pdf', [ReportExportController::class, 'managementReportPdf'])->name('reports.management-report.pdf');
        Route::get('/users', Users\Index::class)->name('users.index');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
