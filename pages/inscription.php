<?php
include('config/database.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

    $query = "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$nom, $email, $mot_de_passe])) {
        header("Location: connexion.php");
        exit;
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Inscription</h1>
    </header>
    <main>
        <form action="inscription.php" method="POST">
            <label for="nom">Nom :</label>
            <input type="text" name="nom" id="nom" required>
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required>
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>
            <button type="submit">S'inscrire</button>
        </form>
    </main>
</body>
</html>
