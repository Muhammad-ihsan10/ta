package com.ta.notiftele.dto;

import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;

/**
 * Request DTO yang diterima dari client (Web-Frontend / service lain)
 * ketika mendeteksi pasien jatuh.
 */
@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class FallAlertRequest {

    /** Latitude lokasi pasien jatuh (nullable jika GPS belum terkunci) */
    private Double lat;

    /** Longitude lokasi pasien jatuh (nullable jika GPS belum terkunci) */
    private Double lng;

    /** URL Google Maps yang sudah di-generate oleh GPS service */
    private String mapsUrl;

    /** Total magnitude akselerasi dari MPU6050 */
    private Double totalAcc;
}
