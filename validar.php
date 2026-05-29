<?php
session_start();

require_once __DIR__ . "/conexao.php";

function voltarLogin(string $mensagem): void
{
    $_SESSION['mensagem_login'] = $mensagem;
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    voltarLogin("Informe e-mail e senha.");
}

$stmt = $conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

$senhaValida = false;

if ($usuario) {
    $senhaSalva = (string) $usuario['senha'];
    $senhaValida = password_verify($senha, $senhaSalva);

    if (!$senhaValida && hash_equals($senhaSalva, $senha)) {
        $senhaValida = true;
        $novoHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        $stmt->bind_param("si", $novoHash, $usuario['id']);
        $stmt->execute();
        $stmt->close();
    }
}

if (!$usuario || !$senhaValida) {
    voltarLogin("E-mail ou senha invalidos.");
}

session_regenerate_id(true);

$_SESSION['usuario_id'] = (int) $usuario['id'];
$_SESSION['usuario_nome'] = $usuario['nome'];
$_SESSION['usuario_email'] = $usuario['email'];
$_SESSION['usuario'] = $usuario['email'];

header("Location: dashboard.php");
exit;

?>
