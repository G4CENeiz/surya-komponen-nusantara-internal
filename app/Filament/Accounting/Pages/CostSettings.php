<?php

namespace App\Filament\Accounting\Pages;

use Filament\Pages\Page;

class CostSettings extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Biaya';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Pengaturan Biaya Potongan';

    protected string $view = 'filament.accounting.pages.cost-settings';

    public $overtime_rate = 25000;
    public $bpjs_kes_percent = 1;
    public $bpjs_tk_percent = 2;
    public $late_penalty = 50000;

    public function mount()
    {
        $this->overtime_rate = \App\Models\PayrollSetting::where('key', 'overtime_rate')->value('value') ?? 25000;
        $this->bpjs_kes_percent = \App\Models\PayrollSetting::where('key', 'bpjs_kes_percent')->value('value') ?? 1;
        $this->bpjs_tk_percent = \App\Models\PayrollSetting::where('key', 'bpjs_tk_percent')->value('value') ?? 2;
        $this->late_penalty = \App\Models\PayrollSetting::where('key', 'late_penalty')->value('value') ?? 50000;
    }

    public function saveOvertime()
    {
        $this->updateSetting('overtime_rate', 'Tarif Lembur', $this->overtime_rate, 'fixed');
        \Filament\Notifications\Notification::make()->success()->title('Tarif Lembur Disimpan')->send();
    }

    public function savePenalty()
    {
        $this->updateSetting('late_penalty', 'Denda Keterlambatan', $this->late_penalty, 'fixed');
        \Filament\Notifications\Notification::make()->success()->title('Denda Disimpan')->send();
    }

    public function saveBpjs()
    {
        $this->updateSetting('bpjs_kes_percent', 'BPJS Kesehatan', $this->bpjs_kes_percent, 'percentage');
        $this->updateSetting('bpjs_tk_percent', 'BPJS Ketenagakerjaan', $this->bpjs_tk_percent, 'percentage');
        \Filament\Notifications\Notification::make()->success()->title('Konfigurasi BPJS Disimpan')->send();
    }

    private function updateSetting($key, $name, $value, $type)
    {
        // Hilangkan titik ribuan jika ada (dari input HTML format number/text)
        $val = str_replace('.', '', $value);
        \App\Models\PayrollSetting::updateOrCreate(
            ['key' => $key],
            [
                'name' => $name,
                'value' => (float) $val,
                'type' => $type
            ]
        );
    }
}
