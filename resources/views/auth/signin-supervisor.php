<!doctype html>
<html lang="PT-BR">
<!-- [Head] start -->

<head>
    <title>Criar Conta - Sistema de Gestão IEADEME</title>
    <!-- [Meta] -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
    <meta name="author" content="Reobote Tecnologia e Pagamentos">
    <!-- [Favicon] icon -->
    <link rel="icon" href="/assets/images/favicon.svg" type="image/x-icon" />
    <!-- [Font] Family -->
    <link rel="stylesheet" href="/assets/fonts/inter/inter.css" id="main-font-link" />
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="/assets/css/style.css" id="main-style-link" />
    <link rel="stylesheet" href="/assets/css/style-preset.css" />

</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" data-pc-theme_contrast="true" data-pc-theme="<?= $theme ?>">
    <!-- [ Pre-loader ] start -->
    <div class="page-loader">
        <div class="bar"></div>
    </div>
    <!-- [ Pre-loader ] End -->

    <div class="auth-main">
        <div class="auth-wrapper v1">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body">
                        <?php if (Kernel\Session::hasError('signin')) { ?>
                            <div class="alert alert-danger" role="alert">
                                <?= Kernel\Session::getError('signin') ?>
                            </div>
                        <?php } ?>
                        <form action="/criar-conta-supervisor" method="POST">
                            <div class="row justify-content-center">
                                <div class="col-12 col-md-6">
                                    <div class="text-center mb-4 text-white-50">
                                        <?php if ($theme == 'dark'): ?>
                                            <img src="images\logo_horizontal_branca.svg" class="img-fluid">
                                        <?php else: ?>
                                            <img src="images\logo_horizontal_azul_escuro.svg" class="img-fluid">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <h4 class="text-center mb-4">Cadastro de Supervisores</h4>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome e Sobrenome" value="<?= hasFlash('nome') ?>" required />
                                <?php displayError('nome') ?>
                            </div>
                            <div class="mb-3">
                                <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?= hasFlash('email') ?>" required />
                                <?php displayError('email') ?>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" id="floatingInput1" placeholder="Senha" required />
                                <?php displayError('password') ?>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="confirm_password" class="form-control" id="floatingInput1" placeholder="Confirmar Senha" required />
                            </div>
                            <div class="mb-3">
                                <select name="congregacao" class="form-select" required>
                                    <option value="">Selecione a congregação</option>
                                    <?php selectOptions($congregacoes, hasFlash('congregacao'), 'id', 'nome') ?>
                                </select>
                                <?php displayError('congregacao') ?>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary"> Criar Conta </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
    <!-- Required Js -->
    <script src="/assets/js/plugins/popper.min.js"></script>
    <script src="/assets/js/plugins/simplebar.min.js"></script>
    <script src="/assets/js/plugins/bootstrap.min.js"></script>
    <script src="/assets/js/fonts/custom-font.js"></script>
	<script src="/assets/js/loading.js"></script>
    <script src="/assets/js/pcoded.js"></script>
</body>
<!-- [Body] end -->

</html>