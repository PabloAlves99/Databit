// --- ATUALIZAÇÃO DA BARRA SUPERIOR (KPIs) ---
function atualizarKPIs(dadosFiltrados) {
  const dInicio = new Date(
    document.getElementById("data_inicio").value + "T00:00:00",
  );
  const dFim = new Date(
    document.getElementById("data_fim").value + "T00:00:00",
  );

  let dias = Math.round((dFim - dInicio) / (1000 * 60 * 60 * 24)) + 1;
  if (dias <= 0) dias = 1;

  let meses =
    (dFim.getFullYear() - dInicio.getFullYear()) * 12 +
    (dFim.getMonth() - dInicio.getMonth()) +
    1;
  if (meses <= 0) meses = 1;

  let totOS = 0,
    totSlaExc = 0,
    totRetorno = 0,
    totReincidencia = 0;
  let totProd = 0,
    totDesl = 0,
    totKm = 0;

  const osProcessadas = new Set();

  dadosFiltrados.forEach((row) => {
    // Conta 1 vez por OS
    if (!osProcessadas.has(row.OS)) {
      osProcessadas.add(row.OS);
      totOS++;
      totSlaExc += row.SLA_Excedido;
      totRetorno += row.Retorno;
      totReincidencia += row.Reincidencia;
    }
    // Soma cumulativa do esforço de todos os envolvidos
    totProd += row.ProdSegundos;
    totDesl += row.TempoDeslocSeg;
    totKm += row.KMSaldo || 0;
  });

  const percSla = totOS > 0 ? Math.round((totSlaExc / totOS) * 100) : 0;
  const mediaDia = Math.round(totOS / dias);
  const mediaMes = Math.round(totOS / meses);

  document.getElementById("kpi-os").innerText = totOS.toLocaleString("pt-BR");
  document.getElementById("kpi-sla").innerText =
    totSlaExc.toLocaleString("pt-BR");
  document.getElementById("kpi-perc-sla").innerText = percSla + "%";
  document.getElementById("kpi-med-mes").innerText =
    mediaMes.toLocaleString("pt-BR");
  document.getElementById("kpi-med-dia").innerText =
    mediaDia.toLocaleString("pt-BR");
  document.getElementById("kpi-prod").innerText = formatarSegundos(totProd);
  document.getElementById("kpi-desl").innerText = formatarSegundos(totDesl);
  document.getElementById("kpi-km").innerText =
    Math.round(totKm).toLocaleString("pt-BR");
  document.getElementById("kpi-retorno").innerText =
    totRetorno.toLocaleString("pt-BR");
  document.getElementById("kpi-reincidencia").innerText =
    totReincidencia.toLocaleString("pt-BR");
}
