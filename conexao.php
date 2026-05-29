<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "sistema_login";

mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Erro de conexao: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$dbSeguro = str_replace("`", "``", $db);

if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$dbSeguro` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die("Erro ao criar banco de dados: " . $conn->error);
}

if (!$conn->select_db($db)) {
    die("Erro ao selecionar banco de dados: " . $conn->error);
}

$sqlTabela = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(120) NOT NULL,
        email VARCHAR(180) NOT NULL,
        senha VARCHAR(255) NOT NULL,
        criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

if (!$conn->query($sqlTabela)) {
    die("Erro ao criar tabela de usuarios: " . $conn->error);
}

function colunaExiste(mysqli $conn, string $coluna): bool
{
    $colunaSegura = $conn->real_escape_string($coluna);
    $resultado = $conn->query("SHOW COLUMNS FROM usuarios LIKE '$colunaSegura'");

    return $resultado && $resultado->num_rows > 0;
}

if (!colunaExiste($conn, "criado_em")) {
    $conn->query("ALTER TABLE usuarios ADD criado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
}

if (!colunaExiste($conn, "atualizado_em")) {
    $conn->query("ALTER TABLE usuarios ADD atualizado_em TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
}

$conn->query("ALTER TABLE usuarios MODIFY nome VARCHAR(120) NOT NULL");
$conn->query("ALTER TABLE usuarios MODIFY email VARCHAR(180) NOT NULL");
$conn->query("ALTER TABLE usuarios MODIFY senha VARCHAR(255) NOT NULL");

$resultadoUsuarios = $conn->query("SELECT COUNT(*) AS total FROM usuarios");
$semUsuarios = $resultadoUsuarios && (int) $resultadoUsuarios->fetch_assoc()['total'] === 0;

if ($semUsuarios) {
    $nomePadrao = "Administrador";
    $emailPadrao = "admin@admin.com";
    $senhaPadrao = password_hash("123456", PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("sss", $nomePadrao, $emailPadrao, $senhaPadrao);
        $stmt->execute();
        $stmt->close();
    }
}

?>
