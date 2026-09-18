<form id="form-filtros">
    <!-- Linha 1 -->
    <div class="row g-2 align-items-center mb-3" style="font-size: 0.85rem;">
        <div class="col-auto"><span class="fw-bold text-dark">Período:</span></div>
        <div class="col-auto"><input type="date" class="form-control form-control-sm" id="data_inicio" value="<?php echo date('Y-m-01'); ?>"></div>
        <div class="col-auto"><input type="date" class="form-control form-control-sm" id="data_fim" value="<?php echo date('Y-m-d'); ?>"></div>

        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-dark">Técnico:</span></div>
        <div class="col-xl-2 col-md-3"><select id="filtro_tecnico" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>

        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-dark">Tipo de OS:</span></div>
        <div class="col-xl-2 col-md-3"><select id="filtro_tipo_os" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>

        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-dark">Equipamento:</span></div>
        <div class="col"><select id="filtro_equipamento" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>

        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-dark">Tipo Contrato:</span></div>
        <div class="col-xl-1 col-md-2"><select id="filtro_contrato" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>
    </div>

    <!-- Linha 2 -->
    <div class="row g-2 align-items-center mb-3" style="font-size: 0.85rem;">
        <div class="col-auto"><span class="fw-bold text-dark">Cliente:</span></div>
        <div class="col-xl-3 col-md-4"><select id="filtro_cliente" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>

        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-dark">Empresa:</span></div>
        <div class="col"><select id="filtro_empresa" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>

        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-dark">Cidade:</span></div>
        <div class="col-xl-2 col-md-3"><select id="filtro_cidade" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>

        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-dark">Defeito:</span></div>
        <div class="col-xl-2 col-md-3"><select id="filtro_defeito" class="form-select form-select-sm din-filter">
                <option value="">Todos</option>
            </select></div>
    </div>

    <!-- Filtro Global -->
    <div class="row g-2 align-items-center border-top pt-3" style="font-size: 0.85rem;">
        <div class="col-auto"><span class="fw-bold text-danger">Pesquisa Global:</span></div>
        <div class="col-xl-4 col-md-5">
            <input type="text" id="filtro_global" class="form-control form-control-sm din-filter" placeholder="Pesquise por palavra, OS ou valor...">
        </div>

        <!-- MENU DE OCULTAR GRÁFICOS -->
        <div class="col-auto ms-xl-4 ms-2"><span class="fw-bold text-primary-emphasis">Painel:</span></div>
        <div class="col-auto">
            <div class="dropdown">
                <!-- O data-bs-auto-close="outside" impede o menu de fechar a cada clique -->
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="bi bi-layout-text-window"></i> Exibir Módulos
                </button>
                <ul class="dropdown-menu p-3 shadow" style="min-width: 260px;">
                    <li>
                        <h6 class="dropdown-header text-primary-emphasis px-0">Módulos Visíveis</h6>
                    </li>

                    <li>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input toggle-modulo" type="checkbox" id="chk-tabela-clientes" data-target="card-tabela-clientes" checked>
                            <label class="form-check-label" style="cursor: pointer;" for="chk-tabela-clientes">Tabela de Clientes</label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input toggle-modulo" type="checkbox" id="chk-tabela" data-target="card-tabela" checked>
                            <label class="form-check-label" style="cursor: pointer;" for="chk-tabela">Tabela de Técnicos</label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input toggle-modulo" type="checkbox" id="chk-tipo-os" data-target="card-tipo-os" checked>
                            <label class="form-check-label" style="cursor: pointer;" for="chk-tipo-os">Gráfico: Tipo de OS</label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input toggle-modulo" type="checkbox" id="chk-sla-mes" data-target="card-sla-mes" checked>
                            <label class="form-check-label" style="cursor: pointer;" for="chk-sla-mes">Gráfico: SLA por Mês</label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check form-switch">
                            <input class="form-check-input toggle-modulo" type="checkbox" id="chk-os-dia" data-target="card-os-dia" checked>
                            <label class="form-check-label" style="cursor: pointer;" for="chk-os-dia">Gráfico: OS por Dia</label>
                        </div>
                    </li>

                    <!-- NOVO: OPÇÕES DE LAYOUT -->
                    <li>
                        <hr class="dropdown-divider mt-3">
                    </li>
                    <li>
                        <h6 class="dropdown-header text-primary px-0">Formato da Tela</h6>
                    </li>
                    <li>
                        <div class="form-check mb-2">
                            <input class="form-check-input layout-mode-radio" type="radio" name="layoutMode" id="layout-grid" value="grid" checked>
                            <label class="form-check-label" style="cursor: pointer;" for="layout-grid"><i class="bi bi-grid-1x2"></i> 2 por linha</label>
                        </div>
                    </li>
                    <li>
                        <div class="form-check">
                            <input class="form-check-input layout-mode-radio" type="radio" name="layoutMode" id="layout-full" value="full">
                            <label class="form-check-label" style="cursor: pointer;" for="layout-full"><i class="bi bi-distribute-vertical"></i> 1 por linha</label>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</form>