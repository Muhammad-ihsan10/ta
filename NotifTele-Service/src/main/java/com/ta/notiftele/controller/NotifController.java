package com.ta.notiftele.controller;

import com.ta.notiftele.dto.FallAlertRequest;
import com.ta.notiftele.dto.NotifResponse;
import com.ta.notiftele.service.TelegramNotifService;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

/**
 * REST Controller untuk NotifTele-Service.
 *
 * <p>Endpoint tersedia:
 * <ul>
 *   <li>POST /api/notif/fall        – kirim notifikasi jatuh</li>
 *   <li>GET  /api/notif/status      – status service + cooldown info</li>
 *   <li>GET  /api/notif/get-chat-id – helper setup: ambil chat_id dari bot</li>
 *   <li>GET  /api/notif/health      – health check</li>
 * </ul>
 */
@RestController
@RequestMapping("/api/notif")
@CrossOrigin(origins = "*")
@RequiredArgsConstructor
@Slf4j
public class NotifController {

    private final TelegramNotifService telegramNotifService;

    // ============================================================
    // POST /api/notif/fall
    // Body: { lat, lng, mapsUrl, totalAcc }
    // ============================================================

    /**
     * Endpoint utama: kirim alert jatuh ke Telegram.
     * Dipanggil oleh Web-Frontend (dashboard.blade.php) saat MPU
     * mendeteksi status JATUH.
     */
    @PostMapping("/fall")
    public ResponseEntity<NotifResponse> notifyFall(@RequestBody FallAlertRequest request) {
        log.info("[NotifTele] /fall dipanggil | lat={} lng={} acc={}",
                request.getLat(), request.getLng(), request.getTotalAcc());

        NotifResponse response = telegramNotifService.sendFallAlert(request);
        return ResponseEntity.ok(response);
    }

    // ============================================================
    // GET /api/notif/status
    // ============================================================

    /**
     * Status service: apakah chat_id sudah dikonfigurasi, cooldown aktif, dll.
     */
    @GetMapping("/status")
    public ResponseEntity<Map<String, Object>> status() {
        return ResponseEntity.ok(telegramNotifService.getStatus());
    }

    // ============================================================
    // GET /api/notif/get-chat-id
    // ============================================================

    /**
     * Helper setup awal: ambil Chat ID dari update terbaru bot.
     *
     * <p>Cara pakai:
     * <ol>
     *   <li>Kirim /start ke @lansiaNotifikasi_bot di Telegram</li>
     *   <li>Hit endpoint ini</li>
     *   <li>Salin chat_id ke environment variable TELEGRAM_CHAT_ID</li>
     * </ol>
     */
    @GetMapping("/get-chat-id")
    public ResponseEntity<Map<String, Object>> getChatId() {
        Map<?, ?> updates = telegramNotifService.getUpdates();

        Long chatId = null;
        String username = null;
        String firstName = null;

        // Parse chat_id dari update terbaru
        Object okVal = updates.get("ok");
        if (Boolean.TRUE.equals(okVal)) {
            Object resultObj = updates.get("result");
            if (resultObj instanceof List<?> results && !results.isEmpty()) {
                // Ambil update terbaru (terakhir di list)
                Object lastObj = results.get(results.size() - 1);
                if (lastObj instanceof Map<?, ?> lastUpdate) {
                    Map<?, ?> msg = null;
                    Object msgObj = lastUpdate.get("message");
                    if (msgObj instanceof Map<?, ?> m) {
                        msg = m;
                    } else {
                        Object cqObj = lastUpdate.get("callback_query");
                        if (cqObj instanceof Map<?, ?> cq) {
                            Object cqMsgObj = cq.get("message");
                            if (cqMsgObj instanceof Map<?, ?> cqMsg) {
                                msg = cqMsg;
                            }
                        }
                    }
                    if (msg != null) {
                        Object chatObj = msg.get("chat");
                        if (chatObj instanceof Map<?, ?> chat) {
                            Object idObj = chat.get("id");
                            if (idObj instanceof Number num) {
                                chatId    = num.longValue();
                            }
                            Object usernameObj  = chat.get("username");
                            Object firstNameObj = chat.get("first_name");
                            username  = usernameObj  instanceof String s ? s : null;
                            firstName = firstNameObj instanceof String s ? s : null;
                        }
                    }
                }
            }
        }

        String hint = chatId != null
                ? "✅ Salin chat_id ini ke env TELEGRAM_CHAT_ID: " + chatId
                : "⚠️ Belum ada pesan. Kirim /start ke @lansiaNotifikasi_bot terlebih dahulu, lalu coba lagi.";

        return ResponseEntity.ok(Map.of(
                "chat_id",    chatId != null ? chatId : "belum_tersedia",
                "username",   username  != null ? username  : "",
                "first_name", firstName != null ? firstName : "",
                "hint",       hint
        ));
    }

    // ============================================================
    // GET /api/notif/health
    // ============================================================

    @GetMapping("/health")
    public ResponseEntity<Map<String, String>> health() {
        return ResponseEntity.ok(Map.of(
                "status",  "UP",
                "service", "notiftele-service"
        ));
    }
}
