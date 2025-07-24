<!doctype html>
<html lang="pt-br">
<!-- [Head] start -->

<!doctype html>
<html lang="pt-br" data-layout="vertical" data-topbar="dark" data-sidebar="light" data-sidebar-size="lg" data-sidebar-image="none">

<head>
  <title> <?= Kernel\Env::get('APP_NAME') ?> | Página não encontrada</title>
  <!-- [Meta] -->
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta
    name="description"
    content="Able Pro is trending dashboard template made using Bootstrap 5 design framework. Able Pro is available in Bootstrap, React, CodeIgniter, Angular,  and .net Technologies." />
  <meta
    name="keywords"
    content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard" />
  <meta name="author" content="Phoenixcoded" />

  <!-- [Favicon] icon -->
  <link rel="icon" href="/assets/images/favicon.svg" type="image/x-icon" />

  <link rel="stylesheet" href="/assets/css/plugins/style.css" />
  <!-- [Font] Family -->
  <link
    rel="stylesheet"
    href="/assets/fonts/inter/inter.css"
    id="main-font-link" />
  <!-- [phosphor Icons] https://phosphoricons.com/ -->
  <link rel="stylesheet" href="/assets/fonts/phosphor/duotone/style.css" />
  <!-- [Tabler Icons] https://tablericons.com -->
  <link rel="stylesheet" href="/assets/fonts/tabler-icons.min.css" />
  <!-- [Feather Icons] https://feathericons.com -->
  <link rel="stylesheet" href="/assets/fonts/feather.css" />
  <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
  <link rel="stylesheet" href="/assets/fonts/fontawesome.css" />
  <!-- [Material Icons] https://fonts.google.com/icons -->
  <link rel="stylesheet" href="/assets/fonts/material.css" />
  <!-- [Template CSS Files] -->
  <link
    rel="stylesheet"
    href="/assets/css/style.css"
    id="main-style-link" />
  <link rel="stylesheet" href="/assets/css/style-preset.css" />
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-layout="vertical" data-pc-direction="ltr" data-pc-theme_contrast="" data-pc-theme="<?= \Kernel\Cookie::get('theme') ?>">
  <!-- [ Pre-loader ] start -->
  <div class="page-loader">
    <div class="bar"></div>
  </div>
  <!-- [ Pre-loader ] End -->

  <!-- [ Main Content ] start -->
  <div class="maintenance-block">
    <div class="container">
      <div class="row">
        <div class="col-sm-12">
          <div class="card error-card">
            <div class="card-body">
              <div class="error-image-block">
                <script src="/js/lottie-player.js"></script>
                <lottie-player src="/animations/notfound-404.json" background="transparent" speed="1" style="width: 400px; height: 400px" direction="1" mode="normal" loop autoplay></lottie-player>
              </div>
              <div class="text-center">
                <h1 class="mt-5"><b>Página não encontrada</b></h1>
                <p class="mt-2 mb-4 text-muted">A página que você procura pode ter sido movida, removida<br />
                  renomeada ou nunca ter existido!</p>
                <a href="/dashboard" class="btn btn-primary mb-3">Voltar para o dashboard</a>
              </div>
            </div>
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
  <script src="/assets/js/pcoded.js"></script>
  <script src="/assets/js/plugins/feather.min.js"></script>
</body>
<!-- [Body] end -->

</html>