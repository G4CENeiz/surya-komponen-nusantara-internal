<?php

namespace App\Filament\Accounting\Resources\Reimbursements\Pages;

use App\Filament\Accounting\Resources\Reimbursements\ReimbursementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReimbursement extends CreateRecord
{
    protected static string $resource = ReimbursementResource::class;
}
