<!DOCTYPE html>
<html lang="pt-br">
<!-- [Head] start -->

<head>
	<title> Área de gestão - Sistema de Gestão IEADEME</title>
	<!-- [Meta] -->
	<meta charset="utf-8" />
	<meta
		name="viewport"
		content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui" />
	<!-- [Favicon] icon -->
	<link rel="icon" href="/images/logo_horizontal_azul_escuro.svg" type="image/x-icon" />
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
	<script src="<?= assets('/js/jquery-3.5.1.js') ?>"></script>
	<link rel="stylesheet" href="/assets/css/style-preset.css" />

	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body
	data-pc-preset="preset-1"
	data-pc-sidebar-caption="true"
	data-pc-layout="vertical"
	data-pc-direction="ltr"
	data-pc-theme_contrast=""
	data-pc-theme="<?= (new Kernel\Redis)->get('theme_user_' . \Kernel\Session::get('user_id')) ?? 'light' ?>">
	<!-- [ Pre-loader ] start -->
	<div class="page-loader">
		<div class="bar"></div>
	</div>
	<!-- [ Pre-loader ] End -->
	<!-- [ Sidebar Menu ] start -->
	<nav class="pc-sidebar">
		<div class="navbar-wrapper">
			<div class="m-header">
				<a href="/dashboard" class="b-brand text-primary">
					<!-- ========   Change your logo from here   ============ -->
					<?php if ((new Kernel\Redis)->get('theme_user_' . \Kernel\Session::get('user_id')) == 'dark') { ?>
						<img
							src="/images/logo_branca.svg"
							class="img-fluid logo-lg"
							alt="logo" />
					<?php } else { ?>
						<img
							src="/images/logo_azul_escuro.svg"
							class="img-fluid logo-lg"
							alt="logo" />
					<?php } ?>
				</a>
			</div>
			<div class="navbar-content">
				<div class="card pc-user-card">
					<div class="card-body">
						<div class="d-flex align-items-center">
							<div class="flex-shrink-0">
								<img
									src="<?= \Kernel\Session::get('foto_perfil') ?>"
									alt="user-image"
									class="user-avtar wid-45 rounded-circle" />
							</div>
							<div class="flex-grow-1 ms-3 me-2">
								<h6 class="mb-0"><?= \Kernel\Session::get('name') ?></h6>
								<small> <?= \Kernel\Session::get('perfil') ?> </small>
							</div>
							<a
								class="btn btn-icon btn-link-secondary avtar"
								data-bs-toggle="collapse"
								href="#pc_sidebar_userlink">
								<svg class="pc-icon">
									<use xlink:href="#custom-sort-outline"></use>
								</svg>
							</a>
						</div>
						<div class="collapse pc-user-links" id="pc_sidebar_userlink">
							<div class="pt-3">
								<a href="/configuracoes/meu-perfil">
									<i class="ti ti-user"></i>
									<span>Minha conta</span>
								</a>
								<?php if (Kernel\Session::get('perfil') == 'Administrador') { ?>
									<a href="/configuracoes">
										<i class="ti ti-settings"></i>
										<span>Configurações</span>
									</a>
								<?php } ?>
								<a href="/auth/logoff">
									<i class="ti ti-power"></i>
									<span>Sair</span>
								</a>
							</div>
						</div>
					</div>
				</div>

				<ul class="pc-navbar">
					<li class="pc-item pc-caption">
						<label>Paineis</label>
					</li>
					<li class="pc-item pc-hasmenu">
						<a href="/dashboard" class="pc-link">
							<span class="pc-micon">
								<svg class="pc-icon">
									<use xlink:href="#custom-status-up"></use>
								</svg>
							</span>
							<span class="pc-mtext">Dashboard</span>
							<span class="pc-arrow"><i data-feather="chevron-right"></i></span>
						</a>
						<ul class="pc-submenu">
							<li class="pc-item">
								<a class="pc-link" href="/dashboard"> Ordens de serviço </a>
							</li>
							<!-- <li class="pc-item">
								<a class="pc-link" href="/dashboard/financeiro"> Financeiro </a>
							</li> -->
					</li>
				</ul>

				<li class="pc-item pc-caption">
					<label> Operacional </label>
					<svg class="pc-icon">
						<use xlink:href="#custom-element-plus"></use>
					</svg>
				</li>
				<li class="pc-item">
					<a href="/ordem-servico" class="pc-link">
						<span class="pc-micon">
							<svg class="pc-icon">
								<use xlink:href="#custom-note-1"></use>
							</svg>
						</span>
						<span class="pc-mtext"> Ordens de Serviço </span></a>
				</li>
				<?php if (hasPermissionOrPerfil('congregacoes.listar', 'Administrador')): ?>
					<li class="pc-item">
						<a href="/congregacoes" class="pc-link">
							<span class="pc-micon">
								<svg class="pc-icon">
									<use xlink:href="#custom-layer"></use>
								</svg>
							</span>
							<span class="pc-mtext"> Congregações </span></a>
					</li>
				<?php endif; ?>
				<!-- <?php if (hasPermissionOrPerfil('membros.listar', ['Administrador', 'Supervisor', 'Secretario'])): ?>
					<li class="pc-item">
						<a href="/membros" class="pc-link">
							<span class="pc-micon">

								<svg class="pc-icon">
									<use xlink:href="#custom-user-square"></use>
								</svg>
							</span>
							<span class="pc-mtext"> Membros </span></a>
					</li>
				<?php endif; ?> -->
				<li class="pc-item">
					<a href="/calendario" class="pc-link">

						<span class="pc-micon">
							<svg class="pc-icon">
								<use xlink:href="#custom-calendar-1"></use>
							</svg>
						</span>
						<span class="pc-mtext"> Calendário </span></a>
				</li>
				<?php if (hasPermissionOrPerfil('usuarios.listar', ['Administrador', 'Supervisor', 'Secretario'])): ?>
					<li class="pc-item pc-caption">
						<label> Gerenciar </label>

						<svg class="pc-icon">
							<use xlink:href="#custom-element-plus"></use>
						</svg>
					</li>
					<?php if (hasPermissionOrPerfil('usuarios.listar', ['Administrador', 'Supervisor', 'Secretario'])): ?>
						<li class="pc-item">
							<a href="/configuracoes/usuarios" class="pc-link">
								<span class="pc-micon">

									<svg class="pc-icon">
										<use xlink:href="#custom-profile-2user-outline"></use>
									</svg>
								</span>
								<span class="pc-mtext"> Usuários </span></a>
						</li>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

	</nav>
	<!-- [ Sidebar Menu ] end -->
	<!-- [ Header Topbar ] start -->
	<header class="pc-header">

		<div class="header-wrapper">
			<!-- [Mobile Media Block] start -->
			<div class="me-auto pc-mob-drp">
				<ul class="list-unstyled">
					<!-- ======= Menu collapse Icon ===== -->
					<li class="pc-h-item pc-sidebar-collapse">
						<a href="#" class="pc-head-link ms-0" id="sidebar-hide">
							<i class="ti ti-menu-2"></i>
						</a>
					</li>
					<li class="pc-h-item pc-sidebar-popup">
						<a href="#" class="pc-head-link ms-0" id="mobile-collapse">
							<i class="ti ti-menu-2"></i>
						</a>
					</li>
					<li class="pc-h-item d-none d-md-inline-flex">
						<form class="form-search">
							<i class="search-icon">
								<svg class="pc-icon">
									<use xlink:href="#custom-search-normal-1"></use>
								</svg>
							</i>
							<input
								type="search"
								class="form-control"
								placeholder="Ctrl + K" />
						</form>
					</li>
				</ul>
			</div>
			<!-- [Mobile Media Block end] -->
			<div class="ms-auto">
				<ul class="list-unstyled">
					<!-- Novo select de congregações -->
					<li class="dropdown pc-h-item">
						<select class="form-select" style="width: 200px; margin-right: 10px;" onchange="trocarCongregacao(this.value)">
							<option value="0">Selecione uma congregação</option>
							<?php
							$congregacoes = (Kernel\Session::get('perfil_id') == 1) ?
								(new \App\Models\Congregacoes)->all() : (new \App\Models\Congregacoes)
								->select('congregacoes.*')
								->join('congregacao_membros', 'congregacoes.id', 'congregacao_membros.congregacao_id')
								->where('congregacao_membros.usuario_id', Kernel\Session::get('user_id'))->get();

							foreach ($congregacoes as $congregacao): ?>
								<option value="<?= $congregacao->id ?>" <?= Kernel\Cookie::get('congregacao_id') == $congregacao->id ? 'selected' : '' ?>><?= $congregacao->nome ?></option>
							<?php endforeach; ?>
						</select>
					</li>
					<li class="dropdown pc-h-item">
						<a
							class="pc-head-link dropdown-toggle arrow-none me-0"
							data-bs-toggle="dropdown"
							href="#"
							role="button"
							aria-haspopup="false"
							aria-expanded="false">
							<svg class="pc-icon">
								<use xlink:href="#custom-sun-1"></use>
							</svg>
						</a>
						<div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
							<a
								href="#!"
								class="dropdown-item"
								onclick="layout_change('dark')">
								<svg class="pc-icon">
									<use xlink:href="#custom-moon"></use>
								</svg>
								<span>Dark</span>
							</a>
							<a
								href="#!"
								class="dropdown-item"
								onclick="layout_change('light')">
								<svg class="pc-icon">
									<use xlink:href="#custom-sun-1"></use>
								</svg>
								<span>Light</span>
							</a>
							<a
								href="#!"
								class="dropdown-item"
								onclick="layout_change_default()">
								<svg class="pc-icon">
									<use xlink:href="#custom-setting-2"></use>
								</svg>
								<span>Default</span>
							</a>
						</div>
					</li>
					<li class="dropdown pc-h-item">
						<a
							class="pc-head-link dropdown-toggle arrow-none me-0"
							data-bs-toggle="dropdown"
							href="#"
							role="button"
							aria-haspopup="false"
							aria-expanded="false">
							<svg class="pc-icon">
								<use xlink:href="#custom-setting-2"></use>
							</svg>
						</a>
						<div class="dropdown-menu dropdown-menu-end pc-h-dropdown">
							<a href="/configuracoes/meu-perfil" class="dropdown-item">
								<i class="ti ti-user"></i>
								<span>Minha conta</span>
							</a>
							<a href="/configuracoes" class="dropdown-item">
								<i class="ti ti-settings"></i>
								<span>Configurações</span>
							</a>
							<a href="/auth/logoff" class="dropdown-item">
								<i class="ti ti-power"></i>
								<span>Sair</span>
							</a>
						</div>
					</li>
					<li class="dropdown pc-h-item header-user-profile">
						<a
							class="pc-head-link dropdown-toggle arrow-none me-0"
							data-bs-toggle="dropdown"
							href="#"
							role="button"
							aria-haspopup="false"
							data-bs-auto-close="outside"
							aria-expanded="false">
							<img
								src="<?= \Kernel\Session::get('foto_perfil') ?>"
								alt="user-image"
								class="user-avtar" />
						</a>
						<div
							class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
							<div
								class="dropdown-header d-flex align-items-center justify-content-between">
								<h5 class="m-0">Perfil</h5>
							</div>
							<div class="dropdown-body">
								<div
									class="profile-notification-scroll position-relative"
									style="max-height: calc(100vh - 225px)">
									<div class="d-flex mb-1">
										<div class="flex-shrink-0">
											<img
												src="<?= \Kernel\Session::get('foto_perfil') ?>"
												alt="user-image"
												class="user-avtar wid-35" />
										</div>
										<div class="flex-grow-1 ms-3">
											<h6 class="mb-1"><?= \Kernel\Session::get('name') ?></h6>
											<span> <?= \Kernel\Session::get('perfil') ?> </span>
										</div>
									</div>
									<hr class="border-secondary border-opacity-50" />
									<div class="card">
										<div class="card-body py-3">
											<div
												class="d-flex align-items-center justify-content-between">
												<h5 class="mb-0 d-inline-flex align-items-center">
													<svg class="pc-icon text-muted me-2">
														<use
															xlink:href="#custom-notification-outline"></use>
													</svg>Notificações
												</h5>
												<div
													class="form-check form-switch form-check-reverse m-0">
													<input
														class="form-check-input f-18"
														type="checkbox"
														role="switch" />
												</div>
											</div>
										</div>
									</div>
									<p class="text-span"> Gerenciar </p>
									<?php if (Kernel\Session::get('perfil') == 'Administrador') { ?>
										<a href="/configuracoes" class="dropdown-item">
											<span>
												<svg class="pc-icon text-muted me-2">
													<use xlink:href="#custom-setting-outline"></use>
												</svg>
												<span> Configurações </span>
											</span>
										</a>
									<?php } ?>
									<a href="#" class="dropdown-item">
										<span>
											<svg class="pc-icon text-muted me-2">
												<use xlink:href="#custom-lock-outline"></use>
											</svg>
											<span> Alterar senha </span>
										</span>
									</a>
									<hr class="border-secondary border-opacity-50" />
									<div class="d-grid mb-3">
										<a href="/auth/logoff" class="btn btn-primary">
											<svg class="pc-icon me-2">
												<use xlink:href="#custom-logout-1-outline"></use>
											</svg>Sair
										</a>
									</div>
								</div>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
	</header>
	<!-- [ Header ] end -->

	<?php include($slot) ?>

	<!-- [ Main Content ] end -->
	<footer class="pc-footer">
		<div class="footer-wrapper container-fluid">
			<div class="row">
				<div class="col my-1">
					<p class="m-0">
						Sistema de Gestão IEADEME &reg;
					</p>
				</div>
				<div class="col-auto my-1">
					Reobote Tecnologia e Pagamentos © <?= date('Y') ?>
				</div>
			</div>
		</div>
	</footer>
	<!-- Required Js -->
	<script src="/assets/js/plugins/popper.min.js"></script>
	<script src="/assets/js/plugins/simplebar.min.js"></script>
	<script src="/assets/js/plugins/bootstrap.min.js"></script>
	<script src="/assets/js/fonts/custom-font.js"></script>
	<script src="/assets/js/loading.js"></script>
	<script src="/assets/js/pcoded.js"></script>
	<script src="/assets/js/plugins/feather.min.js"></script>
	<script>
		function trocarCongregacao(congregacaoId) {
			fetch('/auth/switch-congregacao', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: 'congregacao_id=' + congregacaoId
				})
				.then(response => response.json())
				.then(data => {
					if (!data.success) {
						alert(data.message);
					}

					window.location.reload();
				})
				.catch(() => {
					alert('Erro ao trocar congregação');
				});
		}
	</script>

	<!-- [Flatpickr] https://flatpickr.js.org/ -->
	<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
	<script src="/js/dist/l10n/pt.js"></script>
	<script>
		flatpickr(".date-flatpickr", {
			locale: "pt",
			dateFormat: "d/m/Y",
		});
		flatpickr(".date-time-flatpickr", {
			enableTime: true,
			dateFormat: "d/m/Y H:i",
			time_24hr: true
		});
		flatpickr('.time-flatpickr', {
			enableTime: true,
			noCalendar: true,
			dateFormat: "H:i",
			time_24hr: true,
			locale: "pt"
		});
	</script>

	<?php if ($this->module('datatable')) { ?>
		<script src="<?= assets('/js/jquery.dataTables.min.js') ?>"></script>
		<script src="<?= assets('/js/dataTables.bootstrap5.min.js') ?>"></script>
		<script src="<?= assets('/js/moment.min.js') ?>"></script>
		<script src="<?= assets('/js/datetime-moment.js') ?>"></script>
		<script type="text/javascript">
			$(document).ready(() => {
				$.fn.dataTable.moment('DD/MM/YYYY HH:mm:ss');
			})
		</script>
	<?php } ?>
</body>
<!-- [Body] end -->

</html>