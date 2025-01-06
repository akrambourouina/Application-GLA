package com.example.crypto;

import org.junit.jupiter.api.Test;
import org.mockito.Mockito;

import static org.mockito.Mockito.*;

class CryptoCollectorAppTest {

	@Test
	void testAppRuns() {
		// Arrange
		CryptoCollector mockCollector = mock(CryptoCollector.class);
		doNothing().when(mockCollector).insertDataIntoDatabase(anyString());

		// Act
		CryptoCollectorApp.main(new String[]{});

		// Assert
		verify(mockCollector, never()).insertDataIntoDatabase(anyString()); // Scheduler not invoked in unit test
	}
}
