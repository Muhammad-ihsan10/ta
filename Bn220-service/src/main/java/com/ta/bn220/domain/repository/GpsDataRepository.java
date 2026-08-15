package com.ta.bn220.domain.repository;

import com.ta.bn220.domain.model.GpsData;

import java.util.List;
import java.util.Optional;

public interface GpsDataRepository {
    GpsData save(GpsData gpsData);
    Optional<GpsData> findLatest();
    List<GpsData> findAll();
    List<GpsData> findHistory(int limit);
}
