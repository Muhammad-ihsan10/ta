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
     * Export data ke Excel (format CSV dengan BOM agar dibaca rapi oleh Excel)
     * Berdasarkan range waktu: day (hari ini), week (minggu ini), month (bulan ini)
     */
    public function export(Request $request)
    {
        session_write_close();
        $type  = $request->query('type', 'gps');
        $range = $request->query('range', 'day');

        // Batas maksimal data yang ditarik untuk laporan
        $limit = 5000;
        
        if ($type === 'gps') {
            $data = $this->fetchSafe("/api/gps/history/{$limit}") ?? [];
            $headers = ['No', 'Latitude', 'Longitude', 'Satelit', 'HDOP', 'Google Maps Link', 'Waktu'];
        } else {
            $data = $this->fetchSafe("/api/mpu/history/{$limit}") ?? [];
            $headers = ['No', 'Akselerasi X', 'Akselerasi Y', 'Akselerasi Z', 'Total Akselerasi', 'Status Gerakan', 'Waktu'];
        }

        // Filter berdasarkan range waktu (menggunakan Carbon)
        $now = now();
        $filtered = [];
        foreach ($data as $row) {
            if (empty($row['timestamp'])) continue;
            $ts = \Carbon\Carbon::parse($row['timestamp']);
            
            if ($range === 'day' && !$ts->greaterThanOrEqualTo($now->copy()->subDay())) continue;
            if ($range === 'week' && !$ts->greaterThanOrEqualTo($now->copy()->subWeek())) continue;
            if ($range === 'month' && !$ts->greaterThanOrEqualTo($now->copy()->subMonth())) continue;
            
            $filtered[] = $row;
        }

        $filename = "laporan_{$type}_{$range}_" . now()->format('Y-m-d_H-i-s') . ".csv";

        $callback = function() use ($filtered, $type, $headers) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM agar terbaca kolomnya dengan benar saat dibuka di Excel Windows
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $headers);

            foreach ($filtered as $index => $row) {
                if ($type === 'gps') {
                    fputcsv($file, [
                        $index + 1,
                        $row['latitude'] ?? '',
                        $row['longitude'] ?? '',
                        $row['satelit'] ?? '',
                        $row['hdop'] ?? '',
                        $row['mapsUrl'] ?? '',
                        $row['timestamp'] ?? ''
                    ]);
                } else {
                    $ax = $row['accX'] ?? 0;
                    $ay = $row['accY'] ?? 0;
                    $az = $row['accZ'] ?? 0;
                    $total = abs($ax) + abs($ay) + abs($az);
                    fputcsv($file, [
                        $index + 1,
                        $ax,
                        $ay,
                        $az,
                        $total,
                        $row['gerakan'] ?? ($total > 2.50 ? 'JATUH' : 'AMAN'),
                        $row['timestamp'] ?? ''
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
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
