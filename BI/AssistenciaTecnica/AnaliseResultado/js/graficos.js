// --- RENDERIZAÇÃO DOS GRÁFICOS ---
function customTooltip({ series, seriesIndex, dataPointIndex, w }) {
  const data = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
  if (!data || !data.extra) return "";
  const formatBRL = (val) =>
    parseFloat(val).toLocaleString("pt-BR", {
      style: "currency",
      currency: "BRL",
    });
  return `
    <div class="px-3 py-2" style="background: #fff; border: 1px solid #ccc; font-size: 0.85rem;">
      <strong class="d-block mb-1 border-bottom pb-1">${data.tipoOS || w.globals.labels[dataPointIndex]}</strong>
      <div class="d-flex justify-content-between"><span>OS:</span> <strong>${data.y}</strong></div>
      <div class="d-flex justify-content-between"><span>Retorno:</span> <strong class="ms-3">${data.extra.retorno}</strong></div>
      <div class="d-flex justify-content-between"><span>Reincidência:</span> <strong class="ms-3">${data.extra.reincidencia}</strong></div>
      <div class="d-flex justify-content-between"><span>SLA Excedido:</span> <strong class="ms-3">${data.extra.sla_excedido}</strong></div>
      <div class="d-flex justify-content-between"><span>Peças:</span> <strong class="ms-3">${formatBRL(data.extra.pecas)}</strong></div>
      <div class="d-flex justify-content-between"><span>Clientes:</span> <strong class="ms-3">${data.extra.clientes}</strong></div>
      <div class="mt-2 text-center text-muted" style="font-size: 0.70rem; border-top: 1px dashed #ccc; padding-top: 4px;">Clique para filtrar</div>
    </div>`;
}

function processarERenderizarGraficos(rawDataFiltrado) {
  const chartData = [];
  const osProcessadas = new Set();

  rawDataFiltrado.forEach((row) => {
    if (!osProcessadas.has(row.OS)) {
      osProcessadas.add(row.OS);
      chartData.push(row);
    }
  });

  const tiposUnicos = new Set();
  chartData.forEach((row) => tiposUnicos.add(row.Tipo));

  const agregacaoMesTipo = {};
  const agregacaoMesTotal = {};
  const agregacaoDia = {};

  const initMetricas = () => ({
    qtdOS: 0,
    retorno: 0,
    reincidencia: 0,
    sla_excedido: 0,
    pecas: 0,
    clientesSet: new Set(),
  });

  let dataRangeInicio = document.getElementById("data_inicio").value;
  let dataRangeFim = document.getElementById("data_fim").value;

  if (filtroClicavelDia) {
    dataRangeInicio = filtroClicavelDia;
    dataRangeFim = filtroClicavelDia;
  } else if (filtroClicavelMes) {
    const [yy, mm] = filtroClicavelMes.split("-");
    const ultimoDia = new Date(yy, mm, 0).getDate();
    const calcInicio = `${filtroClicavelMes}-01`;
    const calcFim = `${filtroClicavelMes}-${ultimoDia}`;

    dataRangeInicio =
      calcInicio < dataRangeInicio ? dataRangeInicio : calcInicio;
    dataRangeFim = calcFim > dataRangeFim ? dataRangeFim : calcFim;
  }

  const dtInicio = new Date(dataRangeInicio + "T00:00:00");
  const dtFim = new Date(dataRangeFim + "T00:00:00");

  for (let d = new Date(dtInicio); d <= dtFim; d.setDate(d.getDate() + 1)) {
    agregacaoDia[d.getTime()] = initMetricas();
    const mesStr =
      d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0");
    if (!agregacaoMesTipo[mesStr]) {
      agregacaoMesTipo[mesStr] = {};
      tiposUnicos.forEach(
        (t) => (agregacaoMesTipo[mesStr][t] = initMetricas()),
      );
    }
    if (!agregacaoMesTotal[mesStr]) agregacaoMesTotal[mesStr] = initMetricas();
  }

  chartData.forEach((row) => {
    const mes = row.AnoMes;
    const timestamp = new Date(row.DataB + "T00:00:00").getTime();
    const tipo = row.Tipo;

    const os = 1;
    const ret = parseInt(row.Retorno) || 0;
    const rei = parseInt(row.Reincidencia) || 0;
    const sla = parseInt(row.SLA_Excedido) || 0;
    const pec = parseFloat(row.ValorPecasTrocadas) || 0;
    const cli = row.CodCli;

    if (agregacaoMesTipo[mes] && agregacaoMesTipo[mes][tipo]) {
      let m1 = agregacaoMesTipo[mes][tipo];
      m1.qtdOS += os;
      m1.retorno += ret;
      m1.reincidencia += rei;
      m1.sla_excedido += sla;
      m1.pecas += pec;
      if (cli) m1.clientesSet.add(cli);
    }
    if (agregacaoMesTotal[mes]) {
      let m2 = agregacaoMesTotal[mes];
      m2.qtdOS += os;
      m2.retorno += ret;
      m2.reincidencia += rei;
      m2.sla_excedido += sla;
      m2.pecas += pec;
      if (cli) m2.clientesSet.add(cli);
    }
    if (agregacaoDia[timestamp]) {
      let m3 = agregacaoDia[timestamp];
      m3.qtdOS += os;
      m3.retorno += ret;
      m3.reincidencia += rei;
      m3.sla_excedido += sla;
      m3.pecas += pec;
      if (cli) m3.clientesSet.add(cli);
    }
  });

  renderizarChartTipoOS(agregacaoMesTipo, tiposUnicos);
  renderizarChartSlaMes(agregacaoMesTotal);
  renderizarChartOsDia(agregacaoDia);
}

function renderizarChartTipoOS(dados, tiposUnicos) {
  const meses = Object.keys(dados).sort();
  const coresAUsar = [];

  const seriesToRender = Array.from(tiposUnicos).map((tipo) => {
    coresAUsar.push(MAPA_CORES_TIPO_OS[tipo] || "#cccccc");
    return {
      name: tipo,
      data: meses.map((mes) => {
        const m = dados[mes][tipo];
        return {
          x: mes,
          y: m.qtdOS,
          tipoOS: tipo + " - " + mes,
          extra: {
            retorno: m.retorno,
            reincidencia: m.reincidencia,
            sla_excedido: m.sla_excedido,
            pecas: m.pecas,
            clientes: m.clientesSet.size,
          },
        };
      }),
    };
  });

  if (chartTipoOS) {
    chartTipoOS.updateOptions({
      xaxis: { categories: meses },
      colors: coresAUsar,
    });
    chartTipoOS.updateSeries(seriesToRender);
  } else {
    const options = {
      chart: {
        type: "bar",
        height: "100%",
        toolbar: { show: false },
        events: {
          dataPointSelection: function (event, chartContext, config) {
            if (config.seriesIndex === -1) return;
            const tipoClicado = config.w.config.series[config.seriesIndex].name;
            const selectTipo = document.getElementById("filtro_tipo_os");
            if (selectTipo) {
              selectTipo.value =
                selectTipo.value === tipoClicado ? "" : tipoClicado;
              aplicarFiltrosLocais();
            }
          },
        },
      },
      series: seriesToRender,
      colors: coresAUsar,
      plotOptions: {
        bar: { dataLabels: { position: "top" }, cursor: "pointer" },
      },
      dataLabels: {
        enabled: true,
        offsetY: -20,
        style: { fontSize: "10px", colors: ["#304758"] },
      },
      xaxis: { categories: meses },
      tooltip: { custom: customTooltip },
    };
    chartTipoOS = new ApexCharts(
      document.querySelector("#chart-tipo-os"),
      options,
    );
    chartTipoOS.render();
  }
}

function renderizarChartSlaMes(dados) {
  const meses = Object.keys(dados).sort();
  const dataOS = [],
    dataPerc = [];
  let totalSlaExcedidoPeriodo = 0;
  meses.forEach((mes) => {
    totalSlaExcedidoPeriodo += dados[mes].sla_excedido;
  });

  meses.forEach((mes) => {
    const m = dados[mes];
    dataOS.push({
      x: mes,
      y: m.qtdOS,
      tipoOS: "Todas OS - " + mes,
      extra: {
        retorno: m.retorno,
        reincidencia: m.reincidencia,
        sla_excedido: m.sla_excedido,
        pecas: m.pecas,
        clientes: m.clientesSet.size,
      },
    });
    dataPerc.push({
      x: mes,
      y:
        totalSlaExcedidoPeriodo > 0
          ? parseFloat(
              ((m.sla_excedido / totalSlaExcedidoPeriodo) * 100).toFixed(2),
            )
          : 0,
    });
  });

  const seriesData = [
    { name: "OS", type: "column", data: dataOS },
    { name: "% SLA Excedido", type: "line", data: dataPerc },
  ];

  if (chartSlaMes) {
    chartSlaMes.updateOptions({ xaxis: { categories: meses } });
    chartSlaMes.updateSeries(seriesData);
  } else {
    const options = {
      chart: {
        height: "100%",
        type: "line",
        toolbar: { show: false },
        events: {
          dataPointSelection: function (event, chartContext, config) {
            if (config.dataPointIndex === -1) return;
            const mesClicado =
              config.w.config.xaxis.categories[config.dataPointIndex];
            if (!mesClicado) return;
            filtroClicavelMes =
              filtroClicavelMes === mesClicado ? null : mesClicado;
            aplicarFiltrosLocais();
          },
        },
      },
      series: seriesData,
      colors: ["#3a705f", "#5a5a5a"],
      stroke: { width: [0, 3], curve: "smooth" },
      markers: { size: 5, hover: { sizeOffset: 3 }, cursor: "pointer" },
      plotOptions: { bar: { cursor: "pointer" } },
      dataLabels: {
        enabled: true,
        enabledOnSeries: [0, 1],
        formatter: (val, opts) => (opts.seriesIndex === 1 ? val + "%" : val),
        offsetY: -10,
      },
      xaxis: { categories: meses },
      yaxis: [
        { title: { text: "Qtd OS" }, min: 0 },
        {
          opposite: true,
          min: 0,
          max: 100,
          labels: { formatter: (val) => val.toFixed(0) + "%" },
        },
      ],
      tooltip: {
        shared: false,
        intersect: false,
        custom: (opts) =>
          opts.seriesIndex === 0 ? customTooltip(opts) : undefined,
      },
    };
    chartSlaMes = new ApexCharts(
      document.querySelector("#chart-sla-mes"),
      options,
    );
    chartSlaMes.render();
  }
}

function renderizarChartOsDia(dados) {
  const timestamps = Object.keys(dados).map(Number).sort();
  const dataDiario = timestamps.map((ts) => {
    const m = dados[ts];
    return {
      x: ts,
      y: m.qtdOS,
      tipoOS: "Diário - " + new Date(ts).toLocaleDateString("pt-BR"),
      extra: {
        retorno: m.retorno,
        reincidencia: m.reincidencia,
        sla_excedido: m.sla_excedido,
        pecas: m.pecas,
        clientes: m.clientesSet.size,
      },
    };
  });

  // Lógica de Inteligência Visual:
  // Se houver mais de 35 dias no filtro, oculta os rótulos e bolinhas para não poluir
  const muitosDias = dataDiario.length > 35;
  const tamanhoMarcador = muitosDias ? 0 : 5; // 0 esconde, mas ainda aparece no hover do mouse
  const mostrarLabels = !muitosDias; // true para poucos dias, false para muitos

  if (chartOsDia) {
    // Atualiza não só os dados, mas também as configurações visuais de acordo com o período
    chartOsDia.updateOptions({
      markers: { size: tamanhoMarcador },
      dataLabels: { enabled: mostrarLabels },
    });
    chartOsDia.updateSeries([{ name: "OS Diárias", data: dataDiario }]);
  } else {
    const options = {
      chart: {
        type: "area", // Mudado para 'area' para ter o fundo preenchido igual ao Power BI
        height: "100%",
        toolbar: { show: false },
        events: {
          dataPointSelection: function (event, chartContext, config) {
            if (config.dataPointIndex === -1) return;
            const dataPonto =
              config.w.config.series[0]?.data[config.dataPointIndex];
            if (!dataPonto || !dataPonto.x) return;

            const d = new Date(dataPonto.x);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, "0");
            const dd = String(d.getDate()).padStart(2, "0");
            const dataClicada = `${yyyy}-${mm}-${dd}`;

            filtroClicavelDia =
              filtroClicavelDia === dataClicada ? null : dataClicada;
            aplicarFiltrosLocais();
          },
        },
      },
      series: [{ name: "OS Diárias", data: dataDiario }],
      colors: ["#1d86c8"],
      stroke: { width: 3, curve: "straight" },
      fill: {
        type: "solid",
        opacity: 0.3, // Deixa o azul clarinho embaixo da linha
      },
      markers: {
        size: tamanhoMarcador,
        hover: { sizeOffset: 4 },
        cursor: "pointer",
      },
      dataLabels: {
        enabled: mostrarLabels,
        offsetY: -5,
        background: { enabled: false },
        style: { colors: ["#304758"] },
      },
      xaxis: {
        type: "datetime",
        labels: { datetimeUTC: false, format: "dd MMM yyyy" },
        tooltip: { enabled: false },
      },
      tooltip: { custom: customTooltip },
    };
    chartOsDia = new ApexCharts(
      document.querySelector("#chart-os-dia"),
      options,
    );
    chartOsDia.render();
  }
}
