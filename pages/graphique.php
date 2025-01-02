<?php
include('../config/database.php');

if (!isset($_GET['name'])) {
    die("Nom de cryptomonnaie non spécifié.");
}

$name = $_GET['name'];

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
    $dates[] = $row['date']; // Les dates doivent être au format ISO 8601 (c'est déjà le cas ici)
    $prices[] = $row['price_usd'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphique de Cryptomonnaie : <?php echo htmlspecialchars($name); ?></title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js Adapter pour la gestion des dates -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
    <style>
        /* Réduction de la taille du graphique */
        #chart-container {
            width: 600px; /* Largeur personnalisée */
            margin: 0 auto; /* Centrer horizontalement */
        }
        canvas {
            width: 100% !important; /* Le canvas s'adapte à la largeur du conteneur */
            height: auto !important; /* Conserve le ratio d'aspect */
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
    </main>
    <footer>
        <p>© 2024 CryptoApp</p>
    </footer>

    <script>
        // Les données récupérées depuis PHP
        const dates = <?php echo json_encode($dates); ?>;
        const prices = <?php echo json_encode($prices); ?>;

        const ctx = document.getElementById('cryptoChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates, // Les labels sont vos dates
                datasets: [{
                    label: 'Prix (USD)',
                    data: prices, // Les données sont vos prix
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    }
                },
                scales: {
                    x: {
                        type: 'time', // Indique un axe temporel
                        time: {
                            unit: 'day' // Affiche les dates avec l'unité jour
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
