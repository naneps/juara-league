<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Get all settings grouped by their group
     */
    public function index(): JsonResponse
    {
        $settings = Setting::all()->groupBy('group');
        
        $formatted = [];
        foreach ($settings as $group => $items) {
            foreach ($items as $item) {
                $formatted[$group][$item->key] = Setting::get($item->key);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }

    /**
     * Get specific settings for public use (e.g., maintenance status)
     */
    public function publicIndex(): JsonResponse
    {
        $keys = [
            'platform_name',
            'platform_tagline',
            'maintenance_mode',
            'registration_enabled',
            'contact_email',
            'social_links'
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = Setting::get($key);
        }

        return response()->json([
            'status' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * Update settings in bulk
     */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($data['settings'] as $item) {
            $setting = Setting::where('key', $item['key'])->first();
            if ($setting) {
                $value = $item['value'];
                if ($setting->type === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                } elseif ($setting->type === 'json' && (is_array($value) || is_object($value))) {
                    $value = json_encode($value);
                }
                
                $setting->update(['value' => $value]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully'
        ]);
    }
}
