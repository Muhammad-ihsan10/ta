<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private string $gatewayUrl;

    public function __construct()
    {
        $this->gatewayUrl = config('services.api_gateway.url', env('API_GATEWAY_URL', 'http://localhost:8080'));
    }

    public function index()
    {
        return view('dashboard');
    }

    public function history()
    {
        return view('history');
    }

    /**
     * Endpoint polling AJAX – mengembalikan semua data sensor terbaru
     */
    public function sensorData()
    {
        session_write_close(); // Lepaskan session lock agar polling tidak antri
        $gps   = $this->fetchSafe('/api/gps/latest');
        $mpu   = $this->fetchSafe('/api/mpu/latest');

        return response()->json([
            'gps'  => $gps,
            'mpu'  => $mpu,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
          ->header('Pragma', 'no-cache')
          ->header('Expires', 'Sat, 26 Jul 1997 05:00:00 GMT');
    }

    /**
     * History GPS
     */
    public function gpsHistory(int $limit = 20)
    {
        session_write_close();
        $data = $this->fetchSafe("/api/gps/history/{$limit}");
        return response()->json($data);
    }


    /**
     * History MPU
     */
    public function mpuHistory(int $limit = 20)
    {
        session_write_close();
        $data = $this->fetchSafe("/api/mpu/history/{$limit}");
        return response()->json($data);
    }

    /**
     * Helper: fetch dari API Gateway, return null jika gagal
     */
    private function fetchSafe(string $path): mixed
    {
        try {
            $response = Http::timeout(3)->get($this->gatewayUrl . $path);
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
