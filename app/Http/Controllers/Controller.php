<?php

/**
 * Standalone Base Controller
 * 
 * Base Controller dengan method helper untuk response.
 * File ini dapat digunakan di project Laravel lain.
 * 
 * Cara penggunaan:
 * 1. Copy file ini ke app/Http/Controllers/Controller.php
 * 2. Atau extend controller Anda dari class ini
 */

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Success response helper
     * 
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Exception error response helper
     * 
     * @param \Exception $e
     * @param string $exception
     * @param int $status
     * @return \Illuminate\Http\JsonResponse
     */
    public function exceptionError($e, $exception, $status = 400)
    {
        $request = request();

        // Optional: Log error jika ErrorLog model tersedia
        // Uncomment jika Anda memiliki ErrorLog model
        // try {
        //     ErrorLog::create([
        //         'user_id' => optional($request->user())->id,
        //         'method' => $request->method(),
        //         'url' => $request->fullUrl(),
        //         'message' => $e->getMessage(),
        //         'trace' => $e->getTraceAsString(),
        //         'payload' => json_encode($request->all()),
        //     ]);
        // } catch (\Exception $logException) {
        //     // Ignore logging errors
        // }

        // Gunakan status code dari exception jika ada
        if (method_exists($e, 'getCode') && $e->getCode() > 0 && $e->getCode() < 600) {
            $status = $e->getCode();
        }

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => 'Exception Error : ' . $exception,
        ], $status);
    }

    /**
     * Simple JSON response helper
     * 
     * @param mixed $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function respond($data)
    {
        return response()->json($data);
    }
}

