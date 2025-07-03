<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\AssetBorrowItem;
use App\Models\AssetCondition;

class TrackAssets extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedAssetId = null;
    public $showHistoryModal = false;
    public $historyPerPage = 10;
    public $historySearch = '';

    public function render()
    {
        $assets = Asset::with(['condition'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('asset_code', 'like', '%'.$this->search.'%')
                      ->orWhere('model_number', 'like', '%'.$this->search.'%')
                      ->orWhere('name', 'like', '%'.$this->search.'%')
                      ->orWhereHas('condition', function ($q) {
                          $q->where('condition_name', 'like', '%'.$this->search.'%');
                      });
                });
            })
            ->paginate(10);

        $history = null;
        if ($this->selectedAssetId) {
            $history = AssetBorrowItem::with([
                    'transaction.user.department',
                    'returnItem'
                ])
                ->where('asset_id', $this->selectedAssetId)
                ->when($this->historySearch, function ($query) {
                    $query->where(function ($q) {
                        $q->whereHas('transaction.user', function ($userQuery) {
                            $userQuery->where('name', 'like', '%'.$this->historySearch.'%');
                        })
                        ->orWhereHas('transaction.user.department', function ($deptQuery) {
                            $deptQuery->where('name', 'like', '%'.$this->historySearch.'%');
                        })
                        ->orWhereHas('transaction', function ($transQuery) {
                            $transQuery->where('borrow_code', 'like', '%'.$this->historySearch.'%');
                        })
                        ->orWhereHas('returnItem', function ($returnQuery) {
                            $returnQuery->where('return_code', 'like', '%'.$this->historySearch.'%');
                        })
                        ->orWhereHas('transaction', function ($transQuery) {
                            $transQuery->whereDate('borrowed_at', 'like', '%'.$this->historySearch.'%');
                        })
                        ->orWhereHas('returnItem', function ($returnQuery) {
                            $returnQuery->whereDate('returned_at', 'like', '%'.$this->historySearch.'%');
                        });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate($this->historyPerPage, ['*'], 'historyPage');
        }

        return view('livewire.super-admin.track-assets', [
            'assets' => $assets,
            'history' => $history,
            'conditions' => AssetCondition::all(),
        ]);
    }

    public function showHistory($assetId)
    {
        $this->selectedAssetId = $assetId;
        $this->historySearch = '';
        $this->showHistoryModal = true;
        $this->resetPage('historyPage');
    }

    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->selectedAssetId = null;
        $this->resetPage('historyPage');
    }
}