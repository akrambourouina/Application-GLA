<?php
$host = 'pedago01c.univ-avignon.fr'; // L'adresse de votre serveur de base de données
$dbname = 'etd'; // Le nom de votre base de données
$username = 'uapv2300275'; // Votre nom d'utilisateur
$password = 'Py9rje'; // Votre mot de passe

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
