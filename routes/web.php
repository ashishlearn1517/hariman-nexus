<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
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
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\ExpenseCategoryController;
use App\Http\Controllers\Finance\ExpenseController;
use App\Http\Controllers\Finance\VendorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hariman', function () {
    return view('hariman');
})->name('hariman');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'can:view dashboard'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/sales/projects', [ProjectController::class, 'index'])->middleware('can:view projects')->name('sales.projects.index');
    Route::post('/sales/projects', [ProjectController::class, 'store'])->middleware('can:create projects')->name('sales.projects.store');
    Route::get('/sales/projects/{project}/edit', [ProjectController::class, 'edit'])->middleware('can:edit projects')->name('sales.projects.edit');
    Route::patch('/sales/projects/{project}', [ProjectController::class, 'update'])->middleware('can:edit projects')->name('sales.projects.update');
    Route::get('/sales/clients', [ClientController::class, 'index'])->middleware('can:view clients')->name('sales.clients.index');
    Route::post('/sales/clients', [ClientController::class, 'store'])->middleware('can:create clients')->name('sales.clients.store');
    Route::get('/sales/clients/{client}/edit', [ClientController::class, 'edit'])->middleware('can:edit clients')->name('sales.clients.edit');
    Route::patch('/sales/clients/{client}', [ClientController::class, 'update'])->middleware('can:edit clients')->name('sales.clients.update');
    Route::get('/sales/products', [ProductController::class, 'index'])->middleware('can:view products')->name('sales.products.index');
    Route::post('/sales/products', [ProductController::class, 'store'])->middleware('can:create products')->name('sales.products.store');
    Route::get('/sales/products/{product}/edit', [ProductController::class, 'edit'])->middleware('can:edit products')->name('sales.products.edit');
    Route::patch('/sales/products/{product}', [ProductController::class, 'update'])->middleware('can:edit products')->name('sales.products.update');
    Route::get('/sales/services', [ServiceController::class, 'index'])->middleware('can:view services')->name('sales.services.index');
    Route::post('/sales/services', [ServiceController::class, 'store'])->middleware('can:create services')->name('sales.services.store');
    Route::get('/sales/services/{service}/edit', [ServiceController::class, 'edit'])->middleware('can:edit services')->name('sales.services.edit');
    Route::patch('/sales/services/{service}', [ServiceController::class, 'update'])->middleware('can:edit services')->name('sales.services.update');
    Route::get('/sales/terms', [TermConditionController::class, 'index'])->middleware('can:view terms')->name('sales.terms.index');
    Route::post('/sales/terms', [TermConditionController::class, 'store'])->middleware('can:create terms')->name('sales.terms.store');
    Route::get('/sales/terms/{term}/edit', [TermConditionController::class, 'edit'])->middleware('can:edit terms')->name('sales.terms.edit');
    Route::patch('/sales/terms/{term}', [TermConditionController::class, 'update'])->middleware('can:edit terms')->name('sales.terms.update');

    Route::middleware('can:manage settings')->group(function () {
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
    });

    Route::get('/transactions/quotations', [QuotationController::class, 'index'])->middleware('can:view quotations')->name('transactions.quotations.index');
    Route::post('/transactions/quotations', [QuotationController::class, 'store'])->middleware('can:create quotations')->name('transactions.quotations.store');
    Route::get('/transactions/quotations/{quotation}/status', [QuotationController::class, 'status'])->middleware('can:view quotations')->name('transactions.quotations.status');
    Route::patch('/transactions/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->middleware('can:approve quotations')->name('transactions.quotations.status.update');
    Route::get('/transactions/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->middleware('can:edit quotations')->name('transactions.quotations.edit');
    Route::patch('/transactions/quotations/{quotation}', [QuotationController::class, 'update'])->middleware('can:edit quotations')->name('transactions.quotations.update');
    Route::post('/transactions/quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->middleware('can:create quotations')->name('transactions.quotations.duplicate');
    Route::delete('/transactions/quotations/{quotation}', [QuotationController::class, 'destroy'])->middleware('can:delete quotations')->name('transactions.quotations.destroy');
    Route::get('/transactions/quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->middleware('can:view quotations')->name('transactions.quotations.pdf');
    Route::post('/transactions/quotations/{quotation}/send', [QuotationController::class, 'send'])->middleware('can:send quotations')->name('transactions.quotations.send');
    Route::get('/transactions/quotations/{quotation}', [QuotationController::class, 'show'])->middleware('can:view quotations')->name('transactions.quotations.show');
    Route::get('/transactions/invoices', [InvoiceController::class, 'index'])->middleware('can:view invoices')->name('transactions.invoices.index');
    Route::post('/transactions/invoices', [InvoiceController::class, 'store'])->middleware('can:create invoices')->name('transactions.invoices.store');
    Route::get('/transactions/invoices/{invoice}/payment-status', [InvoiceController::class, 'paymentStatus'])->middleware('can:view invoices')->name('transactions.invoices.payment-status');
    Route::post('/transactions/invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->middleware('can:manage payments')->name('transactions.invoices.payments.store');
    Route::delete('/transactions/invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'destroyPayment'])->middleware('can:manage payments')->name('transactions.invoices.payments.destroy');
    Route::get('/transactions/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->middleware('can:edit invoices')->name('transactions.invoices.edit');
    Route::patch('/transactions/invoices/{invoice}', [InvoiceController::class, 'update'])->middleware('can:edit invoices')->name('transactions.invoices.update');
    Route::post('/transactions/invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->middleware('can:create invoices')->name('transactions.invoices.duplicate');
    Route::delete('/transactions/invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('can:delete invoices')->name('transactions.invoices.destroy');
    Route::get('/transactions/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->middleware('can:view invoices')->name('transactions.invoices.pdf');
    Route::post('/transactions/invoices/{invoice}/send', [InvoiceController::class, 'send'])->middleware('can:send invoices')->name('transactions.invoices.send');
    Route::post('/transactions/invoices/{invoice}/send-reminder', [InvoiceController::class, 'sendReminder'])->middleware('can:send invoices')->name('transactions.invoices.send-reminder');
    Route::post('/transactions/invoices/{invoice}/send-overdue', [InvoiceController::class, 'sendOverdue'])->middleware('can:send invoices')->name('transactions.invoices.send-overdue');
    Route::get('/transactions/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('can:view invoices')->name('transactions.invoices.show');

    Route::get('/finance/expense-categories', [ExpenseCategoryController::class, 'index'])->middleware('can:view expense categories')->name('finance.expense-categories.index');
    Route::post('/finance/expense-categories', [ExpenseCategoryController::class, 'store'])->middleware('can:create expense categories')->name('finance.expense-categories.store');
    Route::get('/finance/expense-categories/{expenseCategory}/edit', [ExpenseCategoryController::class, 'edit'])->middleware('can:edit expense categories')->name('finance.expense-categories.edit');
    Route::patch('/finance/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->middleware('can:edit expense categories')->name('finance.expense-categories.update');
    Route::delete('/finance/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->middleware('can:delete expense categories')->name('finance.expense-categories.destroy');
    Route::get('/finance/expenses', [ExpenseController::class, 'index'])->middleware('can:view expenses')->name('finance.expenses.index');
    Route::post('/finance/expenses', [ExpenseController::class, 'store'])->middleware('can:create expenses')->name('finance.expenses.store');
    Route::get('/finance/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->middleware('can:edit expenses')->name('finance.expenses.edit');
    Route::patch('/finance/expenses/{expense}', [ExpenseController::class, 'update'])->middleware('can:edit expenses')->name('finance.expenses.update');
    Route::delete('/finance/expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('can:delete expenses')->name('finance.expenses.destroy');
    Route::get('/finance/vendors', [VendorController::class, 'index'])->middleware('can:view vendors')->name('finance.vendors.index');
    Route::post('/finance/vendors', [VendorController::class, 'store'])->middleware('can:create vendors')->name('finance.vendors.store');
    Route::get('/finance/vendors/{vendor}/edit', [VendorController::class, 'edit'])->middleware('can:edit vendors')->name('finance.vendors.edit');
    Route::patch('/finance/vendors/{vendor}', [VendorController::class, 'update'])->middleware('can:edit vendors')->name('finance.vendors.update');
    Route::delete('/finance/vendors/{vendor}', [VendorController::class, 'destroy'])->middleware('can:delete vendors')->name('finance.vendors.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('can:view activity logs')
        ->name('activity-logs.index');

    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('can:view reports')
        ->name('reports.index');
});

require __DIR__.'/auth.php';
