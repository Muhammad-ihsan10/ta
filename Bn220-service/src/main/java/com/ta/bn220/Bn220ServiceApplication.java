package com.ta.bn220;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;
import org.springframework.cloud.client.discovery.EnableDiscoveryClient;

@SpringBootApplication
@EnableDiscoveryClient
public class Bn220ServiceApplication {
    public static void main(String[] args) {
        SpringApplication.run(Bn220ServiceApplication.class, args);
    }
}
