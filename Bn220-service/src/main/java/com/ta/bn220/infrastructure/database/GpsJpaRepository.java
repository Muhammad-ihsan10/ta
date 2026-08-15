package com.ta.bn220.infrastructure.database;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface GpsJpaRepository extends JpaRepository<GpsJpaEntity, Long> {
    Optional<GpsJpaEntity> findTopByOrderByTimestampDesc();
}
