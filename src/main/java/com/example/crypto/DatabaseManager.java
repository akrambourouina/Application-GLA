package com.example.crypto;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.logging.Level;
import java.util.logging.Logger;

public class DatabaseManager {
	private static final Logger logger = Logger.getLogger(DatabaseManager.class.getName());

	private static final String SERVER_NAME = "pedago01c.univ-avignon.fr";
	private static final String USERNAME = "uapv2300275";
	private static final String PASSWORD = "Py9rje";
	private static final String DB_NAME = "etd";
	private static Connection conn = null;

	public static Connection connect() {
		if (conn == null) {
			try {
				String url = "jdbc:postgresql://" + SERVER_NAME + ":5432/" + DB_NAME;
				conn = DriverManager.getConnection(url, USERNAME, PASSWORD);
				logger.log(Level.INFO, "Connexion réussie !");
			} catch (SQLException e) {
				logger.log(Level.SEVERE, "Erreur de connexion : " + e.getMessage(), e);
			}
		}
		return conn;
	}

	public static void disconnect() {
		try {
			if (conn != null && !conn.isClosed()) {
				conn.close();
				logger.log(Level.INFO, "Déconnexion réussie !");
			}
		} catch (SQLException e) {
			logger.log(Level.SEVERE, "Erreur lors de la déconnexion : " + e.getMessage(), e);
		}
	}
}
