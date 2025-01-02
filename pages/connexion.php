<?php
include('../config/database.php');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    $query = "SELECT * FROM utilisateurs WHERE email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
        $_SESSION['utilisateur_id'] = $utilisateur['id'];
        $_SESSION['nom'] = $utilisateur['nom'];
        header("Location: accueil.php");
        exit;
    } else {
        echo "Identifiants incorrects.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../assets/css/conx.css">
</head>
<body>
    <header>
        <h1>Connexion</h1>
    </header>
    <main>
        <form action="connexion.php" method="POST">
            <label for="email">Email :</label>
            <input type="email" name="email" id="email" required>
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" name="mot_de_passe" id="mot_de_passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </main>
</body>
</html>
