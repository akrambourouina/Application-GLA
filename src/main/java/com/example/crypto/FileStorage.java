package com.example.crypto;

import java.io.FileWriter;
import java.io.IOException;

public class FileStorage {

	public void saveToFile(String data, String filename) {
		try (FileWriter fileWriter = new FileWriter(filename)) {
			fileWriter.write(data);
			System.out.println("Données sauvegardées dans le fichier : " + filename);
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
}
