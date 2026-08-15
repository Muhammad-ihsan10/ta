<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $chatId;
    private string $apiBase = 'https://api.telegram.org/bot';

    /**
     * Cooldown dalam detik agar notif tidak banjir
     * Notif JATUH hanya dikirim max 1x per COOLDOWN_SECONDS
     */
    private const COOLDOWN_SECONDS = 60;
    private const CACHE_KEY = 'telegram_fall_notif_sent';

    public function __construct()
    {
        $this->token  = env('TELEGRAM_BOT_TOKEN', '');
        $this->chatId = env('TELEGRAM_CHAT_ID', '');
    }

    /**
     * Kirim notifikasi jatuh ke Telegram.
     * Dilengkapi cooldown agar tidak spam.
     *
     * @param float|null $lat       Latitude lokasi jatuh
     * @param float|null $lng       Longitude lokasi jatuh
     * @param string     $mapsUrl   URL Google Maps
     * @param float      $totalAcc  Total akselerasi terdeteksi
     * @return array{sent: bool, reason: string}
     */
    public function sendFallAlert(
        ?float $lat,
        ?float $lng,
        string $mapsUrl = '',
        float  $totalAcc = 0.0
    ): array {
        // Validasi konfigurasi
        if (empty($this->token) || empty($this->chatId)) {
            return ['sent' => false, 'reason' => 'Token atau Chat ID belum dikonfigurasi di .env'];
        }

        // Cek cooldown – jika sudah kirim dalam COOLDOWN_SECONDS terakhir, skip
        if (Cache::has(self::CACHE_KEY)) {
            $remaining = Cache::get(self::CACHE_KEY . '_ttl', self::COOLDOWN_SECONDS);
            return [
                'sent'   => false,
                'reason' => "Cooldown aktif, tunggu {$remaining} detik lagi",
            ];
        }

        // Bangun pesan notifikasi
        $message = $this->buildFallMessage($lat, $lng, $mapsUrl, $totalAcc);

        try {
            $response = Http::timeout(8)->post(
                "{$this->apiBase}{$this->token}/sendMessage",
                [
                    'chat_id'                  => $this->chatId,
                    'text'                     => $message,
                    'parse_mode'               => 'HTML',
                    'disable_web_page_preview' => false,
                ]
            );

            if ($response->successful()) {
                // Set cooldown cache
                Cache::put(self::CACHE_KEY, true, self::COOLDOWN_SECONDS);
                Cache::put(self::CACHE_KEY . '_ttl', self::COOLDOWN_SECONDS, self::COOLDOWN_SECONDS);

                Log::info('[Telegram] Notifikasi jatuh terkirim', [
                    'lat'      => $lat,
                    'lng'      => $lng,
                    'totalAcc' => $totalAcc,
                ]);

                return ['sent' => true, 'reason' => 'Notifikasi berhasil dikirim'];
            }

            $errBody = $response->json();
            Log::warning('[Telegram] Gagal kirim notifikasi', ['response' => $errBody]);

            return [
                'sent'   => false,
                'reason' => 'Telegram API error: ' . ($errBody['description'] ?? 'Unknown'),
            ];

        } catch (\Exception $e) {
            Log::error('[Telegram] Exception saat kirim notifikasi', ['error' => $e->getMessage()]);
            return ['sent' => false, 'reason' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Ambil Chat ID pengirim pesan terakhir ke bot.
     * Berguna untuk setup pertama kali.
     */
    public function getUpdates(): array
    {
        if (empty($this->token)) {
            return ['error' => 'Token belum dikonfigurasi'];
        }

        try {
            $response = Http::timeout(5)->get(
                "{$this->apiBase}{$this->token}/getUpdates"
            );
            return $response->json() ?? [];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Bangun teks pesan notifikasi jatuh
     */
    private function buildFallMessage(
        ?float $lat,
        ?float $lng,
        string $mapsUrl,
        float  $totalAcc
    ): string {
        $now          = now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') . ' WIB';
        $accFormatted = number_format($totalAcc, 4);

        // Koordinat
        if ($lat && $lng) {
            $latStr  = number_format($lat, 6);
            $lngStr  = number_format($lng, 6);
            $lokasiLine = "📌 <b>Koordinat:</b> {$latStr}, {$lngStr}";

            if ($mapsUrl && $mapsUrl !== '#') {
                $mapsLine = "🗺️ <b>Lokasi:</b> <a href=\"{$mapsUrl}\">Buka Google Maps</a>";
            } else {
                $autoMaps = "https://www.google.com/maps?q={$lat},{$lng}";
                $mapsLine = "🗺️ <b>Lokasi:</b> <a href=\"{$autoMaps}\">Buka Google Maps</a>";
            }
        } else {
            $lokasiLine = "📌 <b>Koordinat:</b> Tidak tersedia (GPS belum terkunci)";
            $mapsLine   = "🗺️ <b>Lokasi:</b> –";
        }

        return <<<MSG
🚨 <b>PERINGATAN: PASIEN JATUH!</b> 🚨

⚠️ Sistem monitoring mendeteksi pasien dalam kondisi <b>JATUH</b>.

{$lokasiLine}
{$mapsLine}

📊 <b>Detail Sensor:</b>
   • Total Akselerasi: <code>{$accFormatted}</code> g
   • Threshold Jatuh: <code>2.5000</code> g
   • Sensor: MPU6050

🕐 <b>Waktu:</b> {$now}

<i>Segera periksa kondisi pasien!</i>
MSG;
    }
}
