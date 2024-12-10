package com.example.crypto;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;

import org.json.JSONArray;
import org.json.JSONObject;

public class CryptoCollector {

	private static final String INSERT_QUERY = "INSERT INTO crypto_data_minimal "
			+ "(id, rank, symbol, name, market_cap_usd, price_usd) "
			+ "VALUES (?, ?, ?, ?, ?, ?)";

	public String collectData() {
		StringBuilder response = new StringBuilder();
		try {
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

		return response.toString(); // Retourne la réponse de l'API sous forme de String
	}

	public void insertDataIntoDatabase(String jsonData) {
		try {
			// Convertir la chaîne JSON en objet JSONObject
			JSONObject jsonObject = new JSONObject(jsonData);
			JSONArray data = jsonObject.getJSONArray("data");

			// Se connecter à la base de données
			Connection conn = DatabaseManager.connect();
			if (conn == null) {
				System.out.println("Erreur de connexion à la base de données.");
				return;
			}

			// Préparer la requête d'insertion
			try (PreparedStatement stmt = conn.prepareStatement(INSERT_QUERY)) {
				// Parcourir les données
				for (int i = 0; i < data.length(); i++) {
					JSONObject asset = data.getJSONObject(i);

					// Récupérer les valeurs
					String id = asset.getString("id");
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

					// Afficher les données à insérer
					System.out.println("Insérer : " + id + ", " + rank + ", " + symbol + ", " + name);

					// Insérer les données dans la base de données
					stmt.setString(1, id);
					stmt.setInt(2, rank);
					stmt.setString(3, symbol);
					stmt.setString(4, name);
					stmt.setDouble(5, marketCapUsd);
					stmt.setDouble(6, priceUsd);

					stmt.addBatch();
				}

				// Exécuter le batch
				stmt.executeBatch();
				System.out.println("Données insérées avec succès !");
			} catch (SQLException e) {
				e.printStackTrace();
				System.out.println("Erreur lors de l'exécution du batch : " + e.getMessage());
			} finally {
				DatabaseManager.disconnect();
			}

		} catch (Exception e) {
			e.printStackTrace();
			System.out.println("Erreur lors du traitement des données JSON : " + e.getMessage());
		}
	}
}
