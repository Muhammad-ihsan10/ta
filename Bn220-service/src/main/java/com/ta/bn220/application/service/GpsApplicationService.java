package com.ta.bn220.application.service;

import com.ta.bn220.domain.model.Coordinates;
import com.ta.bn220.domain.model.GpsData;
import com.ta.bn220.domain.repository.GpsDataRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

@Service
@RequiredArgsConstructor
public class GpsApplicationService {

    private final GpsDataRepository repository;

    @Transactional
    public GpsData saveGpsData(Double latitude, Double longitude, Integer satelit, Double hdop) {
        Coordinates coordinates = new Coordinates(latitude, longitude);
        GpsData gpsData = new GpsData(null, coordinates, satelit, hdop, LocalDateTime.now());
        return repository.save(gpsData);
    }

    public Optional<GpsData> getLatestGpsData() {
        return repository.findLatest();
    }

    public List<GpsData> getAllGpsData() {
        return repository.findAll();
    }

    public List<GpsData> getGpsHistory(int limit) {
        return repository.findHistory(limit);
    }
}
