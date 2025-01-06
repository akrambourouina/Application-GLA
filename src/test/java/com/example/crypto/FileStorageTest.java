package com.example.crypto;

import org.junit.jupiter.api.Test;

import java.io.File;
import java.nio.file.Files;
import java.nio.file.Path;

import static org.junit.jupiter.api.Assertions.assertTrue;

class FileStorageTest {

	@Test
	void testSaveToFile() throws Exception {
		// Arrange
		String data = "Test data";
		String filename = "test_file.txt";
		FileStorage fileStorage = new FileStorage();

		// Act
		fileStorage.saveToFile(data, filename);

		// Assert
		File file = new File(filename);
		assertTrue(file.exists());

		// Cleanup
		Files.deleteIfExists(Path.of(filename));
	}
}
