package com.example.crypto;

import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.assertNotNull;

class CryptoCollectorTest {

	@Test
	void testCollectData() {
		CryptoCollector collector = new CryptoCollector();
		String data = collector.collectData();
		assertNotNull(data, "Les données collectées ne doivent pas être nulles !");
	}
}

