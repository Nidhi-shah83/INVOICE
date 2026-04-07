<?php

namespace App\Http\Livewire;

use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ClientIndex extends Component
{
    use WithPagination;

    public string $search = '';

    protected $paginationTheme = 'tailwind';

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn (Builder $query) => $query->where(function (Builder $sub) {
                $sub->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('gstin', 'like', '%'.$this->search.'%')
                    ->orWhere('company_name', 'like', '%'.$this->search.'%');
            }))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.client-index', [
            'clients' => $clients,
        ]);
    }
}
