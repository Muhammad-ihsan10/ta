package com.ta.notiftele.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

/**
 * Response DTO yang dikembalikan setelah proses pengiriman notifikasi.
 */
@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class NotifResponse {

    /** true jika notifikasi berhasil dikirim ke Telegram */
    private boolean success;

    /** Pesan deskriptif hasil proses */
    private String message;

    /** Waktu pengiriman dalam milidetik (epoch) */
    private long timestamp;
}
