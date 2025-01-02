<?php
include('../config/database.php');

$query = "SELECT * FROM crypto_data_minimal";
$stmt = $pdo->query($query);
$cryptos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cryptomonnaies</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header>
        <h1>Tableau de Bord des Cryptomonnaies</h1>
    </header>

    <main>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    
                    <th>Symbol</th>
                    <th>Name</th>
                    <th>Market Cap (USD)</th>
                    <th>Price (USD)</th>
                    <th>Analyse</th>
                    <th>Graphique</th> <!-- Nouvelle colonne -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cryptos as $crypto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($crypto['id']); ?></td>
                        <td><?php echo htmlspecialchars($crypto['symbol']); ?></td>
                        <td><?php echo htmlspecialchars($crypto['name']); ?></td>
                        <td><?php echo number_format($crypto['market_cap_usd'], 2); ?></td>
                        <td><?php echo number_format($crypto['price_usd'], 2); ?></td>
                         <!-- Lien Analyse -->
                         <td>
    <a href="analyse.php?name=<?php echo urlencode($crypto['name']); ?>" 
       style="color: #3498db; text-decoration: none; font-weight: bold;">
        Voir Analyse
    </a>
</td>

            <!-- Lien Graphique -->
                        <td>
    <a href="graphique.php?name=<?php echo urlencode($crypto['name']); ?>" 
       style="color: #e74c3c; text-decoration: none; font-weight: bold;">
        Voir Graphique
    </a>
</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <footer>
        <p>© 2024 CryptoApp</p>
    </footer>

</body>
</html>
