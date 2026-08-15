package com.ta.mpu.domain.repository;

import com.ta.mpu.domain.model.MpuData;

import java.util.List;
import java.util.Optional;

public interface MpuDataRepository {
    MpuData save(MpuData mpuData);
    Optional<MpuData> findLatest();
    List<MpuData> findAll();
    List<MpuData> findHistory(int limit);
}
