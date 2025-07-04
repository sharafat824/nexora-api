<?php

use App\Models\PlatformSetting;
use Illuminate\Http\JsonResponse;

if (!function_exists('success')) {
    function success($data = [], $message = 'Success', $status = 200): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'status'  => $status,
        ], $status);
    }
}

if (!function_exists('error')) {
    function error($message = 'Something went wrong', $status = 500, $errors = []): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'status'  => $status,
        ], $status);
    }
}
if (!function_exists('platform_setting')) {
    function platform_setting($key, $default = null) {
        return PlatformSetting::where('key', $key)->value('value') ?? $default;
    }
}
