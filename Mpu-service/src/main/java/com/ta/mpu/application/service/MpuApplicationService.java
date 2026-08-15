package com.ta.mpu.application.service;

import com.ta.mpu.domain.model.Acceleration;
import com.ta.mpu.domain.model.MpuData;
import com.ta.mpu.domain.repository.MpuDataRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

@Service
@RequiredArgsConstructor
public class MpuApplicationService {

    private final MpuDataRepository repository;

    @Transactional
    public MpuData saveMpuData(Double accX, Double accY, Double accZ) {
        Acceleration acceleration = new Acceleration(accX, accY, accZ);
        MpuData mpuData = new MpuData(null, acceleration, LocalDateTime.now());
        return repository.save(mpuData);
    }

    public Optional<MpuData> getLatestMpuData() {
        return repository.findLatest();
    }

    public List<MpuData> getAllMpuData() {
        return repository.findAll();
    }

    public List<MpuData> getMpuHistory(int limit) {
        return repository.findHistory(limit);
    }
}
