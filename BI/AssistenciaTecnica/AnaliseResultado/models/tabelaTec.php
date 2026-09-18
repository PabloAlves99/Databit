<div class="card shadow-sm h-80 border-0">
    <div class="card-body p-0">
        <div class="table-responsive" style="border-radius: 24px;">
            <table class="table table-hover table-sm align-middle m-1 mb-0" id="tabela-tecnicos">
                <thead class="text-muted" style="font-size: 0.85rem;">
                    <tr>
                        <th>Técnico</th>
                        <th class="text-center">OS fechadas</th>
                        <th class="text-center">% SLA Exc.</th>
                        <th class="text-center">Produção</th>
                        <th class="text-center">Deslocamento</th>
                        <th class="text-center">Peças trocadas</th>
                        <th class="text-center">Reincidência</th>
                        <th class="text-center">Retorno</th>
                    </tr>
                </thead>
                <tbody id="tbody-tecnicos" style="font-size: 0.9rem;">
                    <tr>
                        <td colspan="8" class="text-center py-4">Carregando dados...</td>
                    </tr>
                </tbody>
                <tfoot class="fw-bold bg-light" style="font-size: 0.9rem; position: sticky; bottom: 0;">
                    <tr>
                        <td>Total</td>
                        <td class="text-center" id="tot-os">0</td>
                        <td class="text-center" id="tot-sla">0,00%</td>
                        <td class="text-center" id="tot-prod">00:00:00</td>
                        <td class="text-center" id="tot-desl">00:00:00</td>
                        <td class="text-center" id="tot-pecas">0,00</td>
                        <td class="text-center" id="tot-reincidencia">0</td>
                        <td class="text-center" id="tot-retorno">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>