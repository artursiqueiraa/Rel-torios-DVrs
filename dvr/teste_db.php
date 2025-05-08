<?php
$host = 'localhost';
$dbname = 'dvr';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexão com o banco de dados bem-sucedida!<br>";
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage() . "<br>";
}

echo "Tabelas no banco:<br>";
$result = $pdo->query("SHOW TABLES");
while ($row = $result->fetch(PDO::FETCH_NUM)) {
    echo $row[0] . "<br>";
}
?>