<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AccountingPanelProvider;
use App\Providers\Filament\EmployeePanelProvider;
use App\Providers\Filament\HrdPanelProvider;

return [
    AppServiceProvider::class,
    AccountingPanelProvider::class,
    EmployeePanelProvider::class,
    HrdPanelProvider::class,
];
