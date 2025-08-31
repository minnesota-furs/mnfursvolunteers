<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditableObserver
{
    protected array $ignored = ['updated_at'];

    public function created($model)
    {
        $this->log($model, 'created');
    }

    public function updated($model)
    {
        // Filter out ignored fields
        $changes = collect($model->getChanges())
            ->except($this->ignored)
            ->toArray();

        if (empty($changes)) {
            return; // nothing meaningful changed
        }

        $this->log($model, 'updated', [
            'old' => collect($model->getOriginal())
                        ->only(array_keys($changes)),
            'new' => $changes,
        ]);
    }

    public function deleted($model)
    {
        $this->log($model, 'deleted');
    }

    protected function log($model, string $action, array $changes = [])
    {
        AuditLog::create([
            'action'         => $action,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->id,
            'changes'        => $changes,
            'user_id'        => Auth::id(),
        ]);
    }
}
