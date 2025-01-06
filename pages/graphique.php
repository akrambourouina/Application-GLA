<?php
include('../config/database.php');
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';
require 'mailer/Exception.php';

if (!isset($_SESSION['utilisateur_id'])) {
    die("Veuillez vous connecter pour accéder à cette page.");
}

if (!isset($_GET['name'])) {
    die("Nom de cryptomonnaie non spécifié.");
}

$name = $_GET['name'];
$utilisateur_id = $_SESSION['utilisateur_id'];

// Récupérer les données pour le graphique
$query = "
    SELECT date, price_usd 
    FROM crypto_data_minimal 
    WHERE name = :name 
    ORDER BY date ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute(['name' => $name]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$data) {
    die("Aucune donnée trouvée pour ce nom de cryptomonnaie.");
}

// Préparer les données pour le graphique
$dates = [];
$prices = [];

foreach ($data as $row) {
    $dates[] = $row['date'];
    $prices[] = $row['price_usd'];
}

// Gérer la définition d'une alerte
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seuil'])) {
    $seuil = floatval($_POST['seuil']);
    $query = "
        INSERT INTO alertes (utilisateur_id, crypto_name, seuil) 
        VALUES (:utilisateur_id, :crypto_name, :seuil)
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'utilisateur_id' => $utilisateur_id,
        'crypto_name' => $name,
        'seuil' => $seuil
    ]);

    echo "<p>Alerte définie pour $name à $seuil USD.</p>";
}

// Vérifier si un seuil a été atteint
$query = "
    SELECT seuil 
    FROM alertes 
    WHERE utilisateur_id = :utilisateur_id AND crypto_name = :crypto_name
";
$stmt = $pdo->prepare($query);
$stmt->execute([
    'utilisateur_id' => $utilisateur_id,
    'crypto_name' => $name
]);
$alertes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($alertes as $alerte) {
    $seuil = $alerte['seuil'];
    $dernier_prix = end($prices);

    if ($dernier_prix >= $seuil) {
        try {
            // Configurer PHPMailer
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'akrambourouina462@gmail.com'; // Remplacez par votre adresse Gmail
            $mail->Password = 'othqcieceyzyarlm'; // Remplacez par votre mot de passe Gmail ou mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Destinataires
            $mail->setFrom('akrambourouina462@gmail.com', 'CryptoApp');
            $mail->addAddress($_SESSION['email'], 'Utilisateur');

            // Contenu du mail
            $mail->isHTML(true);
            $mail->Subject = "Alerte Crypto : $name";
            $mail->Body    = "<p>Le seuil de <b>$seuil USD</b> a été atteint pour <b>$name</b>.</p>
                              <p>Dernier prix : <b>$dernier_prix USD</b>.</p>";
            $mail->AltBody = "Le seuil de $seuil USD a été atteint pour $name. Dernier prix : $dernier_prix USD.";

            // Envoyer le mail
            $mail->send();
            echo "<p>Alerte email envoyée pour $name (Seuil : $seuil USD).</p>";
        } catch (Exception $e) {
            echo "<p>Erreur : l'envoi de l'e-mail a échoué. Erreur : {$mail->ErrorInfo}</p>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphique de Cryptomonnaie : <?php echo htmlspecialchars($name); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        #chart-container {
            width: 600px;
            margin: 0 auto;
        }
        canvas {
            width: 100% !important;
            height: auto !important;
        }
    </style>
</head>
<body>
    <header>
        <h1>Graphique de : <?php echo htmlspecialchars($name); ?></h1>
    </header>
    <main>
        <div id="chart-container">
            <canvas id="cryptoChart"></canvas>
        </div>

        <section>
            <h2>Définir une alerte</h2>
            <form action="" method="POST">
                <label for="seuil">Seuil (USD) :</label>
                <input type="number" step="0.01" name="seuil" id="seuil" required>
                <button type="submit">Définir</button>
            </form>
        </section>
    </main>
    <footer>
        <p>© 2024 CryptoApp</p>
    </footer>

    <script>
        const dates = <?php echo json_encode($dates); ?>;
        const prices = <?php echo json_encode($prices); ?>;

        const ctx = document.getElementById('cryptoChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Prix (USD)',
                    data: prices,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top' }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: { unit: 'day' },
                        title: { display: true, text: 'Date' }
                    },
                    y: {
                        title: { display: true, text: 'Prix (USD)' },
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
</body>
</html>
