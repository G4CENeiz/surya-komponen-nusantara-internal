<?php

namespace App\Filament\Accounting\Pages;

use Filament\Pages\Page;

class AccountingDashboard extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Ringkasan';
    protected static ?string $title = 'Dashboard Dept Keuangan';
    protected static ?string $slug = '';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.accounting.pages.accounting-dashboard';

    public bool $isReviewingOvertime = false;

    public function openOvertimeReview()
    {
        $this->isReviewingOvertime = true;
    }

    public function closeOvertimeReview()
    {
        $this->isReviewingOvertime = false;
    }

    public function approveOvertime()
    {
        $this->isReviewingOvertime = false;
        \Filament\Notifications\Notification::make()
            ->title('Rekap Lembur Berhasil Divalidasi')
            ->body('Data lembur telah disetujui dan siap masuk ke draft penggajian.')
            ->success()
            ->send();
    }
}
