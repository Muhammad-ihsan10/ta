package com.ta.mpu.domain.model;

import lombok.Getter;
import lombok.ToString;

import java.time.LocalDateTime;

@Getter
@ToString
public class MpuData {
    private final Long id;
    private final Acceleration acceleration;
    private final MovementStatus movementStatus;
    private final LocalDateTime timestamp;

    public MpuData(Long id, Acceleration acceleration, LocalDateTime timestamp) {
        this.id = id;
        this.acceleration = acceleration != null ? acceleration : new Acceleration(0.0, 0.0, 0.0);
        this.movementStatus = this.acceleration.determineMovementStatus();
        this.timestamp = timestamp != null ? timestamp : LocalDateTime.now();
    }
}
