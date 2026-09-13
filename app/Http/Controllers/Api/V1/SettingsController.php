<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingsController extends BaseController
{
    private const SETTINGS_KEY = 'system_settings';

    public function index(): JsonResponse
    {
        $settings = Cache::get(self::SETTINGS_KEY, $this->getDefaults());

        return $this->sendResponse($settings);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string',
            'currency' => 'nullable|string|max:3',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'quote_validity_days' => 'nullable|integer|min:1',
            'default_payment_terms' => 'nullable|string|max:100',
            'default_lead_time_days' => 'nullable|integer|min:0',
            'whatsapp_number' => 'nullable|string|max:50',
            'website_url' => 'nullable|url|max:255',
            'logo_path' => 'nullable|string|max:500',
            'footer_text' => 'nullable|string|max:500',
        ]);

        $currentSettings = Cache::get(self::SETTINGS_KEY, $this->getDefaults());
        $updatedSettings = array_merge($currentSettings, array_filter($validated, fn ($v) => $v !== null));

        Cache::forever(self::SETTINGS_KEY, $updatedSettings);

        return $this->sendResponse($updatedSettings, 'Settings updated successfully');
    }

    public function show(string $key): JsonResponse
    {
        $settings = Cache::get(self::SETTINGS_KEY, $this->getDefaults());

        if (! array_key_exists($key, $settings)) {
            return $this->sendError('Setting not found.', [], 404);
        }

        return $this->sendResponse(['key' => $key, 'value' => $settings[$key]]);
    }

    protected function getDefaults(): array
    {
        return [
            'company_name' => config('app.name'),
            'company_email' => config('mail.from.address'),
            'company_phone' => '',
            'company_address' => '',
            'currency' => 'INR',
            'tax_rate' => 18.0,
            'low_stock_threshold' => 10,
            'quote_validity_days' => 30,
            'default_payment_terms' => 'Net 30',
            'default_lead_time_days' => 14,
            'whatsapp_number' => '',
            'website_url' => '',
            'logo_path' => '',
            'footer_text' => '',
        ];
    }
}
