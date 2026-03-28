<?php
$servername = "192.168.200.10"; // L'IP de ton DBserver
$username = "user_portfolio";    // L'utilisateur créé par ton playbook Ansible
$password = "password123";      // Ton mot de passe
$dbname = "portfolio_db";

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("<h1 style='color:red'>❌ Échec de la connexion : " . $conn->connect_error . "</h1>");
}
echo "<h1 style='color:green'>✅ Connexion à la base de données réussie !</h1>";
echo "<p>Serveur interrogé : " . $servername . "</p>";
$conn->close();
?>