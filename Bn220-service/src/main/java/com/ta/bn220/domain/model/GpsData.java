package com.ta.bn220.domain.model;

import lombok.Getter;
import lombok.ToString;

import java.time.LocalDateTime;

@Getter
@ToString
public class GpsData {
    private final Long id;
    private final Coordinates coordinates;
    private final Integer satelit;
    private final Double hdop;
    private final String mapsUrl;
    private final LocalDateTime timestamp;

    public GpsData(Long id, Coordinates coordinates, Integer satelit, Double hdop, LocalDateTime timestamp) {
        this.id = id;
        this.coordinates = coordinates != null ? coordinates : new Coordinates(0.0, 0.0);
        this.satelit = satelit != null ? satelit : 0;
        this.hdop = hdop != null ? hdop : 0.0;
        this.mapsUrl = this.coordinates.generateGoogleMapsUrl();
        this.timestamp = timestamp != null ? timestamp : LocalDateTime.now();
    }
}
