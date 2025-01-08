package com.example.crypto;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.Timestamp;
import java.time.LocalDateTime;
import org.json.JSONArray;
import org.json.JSONObject;

public class CryptoCollector {

	private static final String INSERT_QUERY = "INSERT INTO crypto_data_minimal "
			+ "(rank, symbol, name, market_cap_usd, price_usd, date) "
			+ "VALUES (?, ?, ?, ?, ?, ?)";

	private Connection connection;

	// Constructeur par défaut utilisant DatabaseManager
	public CryptoCollector() {
		this.connection = DatabaseManager.connect();
	}

	// Constructeur pour les tests permettant d'injecter une connexion
	public CryptoCollector(Connection connection) {
		this.connection = connection;
	}

	// Récupérer les données pour tous les actifs
	public String collectAllData() {
		StringBuilder response = new StringBuilder();
		try {
			// URL pour récupérer la liste de tous les actifs
			String apiUrl = "https://api.coincap.io/v2/assets";
			URL url = new URL(apiUrl);
			HttpURLConnection connection = (HttpURLConnection) url.openConnection();
			connection.setRequestMethod("GET");
			connection.setRequestProperty("Accept", "application/json");

			BufferedReader in = new BufferedReader(new InputStreamReader(connection.getInputStream()));
			String line;
			while ((line = in.readLine()) != null) {
				response.append(line);
			}
			in.close();
		} catch (Exception e) {
			e.printStackTrace();
		}

		return response.toString(); // Retourne la réponse JSON de l'API
	}

	// Insertion des données dans la base de données
	public void insertDataIntoDatabase(String jsonData) {
		try {
			// Convertir la chaîne JSON en objet JSONObject
			JSONObject jsonObject = new JSONObject(jsonData);
			JSONArray data = jsonObject.getJSONArray("data");

			if (connection == null) {
				System.out.println("Erreur de connexion à la base de données.");
				return;
			}

			// Préparer la requête d'insertion
			try (PreparedStatement stmt = connection.prepareStatement(INSERT_QUERY)) {
				// Parcourir les données
				for (int i = 0; i < data.length(); i++) {
					JSONObject asset = data.getJSONObject(i);

					// Récupérer les valeurs
					int rank = asset.getInt("rank");
					String symbol = asset.getString("symbol");
					String name = asset.getString("name");
					double marketCapUsd = asset.optDouble("marketCapUsd", 0.0);
					double priceUsd = asset.optDouble("priceUsd", 0.0);

					// Limiter les valeurs pour éviter les dépassements
					if (marketCapUsd >= 1e12) {
						marketCapUsd = 999_999_999_999.99; // Limite maximale
					}
					if (priceUsd >= 1e6) {
						priceUsd = 999_999.99; // Limite maximale pour les prix
					}

					// Récupérer la date actuelle sous forme de Timestamp
					Timestamp currentDate = Timestamp.valueOf(LocalDateTime.now());

					// Insérer les données dans la base de données
					stmt.setInt(1, rank);
					stmt.setString(2, symbol);
					stmt.setString(3, name);
					stmt.setDouble(4, marketCapUsd);
					stmt.setDouble(5, priceUsd);
					stmt.setTimestamp(6, currentDate);

					stmt.addBatch();
				}

				stmt.executeBatch();
				System.out.println("Données insérées avec succès !");
			}
		} catch (Exception e) {
			e.printStackTrace();
			System.out.println("Erreur lors du traitement des données JSON : " + e.getMessage());
		}
	}
}
