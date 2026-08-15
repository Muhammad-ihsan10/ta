package com.ta.notiftele.config;

import org.springframework.beans.factory.annotation.Value;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.reactive.function.client.WebClient;

/**
 * Konfigurasi WebClient untuk memanggil Telegram Bot API.
 * Menggunakan WebFlux WebClient agar non-blocking.
 */
@Configuration
public class TelegramConfig {

    @Value("${telegram.bot.token}")
    private String botToken;

    /**
     * Base URL Telegram Bot API dengan token sudah terpasang di path.
     */
    @Bean("telegramWebClient")
    public WebClient telegramWebClient() {
        return WebClient.builder()
                .baseUrl("https://api.telegram.org/bot" + botToken)
                .defaultHeader("Content-Type", "application/json")
                .build();
    }
}
