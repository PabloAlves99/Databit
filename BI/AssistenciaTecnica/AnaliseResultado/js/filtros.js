// --- FILTROS E DROPDOWNS ---
function popularDropdowns() {
  const preencherSelect = (idSelect, chaveObjeto) => {
    const select = document.getElementById(idSelect);
    if (!select) return;

    const valorAtual = select.value;
    const valoresUnicos = [
      ...new Set(rawDataGlobal.map((item) => item[chaveObjeto])),
    ]
      .filter(Boolean)
      .sort();

    select.innerHTML = '<option value="">Todos</option>';
    valoresUnicos.forEach((val) => {
      select.innerHTML += `<option value="${val}">${val}</option>`;
    });

    select.value = valorAtual;
  };

  preencherSelect("filtro_tecnico", "Tecnico");
  preencherSelect("filtro_tipo_os", "Tipo");
  preencherSelect("filtro_equipamento", "Equipamento");
  preencherSelect("filtro_contrato", "TipoContrato");
  preencherSelect("filtro_cliente", "Cliente");
  preencherSelect("filtro_empresa", "Empresa");
  preencherSelect("filtro_cidade", "Cidade");
  preencherSelect("filtro_defeito", "Defeito");
}

function aplicarFiltrosLocais() {
  const getVal = (id) =>
    document.getElementById(id) ? document.getElementById(id).value : "";

  const tTecnico = getVal("filtro_tecnico");
  const tTipo = getVal("filtro_tipo_os");
  const tEquip = getVal("filtro_equipamento");
  const tContrato = getVal("filtro_contrato");
  const tCliente = getVal("filtro_cliente");
  const tEmpresa = getVal("filtro_empresa");
  const tCidade = getVal("filtro_cidade");
  const tDefeito = getVal("filtro_defeito");
  const tGlobal = getVal("filtro_global").toLowerCase().trim();

  const dadosFiltrados = rawDataGlobal.filter((row) => {
    if (tTecnico && row.Tecnico !== tTecnico) return false;
    if (tTipo && row.Tipo !== tTipo) return false;
    if (tEquip && row.Equipamento !== tEquip) return false;
    if (tContrato && row.TipoContrato !== tContrato) return false;
    if (tCliente && row.Cliente !== tCliente) return false;
    if (tEmpresa && row.Empresa !== tEmpresa) return false;
    if (tCidade && row.Cidade !== tCidade) return false;
    if (tDefeito && row.Defeito !== tDefeito) return false;

    if (filtroClicavelMes && row.AnoMes !== filtroClicavelMes) return false;
    if (filtroClicavelDia && row.DataB !== filtroClicavelDia) return false;

    if (tGlobal) {
      const rowTextoCompleto = Object.values(row).join(" ").toLowerCase();
      if (!rowTextoCompleto.includes(tGlobal)) return false;
    }
    return true;
  });

  agruparERenderizarTabelas(dadosFiltrados);
  processarERenderizarGraficos(dadosFiltrados);
  atualizarKPIs(dadosFiltrados);
}
