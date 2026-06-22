<?php

namespace App\Filament\Accounting\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

class SalaryProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Profil Gaji';

    protected static ?string $title = 'Master Gaji Pegawai';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.accounting.pages.salary-profile';

    public array $pegawaiData = [];

    public $isEditing = false;

    public $editingIndex = null;

    public $editName = '';

    public $editClass = '';

    public $editSalary = 0;

    public $editMinRange = 0;

    public $editMaxRange = 0;

    // Filters and Sorting
    public $searchQuery = '';

    public ?array $filterData = [];

    public $sortColumn = 'name';

    public $sortDirection = 'asc';

    public function mount()
    {
        $this->pegawaiData = [
            ['nik' => 'EMP-001', 'name' => 'Budi Santoso', 'dept' => 'Produksi', 'class' => 'Operator Golongan I', 'salary' => 4000000, 'min' => 3500000, 'max' => 4500000],
            ['nik' => 'EMP-002', 'name' => 'Siti Aminah', 'dept' => 'Produksi', 'class' => 'Operator Golongan II', 'salary' => 4500000, 'min' => 4000000, 'max' => 5000000],
            ['nik' => 'EMP-003', 'name' => 'Agus Pratama', 'dept' => 'Gudang', 'class' => 'Staff Logistik', 'salary' => 4200000, 'min' => 4000000, 'max' => 5500000],
            ['nik' => 'EMP-004', 'name' => 'Rina Wijaya', 'dept' => 'HRD', 'class' => 'Staff HRD', 'salary' => 5000000, 'min' => 4500000, 'max' => 6000000],
            ['nik' => 'EMP-005', 'name' => 'Ahmad Fauzi', 'dept' => 'Produksi', 'class' => 'Supervisor Produksi', 'salary' => 7000000, 'min' => 6500000, 'max' => 8500000],
        ];
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('dept')
                    ->options([
                        'Produksi' => 'Produksi',
                        'Gudang' => 'Gudang',
                        'HRD' => 'HRD',
                    ])
                    ->native(false)
                    ->live()
                    ->placeholder('Semua Departemen')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-48']),

                Select::make('class')
                    ->options([
                        'Operator Golongan I' => 'Operator Golongan I',
                        'Operator Golongan II' => 'Operator Golongan II',
                        'Staff Logistik' => 'Staff Logistik',
                        'Staff HRD' => 'Staff HRD',
                        'Supervisor Produksi' => 'Supervisor Produksi',
                    ])
                    ->native(false)
                    ->live()
                    ->placeholder('Semua Kelas Jabatan')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-56']),
            ])
            ->statePath('filterData')
            ->columns(2);
    }

    public function getFilteredDataProperty()
    {
        $data = $this->pegawaiData;

        if (! empty($this->searchQuery)) {
            $data = array_filter($data, function ($item) {
                return stripos($item['name'], $this->searchQuery) !== false || stripos($item['nik'], $this->searchQuery) !== false;
            });
        }

        if (! empty($this->filterData['dept'])) {
            $data = array_filter($data, fn ($item) => $item['dept'] === $this->filterData['dept']);
        }

        if (! empty($this->filterData['class'])) {
            $data = array_filter($data, fn ($item) => $item['class'] === $this->filterData['class']);
        }

        uasort($data, function ($a, $b) {
            $valA = $a[$this->sortColumn] ?? '';
            $valB = $b[$this->sortColumn] ?? '';

            if ($valA == $valB) {
                return 0;
            }

            $result = ($valA < $valB) ? -1 : 1;

            return $this->sortDirection === 'asc' ? $result : -$result;
        });

        return $data;
    }

    public function sortBy($column)
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function openEditModal($index)
    {
        $this->editingIndex = $index;
        $data = $this->pegawaiData[$index];
        $this->editName = $data['name'];
        $this->editClass = $data['class'];
        $this->editSalary = $data['salary'];
        $this->editMinRange = $data['min'];
        $this->editMaxRange = $data['max'];
        $this->isEditing = true;
        $this->resetErrorBag();
    }

    public function closeEditModal()
    {
        $this->isEditing = false;
        $this->editingIndex = null;
    }

    public function saveSalary()
    {
        $this->validate([
            'editSalary' => [
                'required',
                'numeric',
                'min:'.$this->editMinRange,
                'max:'.$this->editMaxRange,
            ],
        ], [
            'editSalary.min' => 'Gaji Pokok tidak boleh di bawah batas minimal kelas jabatan (Rp '.number_format($this->editMinRange, 0, ',', '.').').',
            'editSalary.max' => 'Gaji Pokok tidak boleh melebihi batas maksimal kelas jabatan (Rp '.number_format($this->editMaxRange, 0, ',', '.').').',
        ]);

        $this->pegawaiData[$this->editingIndex]['salary'] = $this->editSalary;
        $this->closeEditModal();

        Notification::make()
            ->title('Berhasil disimpan')
            ->body('Gaji Pokok untuk '.$this->editName.' telah diperbarui.')
            ->success()
            ->send();
    }
}
