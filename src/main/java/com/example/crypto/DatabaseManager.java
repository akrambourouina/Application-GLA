package com.example.crypto;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.sql.ResultSet;
import java.sql.Statement;

public class DatabaseManager {

	private static final String SERVER_NAME = "pedago01c.univ-avignon.fr";
	private static final String USERNAME = "uapv2200995";
	private static final String PASSWORD = "xm4Quj";
	private static final String DB_NAME = "etd";
	private static Connection conn = null;

	// Méthode pour établir une connexion à la base de données
	public static Connection connect() {
		if (conn == null) {
			try {
				// Utiliser l'URL de connexion PostgreSQL
				String url = "jdbc:postgresql://" + SERVER_NAME + ":5432/" + DB_NAME;
				conn = DriverManager.getConnection(url, USERNAME, PASSWORD);
				System.out.println("Connexion réussie !");
			} catch (SQLException e) {
				// Gérer l'erreur en cas d'échec de connexion
				e.printStackTrace();
				System.out.println("Erreur de connexion : " + e.getMessage());
			}
		}
		return conn;
	}

	// Méthode pour fermer la connexion
	public static void disconnect() {
		try {
			if (conn != null && !conn.isClosed()) {
				conn.close();
				System.out.println("Déconnexion réussie !");
			}
		} catch (SQLException e) {
			e.printStackTrace();
			System.out.println("Erreur lors de la déconnexion : " + e.getMessage());
		}
	}


}
