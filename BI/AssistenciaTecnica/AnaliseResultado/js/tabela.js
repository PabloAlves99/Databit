// --- js/tabela.js ---

function toggleFilhos(idNode) {
  const icon = document.getElementById(`icon-${idNode}`);
  const rows = document.querySelectorAll(`.filho-${idNode}`);
  if (icon.classList.contains("bi-dash-square")) {
    icon.classList.replace("bi-dash-square", "bi-plus-square");
    rows.forEach((r) => (r.style.display = "none"));
  } else {
    icon.classList.replace("bi-plus-square", "bi-dash-square");
    rows.forEach((r) => (r.style.display = "table-row"));
  }
}

// Essa função agora constrói TODAS as tabelas do seu painel simultaneamente
function agruparERenderizarTabelas(dados) {
  // 1. Constrói a Tabela de Técnicos
  renderizarTabelaGenerica(
    dados,
    "Tecnico", // Chave do JSON para agrupar
    "filtro_tecnico", // ID do filtro superior
    "tbody-tecnicos", // ID do corpo da tabela
    "tec", // Prefixo para ID do HTML
    {
      os: "tot-os",
      sla: "tot-sla",
      prod: "tot-prod",
      desl: "tot-desl",
      pecas: "tot-pecas",
      reinc: "tot-reincidencia",
      ret: "tot-retorno",
    },
  );

  // 2. Constrói a Tabela de Clientes
  renderizarTabelaGenerica(
    dados,
    "Cliente", // Chave do JSON
    "filtro_cliente", // ID do filtro
    "tbody-clientes",
    "cli",
    {
      os: "tot-os-cli",
      sla: "tot-sla-cli",
      prod: "tot-prod-cli",
      desl: "tot-desl-cli",
      pecas: "tot-pecas-cli",
      reinc: "tot-reinc-cli",
      ret: "tot-ret-cli",
    },
  );
}

// Motor Universal de Construção de Tabelas (Não importa qual seja a coluna principal)
function renderizarTabelaGenerica(
  dados,
  chaveAgrupamento,
  idSelectFiltro,
  tbodyId,
  idPrefix,
  footers,
) {
  const tbody = document.getElementById(tbodyId);
  if (!tbody) return;

  tbody.innerHTML = "";

  if (dados.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="8" class="text-center py-4">Nenhum dado encontrado para este filtro.</td></tr>';
    document.getElementById(footers.os).innerText = "0";
    document.getElementById(footers.sla).innerText = "0,00%";
    document.getElementById(footers.prod).innerText = "00:00:00";
    document.getElementById(footers.desl).innerText = "00:00:00";
    document.getElementById(footers.pecas).innerText = "0,00";
    document.getElementById(footers.reinc).innerText = "0";
    document.getElementById(footers.ret).innerText = "0";
    return;
  }

  const matriz = {};
  dados.forEach((row) => {
    // Busca dinamicamente a propriedade (Técnico ou Cliente)
    const chave = row[chaveAgrupamento] || "NÃO INFORMADO";
    if (!matriz[chave]) {
      matriz[chave] = {
        Nome: chave,
        OSFechadas: 0,
        SLAExcedido: 0,
        ProdSegundos: 0,
        TempoDeslocSeg: 0,
        ValorPecasTrocadas: 0,
        Reincidencia: 0,
        Retorno: 0,
        Filhos: [],
      };
    }
    matriz[chave].OSFechadas++;
    matriz[chave].SLAExcedido += row.SLA_Excedido;
    matriz[chave].ProdSegundos += row.ProdSegundos;
    matriz[chave].TempoDeslocSeg += row.TempoDeslocSeg;
    matriz[chave].ValorPecasTrocadas += row.ValorPecasTrocadas;
    matriz[chave].Reincidencia += row.Reincidencia;
    matriz[chave].Retorno += row.Retorno;
    matriz[chave].Filhos.push(row);
  });

  const dadosAgrupados = Object.values(matriz).sort(
    (a, b) => b.OSFechadas - a.OSFechadas,
  );

  let totOs = 0,
    totSlaExc = 0,
    totProd = 0,
    totDesl = 0,
    totPecas = 0,
    totReincidencia = 0,
    totRetorno = 0;

  dadosAgrupados.forEach((linha, index) => {
    const nodeId = `${idPrefix}-${index}`;
    const percSla =
      linha.OSFechadas > 0 ? (linha.SLAExcedido / linha.OSFechadas) * 100 : 0;

    totOs += linha.OSFechadas;
    totSlaExc += linha.SLAExcedido;
    totProd += linha.ProdSegundos;
    totDesl += linha.TempoDeslocSeg;
    totPecas += linha.ValorPecasTrocadas;
    totReincidencia += linha.Reincidencia;
    totRetorno += linha.Retorno;

    const trPai = document.createElement("tr");
    trPai.style.cursor = "pointer";
    trPai.title = `Clique na linha para filtrar o ${chaveAgrupamento}, ou no ícone [+] para abrir as OS.`;

    // Filtro Cruzado Específico para a Tabela que está sendo clicada
    trPai.onclick = (e) => {
      if (e.target.tagName === "I" || e.target.classList.contains("bi")) {
        toggleFilhos(nodeId);
      } else {
        const selectFiltro = document.getElementById(idSelectFiltro);
        if (selectFiltro) {
          selectFiltro.value =
            selectFiltro.value === linha.Nome ? "" : linha.Nome;
          aplicarFiltrosLocais(); // Chama a função global em filtros.js
        }
      }
    };

    // Estilo "ellipsis" previne que um nome de cliente muito longo quebre o layout
    trPai.innerHTML = `
      <td class="text-secondary fw-bold" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;">
        <i id="icon-${nodeId}" class="bi bi-plus-square text-muted me-2"></i> ${linha.Nome}
      </td>
      <td class="text-center fw-bold">${linha.OSFechadas}</td>
      <td class="text-center text-success">${formatarPercentual(percSla)}</td>
      <td class="text-center text-success">${formatarSegundos(linha.ProdSegundos)}</td>
      <td class="text-center text-success">${formatarSegundos(linha.TempoDeslocSeg)}</td>
      <td class="text-center text-success">${formatarNumero(linha.ValorPecasTrocadas)}</td>
      <td class="text-center text-success">${linha.Reincidencia}</td>
      <td class="text-center text-success">${linha.Retorno}</td>`;
    tbody.appendChild(trPai);

    linha.Filhos.forEach((filho) => {
      const percSlaFilho = filho.SLA_Excedido > 0 ? 100 : 0;
      const trFilho = document.createElement("tr");
      trFilho.classList.add(`filho-${nodeId}`);
      trFilho.style.display = "none";
      trFilho.style.backgroundColor = "#fafafa";
      trFilho.innerHTML = `
        <td class="text-secondary ps-4" style="font-size:0.85em;">${filho.OS}</td>
        <td class="text-center text-muted">1</td>
        <td class="text-center" style="color: #4db6ac;">${formatarPercentual(percSlaFilho)}</td>
        <td class="text-center" style="color: #4db6ac;">${formatarSegundos(filho.ProdSegundos)}</td>
        <td class="text-center" style="color: #4db6ac;">${formatarSegundos(filho.TempoDeslocSeg)}</td>
        <td class="text-center" style="color: #4db6ac;">${formatarNumero(filho.ValorPecasTrocadas)}</td>
        <td class="text-center" style="color: #4db6ac;">${filho.Reincidencia}</td>
        <td class="text-center" style="color: #4db6ac;">${filho.Retorno}</td>`;
      tbody.appendChild(trFilho);
    });
  });

  document.getElementById(footers.os).innerText = totOs;
  document.getElementById(footers.sla).innerText = formatarPercentual(
    totOs > 0 ? (totSlaExc / totOs) * 100 : 0,
  );
  document.getElementById(footers.prod).innerText = formatarSegundos(totProd);
  document.getElementById(footers.desl).innerText = formatarSegundos(totDesl);
  document.getElementById(footers.pecas).innerText = formatarNumero(totPecas);
  document.getElementById(footers.reinc).innerText = totReincidencia;
  document.getElementById(footers.ret).innerText = totRetorno;
}
