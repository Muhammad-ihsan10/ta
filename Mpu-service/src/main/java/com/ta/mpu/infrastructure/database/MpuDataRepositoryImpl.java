package com.ta.mpu.infrastructure.database;

import com.ta.mpu.domain.model.Acceleration;
import com.ta.mpu.domain.model.MpuData;
import com.ta.mpu.domain.model.MovementStatus;
import com.ta.mpu.domain.repository.MpuDataRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.PageRequest;
import org.springframework.data.domain.Sort;
import org.springframework.stereotype.Component;

import java.util.List;
import java.util.Optional;
import java.util.stream.Collectors;

@Component
@RequiredArgsConstructor
public class MpuDataRepositoryImpl implements MpuDataRepository {

    private final MpuJpaRepository jpaRepository;

    @Override
    public MpuData save(MpuData mpuData) {
        MpuJpaEntity entity = toJpaEntity(mpuData);
        MpuJpaEntity savedEntity = jpaRepository.save(entity);
        return toDomainEntity(savedEntity);
    }

    @Override
    public Optional<MpuData> findLatest() {
        return jpaRepository.findTopByOrderByTimestampDesc()
                .map(this::toDomainEntity);
    }

    @Override
    public List<MpuData> findAll() {
        return jpaRepository.findAll(Sort.by(Sort.Direction.DESC, "timestamp")).stream()
                .map(this::toDomainEntity)
                .collect(Collectors.toList());
    }

    @Override
    public List<MpuData> findHistory(int limit) {
        return jpaRepository.findAll(
                PageRequest.of(0, limit, Sort.by(Sort.Direction.DESC, "timestamp"))
        ).getContent().stream()
                .map(this::toDomainEntity)
                .collect(Collectors.toList());
    }

    private MpuJpaEntity toJpaEntity(MpuData domain) {
        MpuJpaEntity entity = new MpuJpaEntity();
        entity.setId(domain.getId());
        entity.setAccX(domain.getAcceleration().getAccX());
        entity.setAccY(domain.getAcceleration().getAccY());
        entity.setAccZ(domain.getAcceleration().getAccZ());
        entity.setGerakan(domain.getMovementStatus().name());
        entity.setTimestamp(domain.getTimestamp());
        return entity;
    }

    private MpuData toDomainEntity(MpuJpaEntity jpa) {
        Acceleration acceleration = new Acceleration(jpa.getAccX(), jpa.getAccY(), jpa.getAccZ());
        MovementStatus status = null;
        if (jpa.getGerakan() != null) {
            try {
                status = MovementStatus.valueOf(jpa.getGerakan().toUpperCase());
            } catch (IllegalArgumentException e) {
                // fallback
            }
        }
        return new MpuData(jpa.getId(), acceleration, status, jpa.getTimestamp());
    }
}
