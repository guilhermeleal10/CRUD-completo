<?php
session_start();

require_once __DIR__ . "/conexao.php";

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

function voltarComMensagem(string $mensagem, string $tipo): void
{
    $_SESSION['mensagem'] = $mensagem;
    $_SESSION['tipoMensagem'] = $tipo;
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    voltarComMensagem("Requisicao invalida.", "erro");
}

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($nome === '' || $email === '' || $senha === '') {
    voltarComMensagem("Preencha todos os campos.", "erro");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComMensagem("Informe um e-mail valido.", "erro");
}

if (strlen($senha) < 6) {
    voltarComMensagem("A senha deve ter pelo menos 6 caracteres.", "erro");
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $stmt->close();
    voltarComMensagem("Ja existe um usuario com esse e-mail.", "erro");
}

$stmt->close();

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nome, $email, $senhaHash);

if ($stmt->execute()) {
    $stmt->close();
    voltarComMensagem("Usuario cadastrado com sucesso.", "sucesso");
}

$stmt->close();
voltarComMensagem("Nao foi possivel cadastrar o usuario.", "erro");

?>
