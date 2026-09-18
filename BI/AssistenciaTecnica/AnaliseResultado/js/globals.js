// --- VARIÁVEIS GLOBAIS ---
let chartTipoOS, chartSlaMes, chartOsDia;
let rawDataGlobal = []; // Guarda os dados crus do banco

// Variáveis de estado para os filtros clicáveis ocultos
let filtroClicavelMes = null;
let filtroClicavelDia = null;

// Dicionário Fixo de Cores
const MAPA_CORES_TIPO_OS = {
  Normal: "#4794c4",
  Aferição: "#8c92ac",
  Instalação: "#4db6ac",
  Desinstalação: "#f0b27a",
  Preventiva: "#af7ac5",
  "Retorno / Recarga": "#cd6155",
};

// --- FUNÇÕES DE FORMATAÇÃO GLOBAIS ---
function formatarSegundos(segundos) {
  if (!segundos) return "00:00:00";
  const h = Math.floor(segundos / 3600)
    .toString()
    .padStart(2, "0");
  const m = Math.floor((segundos % 3600) / 60)
    .toString()
    .padStart(2, "0");
  const s = Math.round(segundos % 60)
    .toString()
    .padStart(2, "0");
  return `${h}:${m}:${s}`;
}

function formatarNumero(valor) {
  return parseFloat(valor).toLocaleString("pt-BR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function formatarPercentual(valor) {
  return (
    valor.toLocaleString("pt-BR", {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }) + "%"
  );
}
