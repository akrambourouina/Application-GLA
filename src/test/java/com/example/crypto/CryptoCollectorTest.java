package com.example.crypto;

import org.junit.jupiter.api.Test;
import org.mockito.Mockito;

import java.sql.Connection;
import java.sql.PreparedStatement;

import static org.junit.jupiter.api.Assertions.assertNotNull;
import static org.mockito.Mockito.*;

class CryptoCollectorTest {

	@Test
	void testCollectAllData() {
		CryptoCollector collector = new CryptoCollector();

		// Tester que collectAllData renvoie un résultat non nul
		String result = collector.collectAllData();
		assertNotNull(result, "La méthode collectAllData doit retourner un résultat non nul.");
		System.out.println("Résultat collectAllData : " + result);
	}

	@Test
	void testInsertDataIntoDatabase() throws Exception {
		// JSON de test simulé
		String mockJsonData = "{\n" +
				"    \"data\": [\n" +
				"        {\n" +
				"            \"rank\": 1,\n" +
				"            \"symbol\": \"BTC\",\n" +
				"            \"name\": \"Bitcoin\",\n" +
				"            \"marketCapUsd\": 1000000000.0,\n" +
				"            \"priceUsd\": 50000.0\n" +
				"        },\n" +
				"        {\n" +
				"            \"rank\": 2,\n" +
				"            \"symbol\": \"ETH\",\n" +
				"            \"name\": \"Ethereum\",\n" +
				"            \"marketCapUsd\": 500000000.0,\n" +
				"            \"priceUsd\": 4000.0\n" +
				"        }\n" +
				"    ]\n" +
				"}";


		// Mock de la connexion à la base de données
		Connection mockConnection = mock(Connection.class);
		PreparedStatement mockStatement = mock(PreparedStatement.class);

		when(mockConnection.prepareStatement(anyString())).thenReturn(mockStatement);

		// Mock de DatabaseManager pour retourner la connexion simulée
		DatabaseManager databaseManagerMock = mock(DatabaseManager.class);
		when(DatabaseManager.connect()).thenReturn(mockConnection);

		// Appeler la méthode à tester
		CryptoCollector collector = new CryptoCollector();
		collector.insertDataIntoDatabase(mockJsonData);

		// Vérifier que la méthode a tenté d'insérer les données
		verify(mockStatement, times(2)).addBatch();
		verify(mockStatement, times(1)).executeBatch();
		System.out.println("Test réussi pour insertDataIntoDatabase.");
	}
}
