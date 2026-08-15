<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    private string $gatewayUrl;

    public function __construct()
    {
        $this->gatewayUrl = config('services.api_gateway.url', env('API_GATEWAY_URL', 'http://localhost:8080'));
    }

    /**
     * Endpoint: Kirim notifikasi jatuh ke Telegram (Proxy ke Spring Boot microservice)
     * POST /notify/fall
     * Body JSON: { lat, lng, mapsUrl, totalAcc }
     */
    public function notifyFall(Request $request)
    {
        try {
            $response = Http::timeout(8)
                ->post($this->gatewayUrl . '/api/notif/fall', [
                    'lat'      => $request->input('lat'),
                    'lng'      => $request->input('lng'),
                    'mapsUrl'  => $request->input('mapsUrl'),
                    'totalAcc' => $request->input('totalAcc'),
                ]);

            if ($response->successful()) {
                return response()->json($response->json(), 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi NotifTele-Service via Gateway: HTTP ' . $response->status(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 200);
        }
    }

    /**
     * Endpoint: Ambil Chat ID dari update terbaru bot (Proxy ke Spring Boot microservice)
     * GET /notify/get-chat-id
     */
    public function getChatId()
    {
        try {
            $response = Http::timeout(8)
                ->get($this->gatewayUrl . '/api/notif/get-chat-id');

            if ($response->successful()) {
                return response()->json($response->json(), 200);
            }

            return response()->json([
                'chat_id' => 'belum_tersedia',
                'hint'    => 'Gagal terhubung ke NotifTele-Service via Gateway.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'chat_id' => 'belum_tersedia',
                'hint'    => 'Exception: ' . $e->getMessage(),
            ], 200);
        }
    }
}
