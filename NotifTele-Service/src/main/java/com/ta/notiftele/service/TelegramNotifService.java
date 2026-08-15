package com.ta.notiftele.service;

import com.ta.notiftele.dto.FallAlertRequest;
import com.ta.notiftele.dto.NotifResponse;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Qualifier;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import org.springframework.web.reactive.function.client.WebClient;
import reactor.core.publisher.Mono;

import java.time.LocalDateTime;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.util.Map;
import java.util.concurrent.atomic.AtomicBoolean;
import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

/**
 * Service utama untuk mengirim notifikasi Telegram.
 *
 * <p>Fitur:
 * <ul>
 *   <li>Anti-spam cooldown menggunakan AtomicBoolean (thread-safe)</li>
 *   <li>Pesan HTML dengan info lokasi + Google Maps link</li>
 *   <li>getChatId helper untuk setup awal</li>
 * </ul>
 */
@Service
@Slf4j
public class TelegramNotifService {

    private final WebClient telegramWebClient;
    private final String chatId;
    private final int cooldownSeconds;

    // Thread-safe flag untuk cooldown anti-spam
    private final AtomicBoolean cooldownActive = new AtomicBoolean(false);
    private final ScheduledExecutorService scheduler = Executors.newSingleThreadScheduledExecutor();

    private static final DateTimeFormatter FORMATTER =
            DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm:ss");

    public TelegramNotifService(
            @Qualifier("telegramWebClient") WebClient telegramWebClient,
            @Value("${telegram.bot.chat-id:}") String chatId,
            @Value("${telegram.notification.cooldown-seconds:60}") int cooldownSeconds) {
        this.telegramWebClient = telegramWebClient;
        this.chatId            = chatId;
        this.cooldownSeconds   = cooldownSeconds;
    }

    // ============================================================
    // PUBLIC: Kirim notifikasi jatuh
    // ============================================================

    /**
     * Kirim alert jatuh ke Telegram.
     * Akan ditolak jika cooldown masih aktif atau chat-id belum dikonfigurasi.
     *
     * @param request Data sensor + lokasi GPS saat pasien jatuh
     * @return NotifResponse berisi status pengiriman
     */
    public NotifResponse sendFallAlert(FallAlertRequest request) {
        // Validasi Chat ID
        if (chatId == null || chatId.isBlank()) {
            log.warn("[NotifTele] TELEGRAM_CHAT_ID belum dikonfigurasi");
            return buildResponse(false,
                    "TELEGRAM_CHAT_ID belum diisi. " +
                    "Kirim /start ke @lansiaNotifikasi_bot, lalu GET /api/notif/get-chat-id");
        }

        // Cek cooldown anti-spam
        if (cooldownActive.get()) {
            log.debug("[NotifTele] Cooldown aktif, notif dilewati");
            return buildResponse(false,
                    "Cooldown aktif (" + cooldownSeconds + " detik). Notif sebelumnya baru saja dikirim.");
        }

        // Bangun pesan HTML
        String message = buildFallMessage(request);

        // Kirim ke Telegram API (blocking — lebih mudah di-handle oleh REST endpoint)
        try {
            Map<String, Object> body = Map.of(
                    "chat_id",                  chatId,
                    "text",                     message,
                    "parse_mode",               "HTML",
                    "disable_web_page_preview", false
            );

            Map<?, ?> response = telegramWebClient.post()
                    .uri("/sendMessage")
                    .bodyValue(body)
                    .retrieve()
                    .bodyToMono(Map.class)
                    .block();

            boolean ok = Boolean.TRUE.equals(response != null ? response.get("ok") : false);

            if (ok) {
                // Aktifkan cooldown
                activateCooldown();
                log.info("[NotifTele] ✅ Notifikasi jatuh berhasil dikirim | lat={} lng={} acc={}",
                        request.getLat(), request.getLng(), request.getTotalAcc());
                return buildResponse(true, "Notifikasi jatuh berhasil dikirim ke Telegram");
            } else {
                String errDesc = response != null ? String.valueOf(response.get("description")) : "Unknown";
                log.error("[NotifTele] ❌ Telegram API error: {}", errDesc);
                return buildResponse(false, "Telegram API error: " + errDesc);
            }

        } catch (Exception e) {
            log.error("[NotifTele] ❌ Exception saat kirim notif: {}", e.getMessage());
            return buildResponse(false, "Exception: " + e.getMessage());
        }
    }

    /**
     * Ambil update terbaru dari bot untuk mendapatkan Chat ID.
     * Digunakan saat setup pertama kali.
     */
    public Map<?, ?> getUpdates() {
        try {
            return telegramWebClient.get()
                    .uri("/getUpdates")
                    .retrieve()
                    .bodyToMono(Map.class)
                    .block();
        } catch (Exception e) {
            log.error("[NotifTele] getUpdates error: {}", e.getMessage());
            return Map.of("ok", false, "description", e.getMessage());
        }
    }

    /**
     * Status service: konfigurasi aktif dan cooldown saat ini.
     */
    public Map<String, Object> getStatus() {
        return Map.of(
                "service",         "notiftele-service",
                "status",          "UP",
                "chatIdConfigured", chatId != null && !chatId.isBlank(),
                "cooldownActive",  cooldownActive.get(),
                "cooldownSeconds", cooldownSeconds,
                "botUsername",     "lansiaNotifikasi_bot"
        );
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================

    /**
     * Aktifkan cooldown selama {@code cooldownSeconds} detik.
     */
    private void activateCooldown() {
        cooldownActive.set(true);
        scheduler.schedule(() -> {
            cooldownActive.set(false);
            log.debug("[NotifTele] Cooldown selesai, siap kirim notif berikutnya");
        }, cooldownSeconds, TimeUnit.SECONDS);
    }

    /**
     * Bangun pesan HTML yang dikirim ke Telegram.
     */
    private String buildFallMessage(FallAlertRequest req) {
        String waktu = LocalDateTime.now(ZoneId.of("Asia/Jakarta")).format(FORMATTER) + " WIB";

        // Lokasi
        String lokasiLine;
        String mapsLine;

        if (req.getLat() != null && req.getLng() != null) {
            String latStr = String.format("%.6f", req.getLat());
            String lngStr = String.format("%.6f", req.getLng());
            lokasiLine = "📌 <b>Koordinat:</b> " + latStr + ", " + lngStr;

            String url = (req.getMapsUrl() != null && !req.getMapsUrl().isBlank() && !req.getMapsUrl().equals("#"))
                    ? req.getMapsUrl()
                    : "https://www.google.com/maps?q=" + req.getLat() + "," + req.getLng();
            mapsLine = "🗺️ <b>Lokasi:</b> <a href=\"" + url + "\">Buka Google Maps</a>";
        } else {
            lokasiLine = "📌 <b>Koordinat:</b> Tidak tersedia (GPS belum terkunci)";
            mapsLine   = "🗺️ <b>Lokasi:</b> –";
        }

        String accFormatted = req.getTotalAcc() != null
                ? String.format("%.4f", req.getTotalAcc())
                : "–";

        return """
                🚨 <b>PERINGATAN: PASIEN JATUH!</b> 🚨
                
                ⚠️ Sistem monitoring mendeteksi pasien dalam kondisi <b>JATUH</b>.
                
                """ + lokasiLine + "\n" + mapsLine + """
                
                
                📊 <b>Detail Sensor:</b>
                   • Total Akselerasi: <code>""" + accFormatted + """
                </code> g
                   • Threshold Jatuh: <code>2.5000</code> g
                   • Sensor: MPU6050
                
                🕐 <b>Waktu:</b> """ + waktu + """
                
                
                <i>Segera periksa kondisi pasien!</i>
                """;
    }

    private NotifResponse buildResponse(boolean success, String message) {
        return NotifResponse.builder()
                .success(success)
                .message(message)
                .timestamp(System.currentTimeMillis())
                .build();
    }
}
