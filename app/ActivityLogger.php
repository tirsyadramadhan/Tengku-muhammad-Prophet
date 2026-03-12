<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

trait ActivityLogger
{
    public function logActivity($action, $model = null, $oldData = null, $newData = null, $description = null)
    {
        $logData = [
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'description' => $description
        ];

        if ($model) {
            $logData['model'] = get_class($model);
            $logData['model_id'] = $model->id;
        }

        if ($oldData) {
            $logData['old_data'] = json_encode($oldData);
        }

        if ($newData) {
            $logData['new_data'] = json_encode($newData);
        }

        ActivityLog::create($logData);
    }

    public function logCreate($model, $description = null)
    {
        $description = $description ?? $this->getModelName($model) . ' created';
        $this->logActivity('create', $model, null, $model->toArray(), $description);
    }

    public function logUpdate($model, $oldData, $description = null)
    {
        $description = $description ?? $this->getModelName($model) . ' updated';
        $this->logActivity('update', $model, $oldData, $model->toArray(), $description);
    }

    public function logDelete($model, $oldData, $description = null)
    {
        $description = $description ?? $this->getModelName($model) . ' deleted';
        $this->logActivity('delete', $model, $oldData, null, $description);
    }

    public function logLogin()
    {
        $this->logActivity('login', null, null, null, 'Pengguna Login');
    }

    public function logLogout()
    {
        $this->logActivity('logout', null, null, null, 'Pengguna Logout');
    }

    private function getModelName($model)
    {
        return class_basename($model);
    }
}
