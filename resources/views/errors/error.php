<?php
// resources/views/errors/error.php
$isDebug = true; // Você pode controlar isso através do seu arquivo de configuração
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro na Aplicação</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Cabeçalho do Erro -->
            <div class="bg-red-600 px-6 py-4">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <h1 class="text-white text-xl font-bold">Erro na Aplicação</h1>
                </div>
            </div>

            <!-- Corpo do Erro -->
            <div class="p-6">
                <!-- Mensagem Principal -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Detalhes do Erro</h2>
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <pre class="text-red-600 font-mono whitespace-pre-wrap"><?= htmlspecialchars($message) ?></pre>
                    </div>
                </div>

                <?php if ($isDebug): ?>
                    <!-- Stack Trace -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Stack Trace</h2>
                        <div class="bg-gray-50 rounded-md p-4 overflow-x-auto">
                            <pre class="text-sm font-mono text-gray-700"><?= htmlspecialchars($Exception->getTraceAsString()) ?></pre>
                        </div>
                    </div>

                    <!-- Informações do Request -->
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Informações da Requisição</h2>
                        <div class="bg-gray-50 rounded-md p-4">
                            <dl class="grid grid-cols-1 gap-2">
                                <div class="flex">
                                    <dt class="font-semibold w-32">Arquivo:</dt>
                                    <dd class="text-gray-600"><?= htmlspecialchars($Exception->getFile()) ?></dd>
                                </div>
                                <div class="flex">
                                    <dt class="font-semibold w-32">Linha:</dt>
                                    <dd class="text-gray-600"><?= $Exception->getLine() ?></dd>
                                </div>
                                <div class="flex">
                                    <dt class="font-semibold w-32">URI:</dt>
                                    <dd class="text-gray-600"><?= htmlspecialchars(Kernel\Server::getRequestURI()) ?></dd>
                                </div>
                                <div class="flex">
                                    <dt class="font-semibold w-32">Método:</dt>
                                    <dd class="text-gray-600"><?= htmlspecialchars(Kernel\Server::getRequestMethod()) ?></dd>
                                </div>
                                <div class="flex">
                                    <dt class="font-semibold w-32">Timestamp:</dt>
                                    <dd class="text-gray-600"><?= date('d/m/Y H:i:s') ?></dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Ações -->
                <div class="flex justify-between items-center">
                    <button onclick="window.history.back()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded">
                        Voltar
                    </button>
                    <button onclick="location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded">
                        Tentar Novamente
                    </button>
                </div>
            </div>

            <!-- Rodapé -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <p class="text-sm text-gray-600 text-center">
                    <?php if (!$isDebug): ?>
                        Se o problema persistir, entre em contato com o suporte técnico.
                    <?php else: ?>
                        Modo de depuração ativado. Não exiba estes detalhes em ambiente de produção.
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <?php if ($isDebug): ?>
    <script>
        console.error(<?= json_encode([
            'message' => $message,
            'file' => $Exception->getFile(),
            'line' => $Exception->getLine(),
            'trace' => $Exception->getTrace()
        ], JSON_PRETTY_PRINT) ?>);
    </script>
    <?php endif; ?>
</body>
</html>