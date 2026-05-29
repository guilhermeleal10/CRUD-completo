<?php
session_start();

require_once __DIR__ . "/conexao.php";

if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$mensagem = $_SESSION['mensagem'] ?? '';
$tipoMensagem = $_SESSION['tipoMensagem'] ?? '';

unset($_SESSION['mensagem'], $_SESSION['tipoMensagem']);

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function jsValor($valor): string
{
    return htmlspecialchars(
        json_encode($valor, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
        ENT_QUOTES,
        'UTF-8'
    );
}

$total = 0;
$resultTotal = $conn->query("SELECT COUNT(*) AS total FROM usuarios");

if ($resultTotal) {
    $total = (int) $resultTotal->fetch_assoc()['total'];
}

$totalMes = 0;
$resultTotalMes = $conn->query("
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE YEAR(criado_em) = YEAR(CURDATE())
      AND MONTH(criado_em) = MONTH(CURDATE())
");

if ($resultTotalMes) {
    $totalMes = (int) $resultTotalMes->fetch_assoc()['total'];
}

$nomeUsuarioLogado = $_SESSION['usuario_nome'] ?? 'Admin User';
$emailUsuarioLogado = $_SESSION['usuario_email'] ?? ($_SESSION['usuario'] ?? 'Administrador');

?>
<!DOCTYPE html>
<html class="light" lang="pt-br">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>AdminPro - Gerenciamento de Usuários</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "background": "#f6faff",
                        "on-primary": "#ffffff",
                        "primary-container": "#171a4a",
                        "on-secondary-container": "#745a00",
                        "secondary-fixed": "#ffdf93",
                        "error": "#ba1a1a",
                        "on-surface-variant": "#46464f",
                        "primary-fixed": "#e0e0ff",
                        "surface": "#f6faff",
                        "error-container": "#ffdad6",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e0e9f2",
                        "secondary-container": "#ffd259",
                        "on-surface": "#141d23",
                        "on-primary-container": "#8083b9",
                        "primary": "#000032",
                        "outline": "#777680",
                        "surface-variant": "#dbe4ed",
                        "on-secondary": "#ffffff",
                        "on-error-container": "#93000a",
                        "secondary": "#765b00",
                        "outline-variant": "#c7c5d0",
                        "surface-container": "#e6eff8",
                        "surface-container-low": "#ecf5fe",
                        "on-tertiary": "#ffffff",
                        "on-secondary-fixed": "#241a00",
                        "on-tertiary-container": "#b17b59",
                        "on-error": "#ffffff",
                        "on-background": "#141d23",
                        "surface-dim": "#d2dbe4",
                        "surface-container-highest": "#dbe4ed"
                    },
                    fontFamily: {
                        "headline-lg": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-sm": ["Inter"],
                        "body-md": ["Inter"],
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .modal-overlay {
            display: none;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .user-row:hover {
            background-color: rgba(230, 239, 248, 0.5);
        }
    </style>
</head>

<body class="bg-background font-body-md text-on-background">

    <!-- Sidebar -->
    <aside class="fixed h-screen w-[280px] left-0 top-0 hidden lg:flex flex-col bg-primary-container shadow-lg border-r border-outline-variant/20 z-50">
        <div class="flex flex-col h-full py-6">
            <div class="px-6 mb-8 flex items-center gap-3">
                <div class="w-10 h-10 bg-secondary-container rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-on-secondary-container" style="font-variation-settings:'FILL' 1;">shield</span>
                </div>
                <div>
                    <h1 class="font-bold text-lg text-on-primary">AdminPro</h1>
                    <p class="text-xs text-on-primary/60 uppercase tracking-widest">Enterprise Suite</p>
                </div>
            </div>
            <nav class="flex-1 space-y-1">
                <a class="flex items-center gap-3 px-6 py-3 text-on-primary/60 hover:bg-white/5 hover:text-on-primary transition-all" href="#">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>
                <a class="flex items-center gap-3 px-6 py-3 text-on-primary bg-white/10 border-l-4 border-secondary-container transition-all" href="#">
                    <span class="material-symbols-outlined">group</span>
                    <span>Users</span>
                </a>
                <a class="flex items-center gap-3 px-6 py-3 text-on-primary/60 hover:bg-white/5 hover:text-on-primary transition-all" href="#">
                    <span class="material-symbols-outlined">settings</span>
                    <span>Settings</span>
                </a>
            </nav>
            <div class="px-4 mt-auto">
                <div class="bg-white/5 rounded-xl p-4 mb-4">
                    <p class="text-xs text-on-primary/60 mb-2">System Status</p>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-secondary shadow-[0_0_8px_#edc14a]"></span>
                        <span class="text-sm font-medium text-on-primary">All Systems Operational</span>
                    </div>
                </div>
                <a href="logout.php"
                    class="w-full flex items-center gap-3 px-4 py-3 text-on-primary/60 hover:bg-white/5 hover:text-on-primary transition-all rounded-lg">

                    <span class="material-symbols-outlined">logout</span>

                    <span>Logout</span>

                </a>
            </div>
        </div>
    </aside>

    <!-- TopBar -->
    <header class="fixed top-0 right-0 w-full lg:w-[calc(100%-280px)] h-16 bg-surface border-b border-surface-variant shadow-sm z-40">
        <div class="flex justify-between items-center px-6 lg:px-8 h-full">
            <div class="flex items-center gap-4 flex-1">
                <div class="relative w-full max-w-md group">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
                    <input class="w-full pl-10 pr-4 py-2 bg-surface-container-low border border-outline-variant/30 rounded-lg focus:ring-2 focus:ring-secondary-container outline-none transition-all text-sm"
                        id="userSearch" placeholder="Buscar usuários..." type="text" />
                </div>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-4 border-r border-outline-variant/30 pr-6">
                    <button class="relative p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors">
                        <span class="material-symbols-outlined">notifications</span>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-error rounded-full"></span>
                    </button>
                    <button class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-full transition-colors">
                        <span class="material-symbols-outlined">help</span>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-bold text-primary"><?= e($nomeUsuarioLogado) ?></p>
                        <p class="text-xs text-on-surface-variant"><?= e($emailUsuarioLogado) ?></p>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-secondary-container bg-primary-container flex items-center justify-center">
                        <span class="material-symbols-outlined text-on-primary text-xl" style="font-variation-settings:'FILL' 1;">person</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main -->
    <main class="pt-24 pb-12 px-6 lg:px-10 ml-0 lg:ml-[280px] min-h-screen">

        <?php if ($mensagem): ?>
            <div class="mb-6 flex items-center gap-3 px-5 py-4 rounded-xl border font-medium text-sm
        <?= $tipoMensagem === 'sucesso'
                ? 'bg-green-50 border-green-200 text-green-800'
                : 'bg-error-container border-error text-error' ?>">
                <span class="material-symbols-outlined text-xl">
                    <?= $tipoMensagem === 'sucesso' ? 'check_circle' : 'error' ?>
                </span>
                <?= e($mensagem) ?>
            </div>
        <?php endif; ?>

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/20 shadow-sm flex items-center justify-between group">
                <div>
                    <p class="text-sm font-medium text-on-surface-variant mb-1">Total Usuários</p>
                    <h3 class="text-4xl font-black text-primary"><?= $totalMes ?></h3>
                    <div class="flex items-center gap-1 mt-2 text-secondary text-sm font-bold">
                        <span class="material-symbols-outlined text-sm">database</span>
                        <span>Registros no banco</span>
                    </div>
                </div>
                <div class="bg-surface-container p-4 rounded-full group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings:'FILL' 1;">group</span>
                </div>
            </div>
            <div class="bg-surface-container-lowest p-6 rounded-xl border border-outline-variant/20 shadow-sm flex items-center justify-between group">
                <div>
                    <p class="text-sm font-medium text-on-surface-variant mb-1">Novos Usuários (Mês)</p>
                    <h3 class="text-4xl font-black text-primary"><?= $total ?></h3>
                    <div class="flex items-center gap-1 mt-2 text-on-tertiary-container text-sm font-bold">
                        <span class="material-symbols-outlined text-sm">verified_user</span>
                        <span>Mês atual</span>
                    </div>
                </div>
                <div class="bg-secondary-container/20 p-4 rounded-full group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-secondary text-3xl" style="font-variation-settings:'FILL' 1;">person_add</span>
                </div>
            </div>
            <div class="bg-primary-container p-6 rounded-xl border border-primary shadow-lg flex items-center justify-between group">
                <div class="text-on-primary">
                    <p class="text-sm font-medium text-on-primary/60 mb-1">System Security</p>
                    <h3 class="text-4xl font-black">99.9%</h3>
                    <p class="text-xs mt-2 opacity-80">Encrypted Infrastructure</p>
                </div>
                <div class="bg-white/10 p-4 rounded-full group-hover:scale-110 transition-transform duration-300">
                    <span class="material-symbols-outlined text-on-primary text-3xl">verified</span>
                </div>
            </div>
        </div>

        <!-- Tabela -->
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant/20 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-outline-variant/10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-primary">Gerenciamento de Usuários</h2>
                    <p class="text-sm text-on-surface-variant">Visualize e gerencie todos os usuários registrados.</p>
                </div>
                <button onclick="abrirModalNovo()"
                    class="flex items-center gap-2 bg-secondary hover:bg-secondary/90 text-on-secondary-fixed font-bold px-6 py-2.5 rounded-lg shadow-md active:scale-95 transition-all">
                    <span class="material-symbols-outlined">add</span>
                    Novo Usuário
                </button>
            </div>

            <?php if ($total === 0): ?>
                <div class="flex flex-col items-center justify-center py-20 text-on-surface-variant gap-3">
                    <span class="material-symbols-outlined text-6xl opacity-30">group_off</span>
                    <p class="text-lg font-medium">Nenhum usuário encontrado.</p>
                    <button onclick="abrirModalNovo()" class="text-secondary font-bold hover:underline">Cadastrar o primeiro usuário</button>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="userTable">
                        <thead class="bg-surface-container-low">
                            <tr>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">ID</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Email</th>
                                <th class="px-6 py-4 text-xs font-bold text-on-surface-variant uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">

                            <?php

                            $sql = "SELECT id, nome, email FROM usuarios ORDER BY id DESC";
                            $result = $conn->query($sql);

                            while ($row = $result->fetch_assoc()) {
                                $idUsuario = (int) $row['id'];
                                $nomeUsuario = $row['nome'];
                                $emailUsuario = $row['email'];
                                $inicialUsuario = strtoupper(substr($nomeUsuario, 0, 1));

                            ?>

                                <tr class="user-row transition-colors">

                                    <td class="px-6 py-4 text-sm font-medium text-on-surface-variant">
                                        #<?php echo $idUsuario; ?>
                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-3">

                                            <div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center text-xs font-bold text-primary border border-outline-variant/30 shrink-0">

                                                <?php echo e($inicialUsuario); ?>

                                            </div>

                                            <span class="text-sm font-bold text-primary">

                                                <?php echo e($nomeUsuario); ?>

                                            </span>

                                        </div>

                                    </td>

                                    <td class="px-6 py-4 text-sm text-on-surface-variant">

                                        <?php echo e($emailUsuario); ?>

                                    </td>

                                    <td class="px-6 py-4">

                                        <div class="flex items-center gap-2">

                                            <button
                                                onclick="abrirModalEditar(
                    <?php echo $idUsuario; ?>,
                    <?php echo jsValor($nomeUsuario); ?>,
                    <?php echo jsValor($emailUsuario); ?>
                )"

                                                class="p-2 text-primary hover:bg-surface-container rounded-lg transition-colors">

                                                <span class="material-symbols-outlined text-[20px]">
                                                    edit
                                                </span>

                                            </button>

                                            <button
                                                onclick="confirmarExclusao(
                    <?php echo $idUsuario; ?>,
                    <?php echo jsValor($nomeUsuario); ?>
                )"

                                                class="p-2 text-error hover:bg-error-container/20 rounded-lg transition-colors">

                                                <span class="material-symbols-outlined text-[20px]">
                                                    delete
                                                </span>

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            <?php
                            }
                            ?>

                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="p-4 bg-surface-container-low flex justify-between items-center text-sm text-on-surface-variant border-t border-outline-variant/10">
                <p>Mostrando <span id="userCountDisplay"><?= $total ?></span> usuário<?= $total !== 1 ? 's' : '' ?></p>
                <div class="flex gap-2">
                    <button class="px-3 py-1 bg-surface-container rounded border border-outline-variant/20 hover:bg-surface-container-high transition-colors">Prev</button>
                    <button class="px-3 py-1 bg-secondary text-on-secondary font-bold rounded shadow-sm">1</button>
                    <button class="px-3 py-1 bg-surface-container rounded border border-outline-variant/20 hover:bg-surface-container-high transition-colors">Next</button>
                </div>
            </div>
        </div>
    </main>

    <!-- ===== MODAL NOVO USUÁRIO ===== -->
    <div class="modal-overlay fixed inset-0 z-[100] items-center justify-center p-4" id="modalNovo">
        <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-outline-variant/30">
            <div class="bg-primary-container p-6 text-on-primary flex justify-between items-center">
                <h3 class="text-xl font-bold">Adicionar Usuário</h3>
                <button onclick="fecharModal('modalNovo')" class="hover:bg-white/10 p-1 rounded-full transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form method="POST" action="cadastrar.php" class="p-6 space-y-4">
                <input type="hidden" name="acao" value="cadastrar" />
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">Nome Completo</label>
                    <input class="w-full px-4 py-2 rounded-lg border border-outline focus:ring-2 focus:ring-secondary-container focus:border-primary outline-none transition-all"
                        name="nome" type="text" required placeholder="Ex: João Silva" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">E-mail</label>
                    <input class="w-full px-4 py-2 rounded-lg border border-outline focus:ring-2 focus:ring-secondary-container focus:border-primary outline-none transition-all"
                        name="email" type="email" required placeholder="joao@empresa.com" />
                </div>
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">Senha</label>
                    <input class="w-full px-4 py-2 rounded-lg border border-outline focus:ring-2 focus:ring-secondary-container focus:border-primary outline-none transition-all"
                        name="senha" type="password" required placeholder="••••••••" />
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="fecharModal('modalNovo')"
                        class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface font-bold rounded-lg hover:bg-surface-variant transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-secondary text-on-secondary font-bold rounded-lg shadow-md hover:opacity-90 active:scale-[0.98] transition-all">
                        Salvar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL EDITAR USUÁRIO ===== -->
    <div class="modal-overlay fixed inset-0 z-[100] items-center justify-center p-4" id="modalEditar">
        <div class="bg-surface-container-lowest w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-outline-variant/30">
            <div class="bg-primary-container p-6 text-on-primary flex justify-between items-center">
                <h3 class="text-xl font-bold">Editar Usuário</h3>
                <button onclick="fecharModal('modalEditar')" class="hover:bg-white/10 p-1 rounded-full transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form method="POST" action="atualizar.php" class="p-6 space-y-4">
                <input type="hidden" name="acao" value="editar" />
                <input type="hidden" name="id" id="editId" />
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">Nome Completo</label>
                    <input class="w-full px-4 py-2 rounded-lg border border-outline focus:ring-2 focus:ring-secondary-container focus:border-primary outline-none transition-all"
                        name="nome" id="editNome" type="text" required />
                </div>
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">E-mail</label>
                    <input class="w-full px-4 py-2 rounded-lg border border-outline focus:ring-2 focus:ring-secondary-container focus:border-primary outline-none transition-all"
                        name="email" id="editEmail" type="email" required />
                </div>
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">
                        Nova Senha <span class="font-normal text-on-surface-variant/60">(deixe em branco para manter)</span>
                    </label>
                    <input class="w-full px-4 py-2 rounded-lg border border-outline focus:ring-2 focus:ring-secondary-container focus:border-primary outline-none transition-all"
                        name="senha" type="password" placeholder="••••••••" />
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="fecharModal('modalEditar')"
                        class="flex-1 px-4 py-2.5 bg-surface-container-high text-on-surface font-bold rounded-lg hover:bg-surface-variant transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2.5 bg-secondary text-on-secondary font-bold rounded-lg shadow-md hover:opacity-90 active:scale-[0.98] transition-all">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== MODAL CONFIRMAR EXCLUSÃO ===== -->
    <div class="modal-overlay fixed inset-0 z-[100] items-center justify-center p-4" id="modalExcluir">
        <div class="bg-surface-container-lowest w-full max-w-sm rounded-2xl shadow-2xl p-6 border border-outline-variant/30 text-center">
            <div class="w-16 h-16 bg-error-container/20 mx-auto rounded-full flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-error text-4xl">delete_forever</span>
            </div>
            <h3 class="text-xl font-bold text-primary mb-2">Excluir Registro?</h3>
            <p class="text-on-surface-variant text-sm mb-1">Você está prestes a excluir:</p>
            <p class="font-bold text-primary mb-4" id="excluirNome"></p>
            <p class="text-on-surface-variant text-xs mb-6">Esta ação é irreversível.</p>
            <div class="flex gap-3">
                <button onclick="fecharModal('modalExcluir')"
                    class="flex-1 px-4 py-2 bg-surface-container-high text-on-surface font-bold rounded-lg hover:bg-surface-variant transition-colors">
                    Cancelar
                </button>
                <a id="linkExcluir" href="deletar.php"
                    class="flex-1 px-4 py-2 bg-error text-on-primary font-bold rounded-lg shadow-md text-center hover:opacity-90 transition-all">
                    Sim, Excluir
                </a>
            </div>
        </div>
    </div>

    <script>
        // ==========================
        // ABRIR E FECHAR MODAIS
        // ==========================

        function abrirModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function fecharModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        // Fechar clicando fora do modal
        document.querySelectorAll('.modal-overlay').forEach(overlay => {

            overlay.addEventListener('click', function(e) {

                if (e.target === this) {
                    fecharModal(this.id);
                }

            });

        });

        // ==========================
        // MODAL NOVO USUÁRIO
        // ==========================

        function abrirModalNovo() {
            abrirModal('modalNovo');
        }

        // ==========================
        // MODAL EDITAR USUÁRIO
        // ==========================

        function abrirModalEditar(id, nome, email) {

            document.getElementById('editId').value = id;
            document.getElementById('editNome').value = nome;
            document.getElementById('editEmail').value = email;

            abrirModal('modalEditar');
        }

        // ==========================
        // MODAL EXCLUIR USUÁRIO
        // ==========================

        function confirmarExclusao(id, nome) {

            document.getElementById('excluirNome').innerText = nome;

            document.getElementById('linkExcluir').href =
                'deletar.php?id=' + id;

            abrirModal('modalExcluir');
        }

        // ==========================
        // BUSCA DE USUÁRIOS
        // ==========================

        document.getElementById('userSearch').addEventListener('input', function() {

            const termo = this.value.toLowerCase();

            const linhas = document.querySelectorAll('#userTable tbody tr');

            let quantidade = 0;

            linhas.forEach(function(linha) {

                const exibir = linha.innerText.toLowerCase().includes(termo);

                linha.style.display = exibir ? '' : 'none';

                if (exibir) {
                    quantidade++;
                }

            });

            document.getElementById('userCountDisplay').innerText = quantidade;

        });
    </script>
</body>

</html>
