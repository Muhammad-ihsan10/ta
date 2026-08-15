package com.ta.bn220.infrastructure.controller;

import com.ta.bn220.application.service.GpsApplicationService;
import com.ta.bn220.domain.model.GpsData;
import lombok.Data;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/gps")
@CrossOrigin(origins = "*")
@RequiredArgsConstructor
public class GpsController {

    private final GpsApplicationService gpsApplicationService;

    @PostMapping
    public ResponseEntity<?> save(@RequestBody GpsRequest request) {
        try {
            GpsData saved = gpsApplicationService.saveGpsData(
                    request.getLatitude(),
                    request.getLongitude(),
                    request.getSatelit(),
                    request.getHdop()
            );
            return ResponseEntity.ok(toResponse(saved));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    @GetMapping("/latest")
    public ResponseEntity<?> latest() {
        return gpsApplicationService.getLatestGpsData()
                .map(data -> ResponseEntity.ok(toResponse(data)))
                .orElse(ResponseEntity.noContent().build());
    }

    @GetMapping
    public ResponseEntity<List<GpsResponse>> getAll() {
        List<GpsResponse> list = gpsApplicationService.getAllGpsData().stream()
                .map(this::toResponse)
                .collect(Collectors.toList());
        return ResponseEntity.ok(list);
    }

    @GetMapping("/history/{limit}")
    public ResponseEntity<List<GpsResponse>> getHistory(@PathVariable int limit) {
        List<GpsResponse> list = gpsApplicationService.getGpsHistory(limit).stream()
                .map(this::toResponse)
                .collect(Collectors.toList());
        return ResponseEntity.ok(list);
    }

    @GetMapping("/health")
    public ResponseEntity<?> health() {
        return ResponseEntity.ok(Map.of("status", "UP", "service", "bn220-service"));
    }

    @Data
    public static class GpsRequest {
        private Double latitude;
        private Double longitude;
        private Integer satelit;
        private Double hdop;
    }

    @Data
    public static class GpsResponse {
        private Long id;
        private Double latitude;
        private Double longitude;
        private Integer satelit;
        private Double hdop;
        private String mapsUrl;
        private LocalDateTime timestamp;
    }

    private GpsResponse toResponse(GpsData domain) {
        GpsResponse response = new GpsResponse();
        response.setId(domain.getId());
        response.setLatitude(domain.getCoordinates().getLatitude());
        response.setLongitude(domain.getCoordinates().getLongitude());
        response.setSatelit(domain.getSatelit());
        response.setHdop(domain.getHdop());
        response.setMapsUrl(domain.getMapsUrl());
        response.setTimestamp(domain.getTimestamp());
        return response;
    }
}
