package com.ta.bn220.domain.model;

import lombok.Getter;
import lombok.ToString;
import lombok.EqualsAndHashCode;

@Getter
@ToString
@EqualsAndHashCode
public class Coordinates {
    private final Double latitude;
    private final Double longitude;

    public Coordinates(Double latitude, Double longitude) {
        this.latitude = latitude != null ? latitude : 0.0;
        this.longitude = longitude != null ? longitude : 0.0;
    }

    public String generateGoogleMapsUrl() {
        return "https://www.google.com/maps?q=" + latitude + "," + longitude;
    }
}
