<?php
include('../config/database.php');

if (!isset($_GET['name'])) {
    die("Nom de cryptomonnaie non spécifié.");
}

$name = $_GET['name'];

// Récupérer les données pour le graphique en fonction du nom
$query = "SELECT date, price_usd FROM crypto_data_minimal WHERE name = :name ORDER BY date";
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphique de Cryptomonnaie : <?php echo htmlspecialchars($name); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Graphique de : <?php echo htmlspecialchars($name); ?></h1>
    </header>
    <main>
        <canvas id="cryptoChart" width="800" height="400"></canvas>
    </main>
    <footer>
        <p>© 2024 CryptoApp</p>
    </footer>

    <script>
        const ctx = document.getElementById('cryptoChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: 'Prix (USD)',
                    data: <?php echo json_encode($prices); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        },
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Prix (USD)'
                        },
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
</body>
</html>
