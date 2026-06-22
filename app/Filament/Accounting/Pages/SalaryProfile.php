<?php

namespace App\Filament\Accounting\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class SalaryProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Profil Gaji';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Master Gaji Pegawai';

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
    
    // Pagination
    public $currentPage = 1;
    public $perPage = 10;

    public function mount()
    {
        $this->loadData();
        $this->form->fill();
    }

    public function loadData()
    {
        $this->pegawaiData = \App\Models\Employee::with(['department', 'jobClass'])
            ->active()
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'nik' => $emp->nik,
                    'name' => $emp->full_name,
                    'dept' => $emp->department?->name ?? '-',
                    'class' => $emp->jobClass?->name ?? '-',
                    'salary' => $emp->base_salary,
                    'min' => $emp->jobClass?->min_salary ?? 0,
                    'max' => $emp->jobClass?->max_salary ?? 0,
                ];
            })->toArray();
    }

    public function form(\Filament\Schemas\Schema $form): \Filament\Schemas\Schema
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('dept')
                    ->options(\App\Models\Department::pluck('name', 'name'))
                    ->native(false)
                    ->live()
                    ->placeholder('Semua Departemen')
                    ->hiddenLabel()
                    ->extraAttributes(['class' => 'w-full sm:w-48']),
                
                \Filament\Forms\Components\Select::make('class')
                    ->options(\App\Models\JobClass::pluck('name', 'name'))
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

        if (!empty($this->searchQuery)) {
            $data = array_filter($data, function ($item) {
                return stripos($item['name'], $this->searchQuery) !== false || stripos($item['nik'], $this->searchQuery) !== false;
            });
        }

        if (!empty($this->filterData['dept'])) {
            $data = array_filter($data, fn($item) => $item['dept'] === $this->filterData['dept']);
        }

        if (!empty($this->filterData['class'])) {
            $data = array_filter($data, fn($item) => $item['class'] === $this->filterData['class']);
        }

        uasort($data, function ($a, $b) {
            $valA = $a[$this->sortColumn] ?? '';
            $valB = $b[$this->sortColumn] ?? '';

            if ($valA == $valB) return 0;
            
            $result = ($valA < $valB) ? -1 : 1;
            return $this->sortDirection === 'asc' ? $result : -$result;
        });

        return $data;
    }

    public function getPaginatedDataProperty()
    {
        $filtered = $this->filteredData;
        $offset = ($this->currentPage - 1) * $this->perPage;
        return array_slice($filtered, $offset, $this->perPage, true);
    }

    public function getTotalPagesProperty()
    {
        $total = count($this->filteredData);
        return $total > 0 ? ceil($total / $this->perPage) : 1;
    }

    public function nextPage()
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
        }
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function setPage($page)
    {
        $this->currentPage = $page;
    }

    public function updatedSearchQuery() { $this->currentPage = 1; }
    public function updatedFilterData() { $this->currentPage = 1; }

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
        
        // Find by index in the array or by ID? The blade passes the array index.
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
        $rules = ['required', 'numeric'];
        $messages = [];

        if ($this->editMinRange > 0) {
            $rules[] = 'min:' . $this->editMinRange;
            $messages['editSalary.min'] = 'Gaji Pokok tidak boleh di bawah batas minimal kelas jabatan (Rp ' . number_format($this->editMinRange, 0, ',', '.') . ').';
        }

        if ($this->editMaxRange > 0) {
            $rules[] = 'max:' . $this->editMaxRange;
            $messages['editSalary.max'] = 'Gaji Pokok tidak boleh melebihi batas maksimal kelas jabatan (Rp ' . number_format($this->editMaxRange, 0, ',', '.') . ').';
        }

        $this->validate(['editSalary' => $rules], $messages);

        $empId = $this->pegawaiData[$this->editingIndex]['id'];
        \App\Models\Employee::where('id', $empId)->update(['base_salary' => $this->editSalary]);

        $this->pegawaiData[$this->editingIndex]['salary'] = $this->editSalary;
        $this->closeEditModal();
        
        \Filament\Notifications\Notification::make()
            ->title('Berhasil disimpan')
            ->body('Gaji Pokok untuk ' . $this->editName . ' telah diperbarui.')
            ->success()
            ->send();
    }
}
