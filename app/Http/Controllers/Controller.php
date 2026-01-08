<?php
namespace App\Http\Controllers;

use App\Models\ErrorLog;
use OpenApi\Annotations as OA;

abstract class Controller
{
    public function exceptionError($e, $exception, $status = 400)
    {
        $request = request();

        ErrorLog::create([
            'user_id' => optional($request->user())->id,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'payload' => json_encode($request->all()),
        ]);


        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => 'Exception Error : ' . $exception,
        ], $status);
    }


    public function successResponse($data, $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public function respond($data)
    {
        return response()->json($data);
    }
}
