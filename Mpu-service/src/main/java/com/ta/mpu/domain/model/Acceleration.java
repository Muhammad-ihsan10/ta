package com.ta.mpu.domain.model;

import lombok.Getter;
import lombok.ToString;
import lombok.EqualsAndHashCode;

@Getter
@ToString
@EqualsAndHashCode
public class Acceleration {
    private final Double accX;
    private final Double accY;
    private final Double accZ;

    public Acceleration(Double accX, Double accY, Double accZ) {
        this.accX = accX != null ? accX : 0.0;
        this.accY = accY != null ? accY : 0.0;
        this.accZ = accZ != null ? accZ : 0.0;
    }

    public double calculateMagnitude() {
        return Math.abs(accX) + Math.abs(accY) + Math.abs(accZ);
    }

    public MovementStatus determineMovementStatus() {
        return calculateMagnitude() > 2.50 ? MovementStatus.JATUH : MovementStatus.AMAN;
    }
}
