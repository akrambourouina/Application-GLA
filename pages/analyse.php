<?php
include('../config/database.php');

// Vérification et récupération de l'ID depuis l'URL
if (isset($_GET['id'])) {
    $cryptoId = htmlspecialchars($_GET['id']);

    // Récupération des données de la cryptomonnaie depuis la base
    $query = "SELECT * FROM crypto_data_minimal WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $cryptoId);
    $stmt->execute();
    $crypto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$crypto) {
        die("Données non trouvées pour cette cryptomonnaie.");
    }
} else {
    die("ID de la cryptomonnaie non spécifié.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse de <?php echo htmlspecialchars($crypto['name']); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <header>
        <h1>Analyse de <?php echo htmlspecialchars($crypto['name']); ?> (<?php echo htmlspecialchars($crypto['symbol']); ?>)</h1>
    </header>

    <main>
        <section>
            <h2>Informations Générales</h2>
            <ul>
                <li><strong>Nom :</strong> <?php echo htmlspecialchars($crypto['name']); ?></li>
                <li><strong>Symbole :</strong> <?php echo htmlspecialchars($crypto['symbol']); ?></li>
                <li><strong>Rang :</strong> <?php echo htmlspecialchars($crypto['rank']); ?></li>
                <li><strong>Market Cap (USD) :</strong> <?php echo number_format($crypto['market_cap_usd'], 2); ?></li>
                <li><strong>Prix (USD) :</strong> <?php echo number_format($crypto['price_usd'], 2); ?></li>
            </ul>
        </section>

        <section>
            <h2>Graphiques</h2>
            <canvas id="cryptoChart" width="800" height="400"></canvas>
        </section>
    </main>

    <footer>
        <p>© 2024 CryptoApp</p>
    </footer>

    <script>
        // Données pour les graphiques (exemple fictif)
        const labels = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin"];
        const data = {
            labels: labels,
            datasets: [{
                label: "Prix de <?php echo htmlspecialchars($crypto['name']); ?> (USD)",
                data: [<?php echo rand(30000, 50000) . ',' . rand(35000, 52000) . ',' . rand(34000, 53000) . ',' . rand(31000, 49000) . ',' . rand(30000, 47000) . ',' . rand(32000, 48000); ?>],
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderColor: 'rgba(41, 128, 185, 1)',
                borderWidth: 2
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        };

        // Initialisation du graphique
        const cryptoChart = new Chart(
            document.getElementById('cryptoChart'),
            config
        );
    </script>

</body>
</html>
