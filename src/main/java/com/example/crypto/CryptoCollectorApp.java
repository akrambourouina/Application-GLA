package com.example.crypto;

import java.util.concurrent.Executors;
import java.util.concurrent.ScheduledExecutorService;
import java.util.concurrent.TimeUnit;

public class CryptoCollectorApp {

	public static void main(String[] args) {
		// Création du collecteur
		CryptoCollector collector = new CryptoCollector();

		// Création d'un ScheduledExecutorService
		ScheduledExecutorService scheduler = Executors.newScheduledThreadPool(1);

		// Tâche périodique de collecte des données toutes les 30 secondes
		scheduler.scheduleAtFixedRate(() -> {
			try {
				System.out.println("Lancement de la collecte des données...");
				// Collecter les données depuis l'API
				String jsonData = collector.collectAllData();

				// Insérer les données dans la base de données
				collector.insertDataIntoDatabase(jsonData);

			} catch (Exception e) {
				System.err.println("Erreur lors de la collecte ou l'insertion des données : " + e.getMessage());
				e.printStackTrace();
			}
		}, 0, 30, TimeUnit.SECONDS); // Délai initial : 0 seconde, Période : 30 secondes

		// Gestion propre à la fin (par exemple, lors de la fermeture de l'application)
		Runtime.getRuntime().addShutdownHook(new Thread(() -> {
			System.out.println("Arrêt de l'application...");
			scheduler.shutdown();
			try {
				if (!scheduler.awaitTermination(5, TimeUnit.SECONDS)) {
					System.err.println("Le scheduler n'a pas pu être arrêté proprement.");
				}
			} catch (InterruptedException e) {
				e.printStackTrace();
			}
		}));
	}
}
