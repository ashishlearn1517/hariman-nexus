<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sales\ClientController;
use App\Http\Controllers\Sales\ProductController;
use App\Http\Controllers\Sales\ProjectController;
use App\Http\Controllers\Sales\ServiceController;
use App\Http\Controllers\Sales\TermConditionController;
use App\Http\Controllers\Settings\CompanySetupController;
use App\Http\Controllers\Settings\CurrencyController;
use App\Http\Controllers\Settings\EmailSettingController;
use App\Http\Controllers\Settings\NumberingSettingController;
use App\Http\Controllers\Settings\TaxSettingController;
use App\Http\Controllers\Transactions\InvoiceController;
use App\Http\Controllers\Transactions\QuotationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/sales/projects', [ProjectController::class, 'index'])->name('sales.projects.index');
    Route::post('/sales/projects', [ProjectController::class, 'store'])->name('sales.projects.store');
    Route::get('/sales/projects/{project}/edit', [ProjectController::class, 'edit'])->name('sales.projects.edit');
    Route::patch('/sales/projects/{project}', [ProjectController::class, 'update'])->name('sales.projects.update');
    Route::get('/sales/clients', [ClientController::class, 'index'])->name('sales.clients.index');
    Route::post('/sales/clients', [ClientController::class, 'store'])->name('sales.clients.store');
    Route::get('/sales/clients/{client}/edit', [ClientController::class, 'edit'])->name('sales.clients.edit');
    Route::patch('/sales/clients/{client}', [ClientController::class, 'update'])->name('sales.clients.update');
    Route::get('/sales/products', [ProductController::class, 'index'])->name('sales.products.index');
    Route::post('/sales/products', [ProductController::class, 'store'])->name('sales.products.store');
    Route::get('/sales/products/{product}/edit', [ProductController::class, 'edit'])->name('sales.products.edit');
    Route::patch('/sales/products/{product}', [ProductController::class, 'update'])->name('sales.products.update');
    Route::get('/sales/services', [ServiceController::class, 'index'])->name('sales.services.index');
    Route::post('/sales/services', [ServiceController::class, 'store'])->name('sales.services.store');
    Route::get('/sales/services/{service}/edit', [ServiceController::class, 'edit'])->name('sales.services.edit');
    Route::patch('/sales/services/{service}', [ServiceController::class, 'update'])->name('sales.services.update');
    Route::get('/sales/terms', [TermConditionController::class, 'index'])->name('sales.terms.index');
    Route::post('/sales/terms', [TermConditionController::class, 'store'])->name('sales.terms.store');
    Route::get('/sales/terms/{term}/edit', [TermConditionController::class, 'edit'])->name('sales.terms.edit');
    Route::patch('/sales/terms/{term}', [TermConditionController::class, 'update'])->name('sales.terms.update');
    Route::get('/settings/company', [CompanySetupController::class, 'edit'])->name('settings.company.edit');
    Route::patch('/settings/company', [CompanySetupController::class, 'update'])->name('settings.company.update');
    Route::get('/settings/currencies', [CurrencyController::class, 'index'])->name('settings.currencies.index');
    Route::post('/settings/currencies', [CurrencyController::class, 'store'])->name('settings.currencies.store');
    Route::get('/settings/currencies/{currency}/edit', [CurrencyController::class, 'edit'])->name('settings.currencies.edit');
    Route::patch('/settings/currencies/{currency}', [CurrencyController::class, 'update'])->name('settings.currencies.update');
    Route::get('/settings/taxes', [TaxSettingController::class, 'index'])->name('settings.taxes.index');
    Route::post('/settings/taxes', [TaxSettingController::class, 'store'])->name('settings.taxes.store');
    Route::get('/settings/taxes/{tax}/edit', [TaxSettingController::class, 'edit'])->name('settings.taxes.edit');
    Route::patch('/settings/taxes/{tax}', [TaxSettingController::class, 'update'])->name('settings.taxes.update');
    Route::get('/settings/email', [EmailSettingController::class, 'edit'])->name('settings.email.edit');
    Route::patch('/settings/email', [EmailSettingController::class, 'update'])->name('settings.email.update');
    Route::get('/settings/numbering', [NumberingSettingController::class, 'edit'])->name('settings.numbering.edit');
    Route::patch('/settings/numbering', [NumberingSettingController::class, 'update'])->name('settings.numbering.update');
    Route::get('/transactions/quotations', [QuotationController::class, 'index'])->name('transactions.quotations.index');
    Route::post('/transactions/quotations', [QuotationController::class, 'store'])->name('transactions.quotations.store');
    Route::get('/transactions/quotations/{quotation}/status', [QuotationController::class, 'status'])->name('transactions.quotations.status');
    Route::patch('/transactions/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('transactions.quotations.status.update');
    Route::get('/transactions/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('transactions.quotations.edit');
    Route::patch('/transactions/quotations/{quotation}', [QuotationController::class, 'update'])->name('transactions.quotations.update');
    Route::post('/transactions/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('transactions.quotations.duplicate');
    Route::delete('/transactions/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('transactions.quotations.destroy');
    Route::get('/transactions/quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('transactions.quotations.pdf');
    Route::post('/transactions/quotations/{quotation}/send', [QuotationController::class, 'send'])->name('transactions.quotations.send');
    Route::get('/transactions/quotations/{quotation}', [QuotationController::class, 'show'])->name('transactions.quotations.show');
    Route::get('/transactions/invoices', [InvoiceController::class, 'index'])->name('transactions.invoices.index');
    Route::post('/transactions/invoices', [InvoiceController::class, 'store'])->name('transactions.invoices.store');
    Route::get('/transactions/invoices/{invoice}/payment-status', [InvoiceController::class, 'paymentStatus'])->name('transactions.invoices.payment-status');
    Route::get('/transactions/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('transactions.invoices.edit');
    Route::patch('/transactions/invoices/{invoice}', [InvoiceController::class, 'update'])->name('transactions.invoices.update');
    Route::post('/transactions/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('transactions.invoices.duplicate');
    Route::delete('/transactions/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('transactions.invoices.destroy');
    Route::get('/transactions/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('transactions.invoices.pdf');
    Route::post('/transactions/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('transactions.invoices.send');
    Route::get('/transactions/invoices/{invoice}', [InvoiceController::class, 'show'])->name('transactions.invoices.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
