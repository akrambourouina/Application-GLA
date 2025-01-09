import http from 'k6/http';
import { check, sleep } from 'k6';

// Configuration de la charge
export const options = {
  vus: 10,
  duration: '30s',
};

// URL de base (hébergement en ligne ou local)
const BASE_URL = 'https://pedago.univ-avignon.fr/~uapv2300275/crypto-app';

// Identifiants de test
const TEST_EMAIL = 'test@example.com';
const TEST_PASSWORD = 'pass123';

// Nom de cryptomonnaie à tester
const CRYPTO_NAME = 'Bitcoin';

export default function () {
  // 1) Connexion (POST)
  const loginPayload = `email=${encodeURIComponent(TEST_EMAIL)}&mot_de_passe=${encodeURIComponent(TEST_PASSWORD)}`;
  const loginHeaders = { 'Content-Type': 'application/x-www-form-urlencoded' };

  // Envoyer la requête POST pour se connecter
  const resLogin = http.post(`${BASE_URL}/pages/connexion.php`, loginPayload, {
    headers: loginHeaders,
  });

  check(resLogin, {
    'login: status 200 ou 302': (r) => r.status === 200 || r.status === 302,
  });
  sleep(1);

  // 2) Page d'accueil (accueil.php)
  const resAccueil = http.get(`${BASE_URL}/pages/accueil.php`);
  check(resAccueil, {
    'accueil: status 200': (r) => r.status === 200,
    'accueil: body non vide': (r) => r.body && r.body.length > 0,
  });
  sleep(1);

  // 3) Page analyse
  const resAnalyse = http.get(`${BASE_URL}/pages/analyse.php?name=${CRYPTO_NAME}`);
  check(resAnalyse, {
    'analyse: status 200': (r) => r.status === 200,
    'analyse: contient le mot Bitcoin': (r) => r.body && r.body.includes('Bitcoin'),
  });
  sleep(1);

  // 4) Page graphique
  const resGraphique = http.get(`${BASE_URL}/pages/graphique.php?name=${CRYPTO_NAME}`);
  // console.log(resGraphique.body); // Pour déboguer
  check(resGraphique, {
    'graphique: status 200': (r) => r.status === 200,
    'graphique: contient un canvas': (r) => r.body && r.body.includes('<canvas'),
  });
  sleep(1);
}
