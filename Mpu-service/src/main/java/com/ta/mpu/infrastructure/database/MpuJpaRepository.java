package com.ta.mpu.infrastructure.database;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

import java.util.Optional;

@Repository
public interface MpuJpaRepository extends JpaRepository<MpuJpaEntity, Long> {
    Optional<MpuJpaEntity> findTopByOrderByTimestampDesc();
}
