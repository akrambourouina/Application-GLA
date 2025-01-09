<?php
include('../config/database.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $email = htmlspecialchars($_POST['email']);
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);

    $query = "INSERT INTO utilisateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);

    // Essayez d'exécuter la requête
    if ($stmt->execute([$nom, $email, $mot_de_passe])) {
        // Si l'insertion réussit, redirigez vers accueil.php
        header("Location: connexion.php");
        exit;
    } else {
        // Si l'insertion échoue, affichez un message d'erreur
        echo "Erreur lors de l'inscription.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/insc.css">
    <title>Page d'Inscription</title>
</head>
<body>
    <header>
        Bienvenue sur notre plateforme
    </header>
    <main>
        <div class="form-container">
            <form class="styled-form" action="<?= $_SERVER['PHP_SELF']; ?>" method="POST">
                <h2>Inscription</h2>
                <div class="form-group">
                    <input type="text" id="nom" name="nom" placeholder=" " required>
                    <label for="nom">Nom d'utilisateur</label>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder=" " required>
                    <label for="email">Adresse Email</label>
                </div>
                <div class="form-group">
                    <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder=" " required>
                    <label for="mot_de_passe">Mot de passe</label>
                </div>
                <button type="submit" class="btn">S'inscrire</button>
                <a href="../pages/connexion.php" class="link">Vous avez déjà un compte ? Connectez-vous</a>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 VotreEntreprise. Tous droits réservés.</p>
    </footer>
</body>
</html>
