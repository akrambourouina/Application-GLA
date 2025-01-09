<?php
include('../config/database.php');

// Vérifier si le nom de la cryptomonnaie est passé en paramètre
if (!isset($_GET['name'])) {
    die("Cryptomonnaie non spécifiée.");
}

$cryptoName = $_GET['name'];

// Récupérer les détails de la cryptomonnaie à partir de son nom
$query = "SELECT * FROM crypto_data_minimal WHERE name = :name";
$stmt = $pdo->prepare($query);
$stmt->execute(['name' => $cryptoName]);
$crypto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$crypto) {
    die("Cryptomonnaie non trouvée.");
}

// Si le formulaire est soumis pour récupérer et insérer des données
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = strtotime($_POST['start_date']) * 1000; // Convertir en millisecondes
    $endDate = strtotime($_POST['end_date']) * 1000; // Convertir en millisecondes

    // Fonction pour récupérer les données de l'API
    function fetchCryptoData($start, $end, $cryptoName) {
        $url = "https://api.coincap.io/v2/assets/" . urlencode(strtolower($cryptoName)) . "/history?interval=d1&start=$start&end=$end";
        
        // Récupérer les données JSON de l'API
        $response = file_get_contents($url);
        if (!$response) {
            die("Erreur lors de la récupération des données.");
        }
        
        return json_decode($response, true);
    }

    // Récupérer les données de l'API
    $data = fetchCryptoData($startDate, $endDate, $cryptoName);

    // Insérer les données dans la base de données
    if (isset($data['data'])) {
        $insertQuery = "INSERT INTO crypto_prices (crypto_name, price_usd, time) VALUES (:crypto_name, :price_usd, :time)";
        $stmt = $pdo->prepare($insertQuery);

        foreach ($data['data'] as $entry) {
            $stmt->execute([
                'crypto_name' => $cryptoName,
                'price_usd' => $entry['priceUsd'],
                'time' => $entry['time']
            ]);
        }

      // echo "<p>Données insérées avec succès!</p>";
    } else {
        echo "<p>Aucune donnée trouvée pour la plage de dates sélectionnée.</p>";
    }
}

// Récupérer les données pour afficher le graphique
if (isset($startDate) && isset($endDate)) {
    $query = "SELECT price_usd, time FROM crypto_prices WHERE crypto_name = :name AND time BETWEEN :start AND :end ORDER BY time ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['name' => $cryptoName, 'start' => $startDate, 'end' => $endDate]);
    $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formater les données pour le graphique
    $timestamps = [];
    $pricesData = [];
    foreach ($prices as $entry) {
        $timestamps[] = date('Y-m-d', $entry['time'] / 1000); // Convertir en date format
        $pricesData[] = $entry['price_usd'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse Cryptomonnaie : <?php echo htmlspecialchars($cryptoName); ?></title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Style pour la page et le formulaire */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        header {
            background-color: #2c3e50;
            padding: 20px;
            color: #fff;
            text-align: center;
        }

        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 15px;
        }

        h1, h2 {
            text-align: center;
            font-weight: normal;
        }

        .form-container {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            max-width: 400px;
            margin: 20px auto;
        }

        .form-container label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
            color: #333;
        }

        .form-container input[type="date"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-container button[type="submit"] {
            display: block;
            margin: 0 auto;
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .form-container button[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Style pour le message d'insertion */
        p {
            text-align: center;
            color: #333;
        }

        /* Style pour le canvas */
        #cryptoChart {
            display: block;
            margin: 40px auto;
            max-width: 100%;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #888;
        }
    </style>
</head>
<body>
    <header>
        <h1>Analyse de la Cryptomonnaie : <?php echo htmlspecialchars($cryptoName); ?></h1>
    </header>

    <main>
        <div class="form-container">
            <h2>Choisissez votre plage de dates</h2>
            <form method="POST">
                <div>
                    <label for="start_date">Date de début :</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div>
                    <label for="end_date">Date de fin :</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
                <div>
                    <button type="submit">Récupérer et Insérer les Données</button>
                </div>
            </form>
        </div>

        <?php if (isset($timestamps) && isset($pricesData)): ?>
            <canvas id="cryptoChart" width="800" height="400"></canvas>
            <script>
                const ctx = document.getElementById('cryptoChart').getContext('2d');
                const cryptoChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?php echo json_encode($timestamps); ?>, // Dates
                        datasets: [{
                            label: 'Prix en USD',
                            data: <?php echo json_encode($pricesData); ?>, // Prix
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 2,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Prix (USD)'
                                }
                            }
                        }
                    }
                });
            </script>
        <?php endif; ?>
    </main>

    <footer>
        <p>© 2024 CryptoApp</p>
    </footer>
</body>
</html>

<?php
// VIDAGE DE LA TABLE après affichage
$pdo->exec("DELETE FROM crypto_prices;");
