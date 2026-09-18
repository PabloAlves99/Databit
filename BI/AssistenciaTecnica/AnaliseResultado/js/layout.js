// js/layout.js

function inicializarControlesDeLayout() {
  const checkboxes = document.querySelectorAll(".toggle-modulo");
  const radiosLayout = document.querySelectorAll(".layout-mode-radio");

  // 1. Inicia o formato da tela (1 ou 2 por linha) gravado no localStorage
  const formatoSalvo = localStorage.getItem("layout_modo_exibicao") || "grid";
  radiosLayout.forEach((radio) => {
    if (radio.value === formatoSalvo) radio.checked = true;

    // Ouve a troca do layout
    radio.addEventListener("change", function () {
      localStorage.setItem("layout_modo_exibicao", this.value);
      recalcularTamanhoDoGrid();
    });
  });

  // 2. Inicia os módulos visíveis
  checkboxes.forEach((chk) => {
    const estadoSalvo = localStorage.getItem("layout_" + chk.id);
    if (estadoSalvo !== null) {
      chk.checked = estadoSalvo === "true";
    }

    aplicarVisibilidadeGrid(chk); // aplica ocultar/mostrar

    // Ouve a troca dos switches on/off
    chk.addEventListener("change", function () {
      localStorage.setItem("layout_" + this.id, this.checked);
      aplicarVisibilidadeGrid(this);
      recalcularTamanhoDoGrid(); // redimensiona se algo sumiu
    });
  });

  // Ajusta o tamanho inicial de tudo
  recalcularTamanhoDoGrid();
}

function aplicarVisibilidadeGrid(checkbox) {
  const targetId = checkbox.getAttribute("data-target");
  const modulo = document.getElementById(targetId);

  if (modulo) {
    if (checkbox.checked) {
      modulo.classList.remove("d-none"); // Mostra
    } else {
      modulo.classList.add("d-none"); // Oculta
    }
  }
}

function recalcularTamanhoDoGrid() {
  const modulos = document.querySelectorAll(".modulo-dashboard");
  const visiveis = Array.from(modulos).filter(
    (m) => !m.classList.contains("d-none"),
  );
  const modoExibicao = localStorage.getItem("layout_modo_exibicao") || "grid";

  if (modoExibicao === "full") {
    // LAYOUT: 1 POR LINHA (Estica todos para 100%)
    visiveis.forEach((m) => {
      m.classList.remove("col-lg-6");
      m.classList.add("col-lg-12");
    });
  } else {
    // LAYOUT: 2 POR LINHA (Padrão)
    visiveis.forEach((m) => {
      m.classList.remove("col-lg-12");
      m.classList.add("col-lg-6");
    });

    // O pulo do gato: se sobrou ímpar, o último abraça o espaço vazio
    if (visiveis.length % 2 !== 0 && visiveis.length > 0) {
      visiveis[visiveis.length - 1].classList.replace("col-lg-6", "col-lg-12");
    }
  }

  // Aciona o redimensionamento nativo do ApexCharts instantaneamente
  // (sem isso, os gráficos só se ajustariam quando o usuário mexesse a janela do PC)
  setTimeout(() => {
    window.dispatchEvent(new Event("resize"));
  }, 250);
}

// Inicia automaticamente assim que o HTML estiver carregado
document.addEventListener("DOMContentLoaded", inicializarControlesDeLayout);
