<?php

use Illuminate\Support\Facades\Route;
use App\Models\Employee;

Route::get('/print-payroll/{month}/{year}', function ($month, $year) {
    $cacheKey = 'payroll_print_' . auth()->id();
    $data = \Illuminate\Support\Facades\Cache::get($cacheKey);

    if (!$data) {
        return redirect('/accounting/payroll')->with('error', 'Sesi cetak telah kedaluwarsa. Silakan tekan Generate Payroll lagi.');
    }

    $periodText = str_pad($month, 2, '0', STR_PAD_LEFT) . ' - ' . $year;

    return view('pdf.payslip', [
        'data' => $data,
        'periodText' => $periodText
    ]);
})->name('payroll.print')->middleware('web');
