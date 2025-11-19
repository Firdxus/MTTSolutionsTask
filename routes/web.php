<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalesReportController;

Route::get('/', fn() => redirect()->route('report.index'));
Route::get('/report', [SalesReportController::class, 'index'])->name('report.index');
Route::get('/report/export', [SalesReportController::class, 'export'])->name('report.export');
