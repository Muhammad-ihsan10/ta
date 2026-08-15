package com.ta.bn220.infrastructure.database;

import com.ta.bn220.domain.model.Coordinates;
import com.ta.bn220.domain.model.GpsData;
import com.ta.bn220.domain.repository.GpsDataRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Component;

import java.util.List;
import java.util.Optional;
import java.util.stream.Collectors;

@Component
@RequiredArgsConstructor
public class GpsDataRepositoryImpl implements GpsDataRepository {

    private final GpsJpaRepository jpaRepository;

    @Override
    public GpsData save(GpsData gpsData) {
        GpsJpaEntity entity = toJpaEntity(gpsData);
        GpsJpaEntity savedEntity = jpaRepository.save(entity);
        return toDomainEntity(savedEntity);
    }

    @Override
    public Optional<GpsData> findLatest() {
        return jpaRepository.findTopByOrderByTimestampDesc()
                .map(this::toDomainEntity);
    }

    @Override
    public List<GpsData> findAll() {
        return jpaRepository.findAll(Sort.by(Sort.Direction.DESC, "timestamp")).stream()
                .map(this::toDomainEntity)
                .collect(Collectors.toList());
    }

    @Override
    public List<GpsData> findHistory(int limit) {
        return jpaRepository.findAll(
                PageRequest.of(0, limit, Sort.by(Sort.Direction.DESC, "timestamp"))
        ).getContent().stream()
                .map(this::toDomainEntity)
                .collect(Collectors.toList());
    }

    private GpsJpaEntity toJpaEntity(GpsData domain) {
        GpsJpaEntity entity = new GpsJpaEntity();
        entity.setId(domain.getId());
        entity.setLatitude(domain.getCoordinates().getLatitude());
        entity.setLongitude(domain.getCoordinates().getLongitude());
        entity.setSatelit(domain.getSatelit());
        entity.setHdop(domain.getHdop());
        entity.setMapsUrl(domain.getMapsUrl());
        entity.setTimestamp(domain.getTimestamp());
        return entity;
    }

    private GpsData toDomainEntity(GpsJpaEntity jpa) {
        Coordinates coordinates = new Coordinates(jpa.getLatitude(), jpa.getLongitude());
        return new GpsData(
                jpa.getId(),
                coordinates,
                jpa.getSatelit(),
                jpa.getHdop(),
                jpa.getTimestamp()
        );
    }
}
