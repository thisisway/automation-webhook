<?php use Kernel\Session; ?>

<!doctype html>
<html lang="pt-br" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none">

<head>

    <meta charset="utf-8" />
    <title>Login - Sistema de Gestão IEADEME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="ANVT TECH">
    <!-- App favicon -->
    <link rel="shortcut icon" href="/images/favicon.ico">

    <!-- Layout config Js -->
    <script src="/assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="/assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="/assets/css/app.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="/assets/css/custom.css" rel="stylesheet" type="text/css" />

</head>

<body>

    <div class="auth-page-wrapper pt-5">
        <!-- auth page bg -->
        <div class="auth-one-bg-position auth-one-bg">
            <div class="bg-overlay"></div>
        </div>

        <!-- auth page content -->
        <div class="auth-page-content">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="text-center mt-sm-5 mb-4 text-white-50">
                            <img src="images\logo_horizontal_branca.svg"  class="img-fluid"> 
                        </div>
                    </div>
                </div>
                <!-- end row -->

                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6 col-xl-5">
                        <div class="card mt-4">

                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Bem vindo !</h5>
                                    <p class="text-muted">Faça login para continuar.</p>
                                </div>
                                <div class="p-2 mt-4">
                                    <?php if(Session::hasError('login')) { ?>
                                        <div class="alert alert-danger" role="alert">
                                                <?=Session::getError('login')?>
                                        </div>
                                    <?php } ?>
                                    <form action="/auth/login" method="POST">

                                        <div class="mb-3">
                                            <label for="username" class="form-label">Nome de usuário</label>
                                            <input type="text" class="form-control" id="username" name="username" placeholder="digite o nome de usuário">
                                        </div>

                                        <div class="mb-3">
                                            <div class="float-end">
                                                <a href="auth-pass-reset-basic.html" class="text-muted">Esqueceu a senha?</a>
                                            </div>
                                            <label class="form-label" for="password-input">Senha</label>
                                            <div class="position-relative auth-pass-inputgroup mb-3">
                                                <input type="password" name="password" class="form-control pe-5" placeholder="digite a senha" id="password-input">
                                                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            </div>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="remember" name="remember">
                                            <label class="form-check-label" for="remember">Lembre-se</label>
                                        </div>

                                        <div class="mt-4">
                                            <button class="btn btn-primary w-100" type="submit">Entrar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- end card body -->
                        </div>
                        <!-- end card -->

                    </div>
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->

        <!-- footer -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="text-center">
                            <p class="mb-0 text-muted">&copy;
                                Reobote Tecnologia e Pagamentos <br> &reg; <?= date('Y') ?>  Todos os direitos reservados. 
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->
    </div>
    <!-- end auth-page-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="/assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- password-addon init -->
    <script src="/assets/js/pages/password-addon.init.js"></script>
</body>

</html>