<?php

namespace App\Livewire\SuperAdmin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\SendEmail;
use Illuminate\Support\Facades\DB;

class AssetDisposals extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedAsset;
    public $showDisposeModal = false;
    public $showConfirmModal = false;
    public $disposalMethod = '';
    public $reason = '';
    public $notes = '';
    public $successMessage = '';
    public $errorMessage = '';

    #[Layout('components.layouts.app')]
    public function render()
    {
        $assets = Asset::query()
            ->with(['category', 'condition', 'location'])
            ->whereHas('condition', function($query) {
                $query->whereIn('condition_name', ['Defective', 'Available', 'New']);
            })
            ->where('is_disposed', false)
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('assets.asset_code', 'like', '%'.$this->search.'%')
                    ->orWhere('assets.name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('category', function($q) {
                        $q->where('category_name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('location', function($q) {
                        $q->where('location_name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('condition', function($q) {
                        $q->where('condition_name', 'like', '%'.$this->search.'%');
                    });
                });
            })
            ->paginate(10);

        return view('livewire.super-admin.asset-disposals', [
            'assets' => $assets,
            'disposalMethods' => [
                'sold',
                'scrapped',
                'donated',
                'recycled',
                'traded_in',
                'returned',
                'transferred',
                'lost_stolen'
            ]
        ]);
    }


    public function disposeAsset($assetId)
    {
        $this->selectedAsset = Asset::with(['category', 'condition', 'location', 'vendor'])
            ->find($assetId);
        
        if (!$this->selectedAsset) {
            $this->errorMessage = 'Asset not found';
            return;
        }
        
        if ($this->selectedAsset->is_disposed) {
            $this->errorMessage = 'This asset has already been disposed';
            return;
        }
            
        $this->resetDisposalForm();
        $this->showDisposeModal = true;
    }

    public function confirmDisposal()
    {
        $this->validate([
            'disposalMethod' => 'required',
            'reason' => 'required|min:10',
        ], [
            'disposalMethod.required' => 'Please select a disposal method.',
            'reason.required' => 'Please provide a reason for disposal.',
            'reason.min' => 'Reason should be at least 10 characters.',
        ]);

        $this->showConfirmModal = true;
    }

    public function performDisposal()
    {
        try {
            $asset = Asset::where('id', $this->selectedAsset->id)
                ->where('is_disposed', false)
                ->lockForUpdate()
                ->first();

            if (!$asset) {
                throw new \Exception('This asset has already been disposed by another user');
            }

            DB::transaction(function () use ($asset) {
                $disposal = AssetDisposal::create([
                    'asset_id' => $asset->id,
                    'disposed_by' => Auth::id(),
                    'disposal_date' => now(),
                    'method' => $this->disposalMethod,
                    'reason' => $this->reason,
                    'notes' => $this->notes,
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now(),
                ]);

                $disposedCondition = \App\Models\AssetCondition::firstOrCreate(
                    ['condition_name' => 'Disposed'],
                    ['condition_name' => 'Disposed']
                );

                $asset->update([
                    'is_disposed' => true,
                    'condition_id' => $disposedCondition->id 
                ]);

                $this->sendDisposalEmail($disposal, $asset);
            });

            $this->successMessage = 'Asset has been disposed successfully!';
            $this->closeModal();
            $this->resetDisposalForm();
            $this->dispatch('notify', message: $this->successMessage);
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error: ' . $e->getMessage();
            $this->closeModal();
        }
    }

    private function sendDisposalEmail($disposal, $asset)
    {
        $user = Auth::user();
        
        $admins = User::whereHas('role', function($q) {
            $q->whereIn('name', ['Super Admin', 'Admin']);
        })->get();
        
        $emailContent = view('emails.asset-disposal', [
            'disposalId' => $disposal->id,
            'assetCode' => $asset->asset_code,
            'assetName' => $asset->name,
            'condition' => $asset->condition->condition_name,
            'category' => $asset->category->category_name,
            'location' => $asset->location->location_name,
            'disposalMethod' => $this->disposalMethod,
            'disposedBy' => $user->name,
            'reason' => $this->reason,
            'notes' => $this->notes
        ])->render();
        
        $emailService = new SendEmail();
        foreach ($admins as $admin) {
            $emailService->send(
                $admin->email,  
                "Asset Disposal: {$asset->asset_code} - {$asset->name}",
                $emailContent,
                [],
                null,
                null,
                true
            );
        }
    }

    public function closeModal()
    {
        $this->showDisposeModal = false;
        $this->showConfirmModal = false;
        $this->selectedAsset = null;
    }

    public function clearSearch()
    {
        $this->search = '';
    }

    private function resetDisposalForm()
    {
        $this->disposalMethod = '';
        $this->reason = '';
        $this->notes = '';
    }
    public function clearMessages()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }
}