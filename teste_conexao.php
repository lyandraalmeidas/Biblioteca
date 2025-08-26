<?php
$host = "localhost";
$db = "biblioteca";   // substitua pelo seu banco
$user = "root";       // usuário criado
$pass = "";       // senha do usuário

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
echo "Conexão bem-sucedida!";
?>
