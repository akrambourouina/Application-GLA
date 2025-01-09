<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include('../config/database.php');
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';
require 'mailer/Exception.php';

// Vérification de la session
if (!isset($_SESSION['utilisateur_id'])) {
    die("Veuillez vous connecter pour accéder à cette page.");
}

// Vérification du paramètre 'name'
if (!isset($_GET['name'])) {
    die("Nom de cryptomonnaie non spécifié.");
}

$name = $_GET['name'];
$utilisateur_id = $_SESSION['utilisateur_id'];

// ─────────────────────────────────────────────────────────────────────────────
// 1) Récupérer les données historiques
// ─────────────────────────────────────────────────────────────────────────────
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
    $dates[] = $row['date'];         // ex: "2025-01-15"
    $prices[] = $row['price_usd'];   // ex: 95000.123
}

// ─────────────────────────────────────────────────────────────────────────────
// 2) Calculer une prévision sur 3 jours (T+1, T+2, T+3) via moyenne mobile
// ─────────────────────────────────────────────────────────────────────────────

// A) Fonction de moyenne mobile
function movingAverageForecast(array $arrPrices, int $window = 3): float {
    $count = count($arrPrices);
    if ($count === 0) {
        return 0.0;
    }
    $actualWindow = min($window, $count);
    $subset = array_slice($arrPrices, -$actualWindow);
    return array_sum($subset) / $actualWindow;
}

// B) Ajouter 1 jour (à la dernière date)
function addOneDay(string $dateStr): string {
    $timestamp = strtotime($dateStr);
    $timestamp += 86400; // +1 jour (en secondes)
    return date('Y-m-d', $timestamp);
}

// On construit un "extendedDates" qui inclut 3 jours de plus
$extendedDates = $dates;
$lastDate = end($extendedDates);
for ($i = 1; $i <= 3; $i++) {
    $lastDate = addOneDay($lastDate);
    $extendedDates[] = $lastDate;
}

// On veut 2 datasets : historique + prévision
//  - historicPricesExtended : valeurs réelles, null sur T+1, T+2, T+3
//  - forecastPricesExtended : null pour la partie historique, valeurs sur T+1...T+3

$historicCount = count($dates);
$extendedCount = count($extendedDates);

// Dataset historique : même taille que $extendedDates,
// mais null pour les 3 dernières dates futures
$historicPricesExtended = [];
for ($i = 0; $i < $extendedCount; $i++) {
    if ($i < $historicCount) {
        $historicPricesExtended[] = $prices[$i];
    } else {
        $historicPricesExtended[] = null;
    }
}

// Dataset prévision : null pour la partie historique
// puis calcul T+1, T+2, T+3 par moyenne mobile
$forecastPricesExtended = array_fill(0, $extendedCount, null);
$working = $prices; // copie pour calcul

for ($i = $historicCount; $i < $extendedCount; $i++) {
    $val = movingAverageForecast($working, 3);
    $forecastPricesExtended[$i] = $val;
    $working[] = $val;
}

// ─────────────────────────────────────────────────────────────────────────────
// 3) Gérer la définition d'une alerte (logique d'alerte + envoi email)
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seuil'])) {
    $seuil = floatval($_POST['seuil']);
    $queryInsert = "
        INSERT INTO alertes (utilisateur_id, crypto_name, seuil)
        VALUES (:utilisateur_id, :crypto_name, :seuil)
    ";
    $stmtInsert = $pdo->prepare($queryInsert);
    $stmtInsert->execute([
        'utilisateur_id' => $utilisateur_id,
        'crypto_name'    => $name,
        'seuil'          => $seuil
    ]);
   // echo "<p>Alerte définie pour $name à $seuil USD.</p>";
}

// Vérifier si un seuil est atteint
$queryA = "
    SELECT seuil
    FROM alertes
    WHERE utilisateur_id = :utilisateur_id AND crypto_name = :crypto_name
";
$stmtA = $pdo->prepare($queryA);
$stmtA->execute([
    'utilisateur_id' => $utilisateur_id,
    'crypto_name'    => $name
]);
$alertes = $stmtA->fetchAll(PDO::FETCH_ASSOC);

// Dernier prix réel
$dernier_prix = end($prices);

foreach ($alertes as $alerte) {
    $seuil = $alerte['seuil'];
    if ($dernier_prix >= $seuil) {
        // Envoyer email via PHPMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'akrambourouina462@gmail.com'; // votre Gmail
            $mail->Password   = 'othqcieceyzyarlm';            // mot de passe/app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('akrambourouina462@gmail.com', 'CryptoApp');
            $mail->addAddress($_SESSION['email'], 'Utilisateur');

            $mail->isHTML(true);
            $mail->Subject = "Alerte Crypto : $name";
            $mail->Body    = "
                <p>Le seuil de <b>$seuil USD</b> a été atteint pour <b>$name</b>.</p>
                <p>Dernier prix : <b>$dernier_prix USD</b>.</p>
            ";
            $mail->AltBody = "Seuil $seuil atteint pour $name. Dernier prix: $dernier_prix USD";

            $mail->send();
           // echo "<p>Alerte email envoyée pour $name (Seuil : $seuil USD).</p>";
        } catch (Exception $e) {
            echo "<p>Erreur : l'envoi de l'email a échoué. Erreur: {$mail->ErrorInfo}</p>";
        }
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// 4) Affichage HTML + Chart.js (historique + prévisions 3 jours)
// ─────────────────────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Graphique de Cryptomonnaie : <?php echo htmlspecialchars($name); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        body {
            margin: 0; font-family: Arial, sans-serif;
        }
        header {
            background-color: #2c3e50; color: #fff;
            text-align: center; padding: 20px;
        }
        main {
            max-width: 800px; margin: 0 auto; padding: 20px;
        }
        #chart-container {
            width: 600px; margin: 0 auto;
        }
        canvas {
            width: 100% !important; height: auto !important;
        }
        section {
            margin-top: 30px; text-align: center;
        }
        h2 {
            color: #2c3e50; margin-bottom: 20px;
        }
        form {
            display: inline-block; background-color: #f9f9f9;
            border: 1px solid #ddd; padding: 20px; border-radius: 5px;
        }
        label {
            display: block; text-align: left; margin-bottom: 5px; font-weight: bold;
        }
        input[type="number"] {
            width: 100%; max-width: 200px; padding: 8px;
            margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;
        }
        button[type="submit"] {
            background-color: #3498db; color: #fff; border: none;
            border-radius: 4px; padding: 10px 20px;
            cursor: pointer; font-weight: bold;
        }
        button[type="submit"]:hover {
            background-color: #2980b9;
        }
        footer {
            text-align: center; padding: 20px; color: #888;
        }
        p {
            text-align: center; font-size: 16px; color: #333;
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
        // Tableau des dates étendues (historique + 3 dates futures)
        const extendedDates = <?php echo json_encode($extendedDates); ?>;
        
        // Dataset Historique (même longueur que extendedDates, null sur T+1..T+3)
        const historicPricesExtended = <?php echo json_encode($historicPricesExtended); ?>;
        
        // Dataset Prévision (null sur l'historique, valeurs sur T+1..T+3)
        const forecastPricesExtended = <?php echo json_encode($forecastPricesExtended); ?>;
        
        const ctx = document.getElementById('cryptoChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: extendedDates, // l'échelle X = historique + 3 jours
                datasets: [
                    {
                        label: 'Historique',
                        data: historicPricesExtended,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        spanGaps: true, // Autorise les "trous" (null)
                    },
                    {
                        label: 'Prévision (J+1 à J+3)',
                        data: forecastPricesExtended,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderDash: [5, 5],
                        fill: false,
                        borderWidth: 2,
                        spanGaps: true,
                    }
                ]
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

<?php
// (Optionnel) Vider la table alertes après affichage
 $pdo->exec("DELETE FROM alertes;");