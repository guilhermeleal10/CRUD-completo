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

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    voltarComMensagem("Usuario invalido.", "erro");
}

if (isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] === (int) $id) {
    voltarComMensagem("Voce nao pode excluir o usuario logado.", "erro");
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $stmt->close();
    voltarComMensagem("Usuario excluido com sucesso.", "sucesso");
}

$stmt->close();
voltarComMensagem("Usuario nao encontrado.", "erro");

?>
