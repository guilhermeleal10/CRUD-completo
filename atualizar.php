<?php
session_start();

include("conexao.php");

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

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if (!$id || $nome === '' || $email === '') {
    voltarComMensagem("Preencha os campos obrigatorios.", "erro");
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    voltarComMensagem("Informe um e-mail valido.", "erro");
}

if ($senha !== '' && strlen($senha) < 6) {
    voltarComMensagem("A nova senha deve ter pelo menos 6 caracteres.", "erro");
}

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1");
$stmt->bind_param("si", $email, $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $stmt->close();
    voltarComMensagem("Ja existe outro usuario com esse e-mail.", "erro");
}

$stmt->close();

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $stmt->close();
    voltarComMensagem("Usuario nao encontrado.", "erro");
}

$stmt->close();

if ($senha !== '') {
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ? WHERE id = ?");
    $stmt->bind_param("sssi", $nome, $email, $senhaHash, $id);
} else {
    $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nome, $email, $id);
}

if ($stmt->execute()) {
    if (isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] === (int) $id) {
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_email'] = $email;
        $_SESSION['usuario'] = $email;
    }

    $stmt->close();
    voltarComMensagem("Usuario atualizado com sucesso.", "sucesso");
}

$stmt->close();
voltarComMensagem("Nao foi possivel atualizar o usuario.", "erro");

?>
