package com.ta.mpu.infrastructure.database;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Entity
@Table(name = "mpu_data")
@Data
@NoArgsConstructor
@AllArgsConstructor
public class MpuJpaEntity {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "acc_x")
    private Double accX;

    @Column(name = "acc_y")
    private Double accY;

    @Column(name = "acc_z")
    private Double accZ;

    @Column(name = "gerakan", length = 20)
    private String gerakan;

    @Column(nullable = false)
    private LocalDateTime timestamp;
}
