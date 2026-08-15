package com.ta.mpu.infrastructure.controller;

import com.ta.mpu.application.service.MpuApplicationService;
import com.ta.mpu.domain.model.MpuData;
import lombok.Data;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/mpu")
@CrossOrigin(origins = "*")
@RequiredArgsConstructor
public class MpuController {

    private final MpuApplicationService mpuApplicationService;

    @PostMapping
    public ResponseEntity<?> save(@RequestBody MpuRequest request) {
        try {
            MpuData saved = mpuApplicationService.saveMpuData(
                    request.getAccX(),
                    request.getAccY(),
                    request.getAccZ()
            );
            return ResponseEntity.ok(toResponse(saved));
        } catch (Exception e) {
            return ResponseEntity.badRequest().body(Map.of("error", e.getMessage()));
        }
    }

    @GetMapping("/latest")
    public ResponseEntity<?> latest() {
        return mpuApplicationService.getLatestMpuData()
                .map(data -> ResponseEntity.ok(toResponse(data)))
                .orElse(ResponseEntity.noContent().build());
    }

    @GetMapping
    public ResponseEntity<List<MpuResponse>> getAll() {
        List<MpuResponse> list = mpuApplicationService.getAllMpuData().stream()
                .map(this::toResponse)
                .collect(Collectors.toList());
        return ResponseEntity.ok(list);
    }

    @GetMapping("/history/{limit}")
    public ResponseEntity<List<MpuResponse>> getHistory(@PathVariable int limit) {
        List<MpuResponse> list = mpuApplicationService.getMpuHistory(limit).stream()
                .map(this::toResponse)
                .collect(Collectors.toList());
        return ResponseEntity.ok(list);
    }

    @GetMapping("/health")
    public ResponseEntity<?> health() {
        return ResponseEntity.ok(Map.of("status", "UP", "service", "mpu-service"));
    }

    @Data
    public static class MpuRequest {
        private Double accX;
        private Double accY;
        private Double accZ;
    }

    @Data
    public static class MpuResponse {
        private Long id;
        private Double accX;
        private Double accY;
        private Double accZ;
        private String gerakan;
        private LocalDateTime timestamp;
    }

    private MpuResponse toResponse(MpuData domain) {
        MpuResponse response = new MpuResponse();
        response.setId(domain.getId());
        response.setAccX(domain.getAcceleration().getAccX());
        response.setAccY(domain.getAcceleration().getAccY());
        response.setAccZ(domain.getAcceleration().getAccZ());
        response.setGerakan(domain.getMovementStatus().name());
        response.setTimestamp(domain.getTimestamp());
        return response;
    }
}
