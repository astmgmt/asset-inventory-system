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
        
        // Check if asset exists and hasn't been disposed
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
            // Re-fetch asset with lock to prevent concurrent disposal
            $asset = Asset::where('id', $this->selectedAsset->id)
                ->where('is_disposed', false)
                ->lockForUpdate()
                ->first();

            if (!$asset) {
                throw new \Exception('This asset has already been disposed by another user');
            }

            DB::transaction(function () use ($asset) {
                // Create disposal record
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

                // Mark asset as disposed
                $asset->update(['is_disposed' => true]);

                // Send email notification
                $this->sendDisposalEmail($disposal, $asset);
            });

            $this->successMessage = 'Asset has been disposed successfully!';
            $this->closeModal();
            $this->resetDisposalForm();
            
            // Auto-hide success message after 3 seconds
            $this->dispatch('notify', message: $this->successMessage);
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Error: ' . $e->getMessage();
            $this->closeModal();
        }
    }

    private function sendDisposalEmail($disposal, $asset)
    {
        $user = Auth::user();
        
        // Get super admins and admins
        $admins = User::whereHas('role', function($q) {
            $q->whereIn('name', ['Super Admin', 'Admin']);
        })->get();
        
        $to = $admins->pluck('email')->toArray();
        
        // Generate email body directly using view
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
        
        // Send email
        $emailService = new SendEmail();
        $emailService->send(
            $to,
            "Asset Disposal: {$asset->asset_code} - {$asset->name}",
            $emailContent,
            [],
            null,
            null,
            true
        );
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