// --- CARREGAMENTO INICIAL E FETCH API ---
async function carregarDadosDoBanco() {
  const dataInicio = document.getElementById("data_inicio").value;
  const dataFim = document.getElementById("data_fim").value;
  const tbody = document.getElementById("tbody-tecnicos");

  tbody.innerHTML =
    '<tr><td colspan="8" class="text-center py-4">Carregando do banco de dados...</td></tr>';

  try {
    const response = await fetch(
      `consulta.php?data_inicio=${dataInicio}&data_fim=${dataFim}`,
    );
    rawDataGlobal = await response.json();

    if (rawDataGlobal.erro) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">${rawDataGlobal.erro}</td></tr>`;
      return;
    }

    filtroClicavelMes = null;
    filtroClicavelDia = null;

    popularDropdowns();
    aplicarFiltrosLocais();
  } catch (error) {
    tbody.innerHTML =
      '<tr><td colspan="8" class="text-center text-danger py-4">Erro fatal de conexão. Verifique o console.</td></tr>';
    console.error(error);
  }
}

function salvarEBuscarDados() {
  localStorage.setItem(
    "filtro_data_inicio",
    document.getElementById("data_inicio").value,
  );
  localStorage.setItem(
    "filtro_data_fim",
    document.getElementById("data_fim").value,
  );
  carregarDadosDoBanco();
}

// --- EVENT LISTENERS ---
document.addEventListener("DOMContentLoaded", () => {
  const dInicio = localStorage.getItem("filtro_data_inicio");
  const dFim = localStorage.getItem("filtro_data_fim");
  if (dInicio) document.getElementById("data_inicio").value = dInicio;
  if (dFim) document.getElementById("data_fim").value = dFim;

  carregarDadosDoBanco();

  // As datas fazem a consulta pesada no Banco
  document
    .getElementById("data_inicio")
    .addEventListener("change", salvarEBuscarDados);
  document
    .getElementById("data_fim")
    .addEventListener("change", salvarEBuscarDados);

  // Os combos pesquisam instantaneamente na memória
  const inputsLocais = document.querySelectorAll(".din-filter");
  inputsLocais.forEach((input) =>
    input.addEventListener("input", aplicarFiltrosLocais),
  );
});

document
  .getElementById("form-filtros")
  .addEventListener("submit", (e) => e.preventDefault());
