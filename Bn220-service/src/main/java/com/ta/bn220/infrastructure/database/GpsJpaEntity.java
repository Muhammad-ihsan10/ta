package com.ta.bn220.infrastructure.database;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Data;
import lombok.NoArgsConstructor;

import java.time.LocalDateTime;

@Entity
@Table(name = "gps_data")
@Data
@NoArgsConstructor
@AllArgsConstructor
public class GpsJpaEntity {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false)
    private Double latitude;

    @Column(nullable = false)
    private Double longitude;

    @Column
    private Integer satelit;

    @Column
    private Double hdop;

    @Column(name = "maps_url", length = 500)
    private String mapsUrl;

    @Column(nullable = false)
    private LocalDateTime timestamp;
}
