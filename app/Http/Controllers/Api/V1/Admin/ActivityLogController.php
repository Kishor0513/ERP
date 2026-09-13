<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $logs = Activity::with('causer')
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->sendPaginated($logs);
    }
}
