<?php

namespace App\Filament\Accounting\Pages;

use Filament\Pages\Page;

class CostSettings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Pengaturan Biaya';

    protected static ?string $title = 'Pengaturan Biaya Potongan';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.accounting.pages.cost-settings';

    public $overtime_rate = 25000;

    public $bpjs_kes_percent = 1;

    public $bpjs_tk_percent = 2;

    public $late_penalty = 50000;
}
