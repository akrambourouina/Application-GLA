package com.example.crypto;

public class Main {
	public static void main(String[] args) {
		// Récupérer les données de l'API
		CryptoCollector collector = new CryptoCollector();
		String jsonData = collector.collectData();
		collector.insertDataIntoDatabase(jsonData);

	}
}
