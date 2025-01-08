package com.example.crypto;

import org.junit.jupiter.api.Test;

import java.sql.Connection;

import static org.junit.jupiter.api.Assertions.assertNotNull;
import static org.junit.jupiter.api.Assertions.assertDoesNotThrow;

class DatabaseManagerTest {

	/*@Test
	void testConnect() {
		// Act
		Connection connection = DatabaseManager.connect();

		// Assert
		assertNotNull(connection);
	}*/

	@Test
	void testDisconnect() {
		// Arrange
		DatabaseManager.connect();

		// Act & Assert
		assertDoesNotThrow(DatabaseManager::disconnect);
	}
}
