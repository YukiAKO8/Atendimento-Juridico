jQuery(document).ready(function($) {

    // --- Início: Lógica para busca de sócios (Dropdown dinâmico) ---
    const $input = $('#aj_socios');
    const $box   = $('#aj_socios_suggestions');

    // Apenas executa se o campo de sócios existir na página
    if ($input.length > 0) {
        // Busca os sócios via AJAX ao carregar a página
        $.post(ajaxurl, { action: 'aj_get_socios', _ajax_nonce: aj_object.delete_nonce }, function(res) {
            if (res.success) {
                let html = '';
                res.data.forEach(function(nome) {
                    // Escapa o nome para evitar problemas com aspas ou outros caracteres
                    const esc_nome = $('<div>').text(nome).html();
                    html += '<div class="suggestion-item">' + esc_nome + '</div>';
                });
                $box.html(html);
            }
        });

        // Mostra/esconde a lista de sugestões ao clicar no botão de lupa
        $(document).on('click', '.aj-socio-search-button', function(e) {
            e.stopPropagation(); // Impede que o clique se propague para o document
            $box.toggle();
        });


        // Filtra a lista enquanto o usuário digita
        $input.on('input', function() {
            const val = $(this).val().toLowerCase();
            $box.children().each(function() {
                const txt = $(this).text().toLowerCase();
                $(this).toggle(txt.includes(val));
            });
            $box.show();
        });

        // Preenche o campo ao clicar em uma sugestão
        $box.on('click', '.suggestion-item', function() {
            $input.val($(this).text()).trigger('change');
            $box.hide();
            $input.focus(); // Devolve o foco ao input após a seleção
        });
    }

    // --- Lógica para expandir/recolher a Pesquisa Avançada ---
    $('.aj-top-container-header').on('click', function() {
        const $container = $(this).closest('.aj-top-container');
        $container.toggleClass('collapsed');
        $container.find('.aj-top-container-body').slideToggle(300);
    });


    // --- Lógica para o checkbox customizado "Entrada de processo?" ---

    // Função para atualizar a aparência e o texto do checkbox
    function updateCheckboxState() {
        var checkbox = $('#aj_entrada_processo');
        var container = checkbox.closest('.form-group-checkbox');
        var span = container.find('span');
        
        if (checkbox.is(':checked')) {
            container.addClass('aj-checked');
            span.text('Sim');
        } else {
            container.removeClass('aj-checked');
            span.text('Não');
        }
    }
    

    $('.form-group-checkbox').on('click', function() {
        var checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
    });
  
    $('#aj_entrada_processo').on('change', function() {
        updateCheckboxState();
    });
  
    updateCheckboxState();

 
    $('.aj-btn-limpar').on('click', function(e) {
        if (!confirm('Tem certeza que deseja limpar o formulário? Todos os dados serão perdidos.')) {
            e.preventDefault(); 
        }
    });

    $('.aj-btn-cancelar').on('click', function(e) {
        if (!confirm('Tem certeza que deseja cancelar? As alterações não serão salvas.')) {
            e.preventDefault(); 
        }
    });

   
    $(document).on('click', '.aj-actions-button', function(e) {
        e.stopPropagation();
        const $button = $(this);
        const $row = $button.closest('.aj-card-row');
        const $dropdown = $button.next('.aj-actions-dropdown');

        // --- Lógica para abrir o submenu para cima ---
        // Verifica se o menu está perto do final da viewport
        const windowHeight = $(window).height();
        const dropdownTop = $button.get(0).getBoundingClientRect().top;
        const spaceBelow = windowHeight - dropdownTop;

        // Se houver menos de 300px abaixo, abre para cima.
        // O valor 300px é uma estimativa da altura do menu + submenu.
        if (spaceBelow < 300) {
            $dropdown.addClass('aj-open-upward');
        } else {
            $dropdown.removeClass('aj-open-upward');
        }

        // Fecha outros menus e remove a classe das outras linhas
        $('.aj-actions-dropdown').not($dropdown).hide();
        $('.aj-card-row').removeClass('actions-menu-open');

        // Fecha a caixa de sugestões de sócios se estiver aberta
        if ($('#aj_socios_suggestions').is(':visible')) {
            $('#aj_socios_suggestions').hide();
        }

        // Alterna o menu atual e a classe na linha correspondente
        $dropdown.toggle();
        $row.toggleClass('actions-menu-open', $dropdown.is(':visible'));
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.aj-actions-container').length) {
            $('.aj-actions-dropdown').hide(); // Esconde o menu de ações
            $('.aj-card-row').removeClass('actions-menu-open'); // Remove a classe de todas as linhas
        }

        // Fecha a lista de sócios ao clicar fora do seu container
        // A condição verifica se o clique não foi no input E não foi na caixa de sugestões.
        if (!$(e.target).is('#aj_socios') && !$(e.target).closest('#aj_socios_suggestions').length) {
            $('#aj_socios_suggestions').hide();
        }
    });

    // --- Lógica para Criar Evento no Google Calendar ---
    $(document).on('click', '.aj-action-create-event', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $row = $(this).closest('tr');

        // Coleta dados da linha para o título do evento
        const assunto = $row.find('.column-assunto .row-title').text().trim();
        const socio = $row.find('.column-socio').text().trim();

        // Monta o título do evento
        let eventTitle = "Atendimento";
        if (assunto) eventTitle += `: ${assunto}`;
        if (socio) eventTitle += ` - ${socio}`;

        // Monta a URL do Google Calendar e abre em uma nova aba
        const googleCalendarUrl = `https://calendar.google.com/calendar/u/0/r/eventedit?text=${encodeURIComponent(eventTitle)}`;
        window.open(googleCalendarUrl, '_blank');
    });


    // --- Lógica para Excluir Atendimento ---
    $(document).on('click', '.aj-action-delete', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $link = $(this);
        const atendimentoId = $link.data('id');
        const $row = $link.closest('tr');

        // Coleta dados da linha para a mensagem de confirmação
        const id = $row.find('.column-id').text();
        const assunto = $row.find('.column-assunto .row-title').text();
        const socio = $row.find('.column-socio').text();
        const data = $row.find('.column-data').text();

        const confirmationMessage = `Deseja excluir o atendimento:\n\nID: ${id}\nAssunto: ${assunto}\nSócio: ${socio}\nData: ${data}`;

        if (confirm(confirmationMessage)) {
            $.ajax({
                url: ajaxurl, // ajaxurl é uma variável global do WordPress
                type: 'POST',
                data: {
                    action: 'aj_excluir_atendimento',
                    atendimento_id: atendimentoId,
                    _ajax_nonce: aj_object.delete_nonce // Usando o nonce que vamos adicionar
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(400, function() { $(this).remove(); });
                    } else {
                        alert('Erro ao excluir o atendimento: ' + response.data.message);
                    }
                }
            });
        }
    });



    $('.aj-nav-tab-wrapper .nav-tab').on('click', function() {
        const tab = $(this).data('tab');
        const url = $(this).data('url');

      
        $('.aj-nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.tab-content').removeClass('active');
        $('#tab-content-' + tab).addClass('active');

      
        if (history.pushState) {
            history.pushState({path: url}, '', url);
        }
    });



   
    if (typeof tinymce !== 'undefined') {
        tinymce.on('addeditor', function(event) {
           
        });
    }

   
    const form = $('#aj-main-form'); 
    const isNewForm = new URLSearchParams(window.location.search).has('action');

    if (isNewForm && form.length) {
        const formId = form.attr('id');
        const storageKey = 'aj_new_atendimento_data';

        
        function loadFormData() {
            const savedData = JSON.parse(localStorage.getItem(storageKey));
            if (savedData) {
                form.find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (name && savedData[name]) {
                        if ($(this).is(':checkbox')) {
                            $(this).prop('checked', savedData[name] === $(this).val()).trigger('change');
                        } else {
                            $(this).val(savedData[name]);
                        }
                    }
                });
                
                updateCheckboxState();
            }
        }

   
        function saveFormData() {
            const data = form.serializeArray().reduce((obj, item) => {
                obj[item.name] = item.value;
                return obj;
            }, {});
            localStorage.setItem(storageKey, JSON.stringify(data));
        }

        loadFormData();

       
        form.on('change keyup', 'input, select, textarea', saveFormData);

      
        form.on('submit', function() {
            localStorage.removeItem(storageKey);
        });

   
        $('.aj-btn-limpar').on('click', function() {
            localStorage.removeItem(storageKey);
          
        });

       
        if ($('.notice-success').length && localStorage.getItem(storageKey)) {
            localStorage.removeItem(storageKey);
        }
    }

    // --- LÓGICA PARA OBRIGAR OBSERVAÇÃO AO MUDAR STATUS ---
    const $mainForm = $('#aj-main-form');
    const atendimentoId = new URLSearchParams(window.location.search).get('id');

    // Só executa em páginas de edição de um atendimento existente
    if ($mainForm.length && atendimentoId) {
        const initialStatus = $('#aj_status').val();

        $mainForm.on('submit', function(e) {
            const currentStatus = $('#aj_status').val();

            // Verifica se o status foi alterado
            if (currentStatus !== initialStatus) {
                let observacoesContent = '';
                // Verifica se o editor TinyMCE está ativo para o campo
                if (typeof tinymce !== 'undefined' && tinymce.get('aj_observacoes_atendimento')) {
                    observacoesContent = tinymce.get('aj_observacoes_atendimento').getContent({ format: 'text' }).trim();
                } else {
                    observacoesContent = $('#aj_observacoes_atendimento').val().trim();
                }

                // Se o conteúdo das observações estiver vazio, impede o envio
                if (observacoesContent === '') {
                    e.preventDefault(); // Bloqueia o envio do formulário
                    alert('Ao alterar o status do atendimento, é obrigatório preencher o campo "Observações do atendimento".');
                    // Opcional: focar na aba e no editor
                    $('.nav-tab[data-tab="observacoes"]').trigger('click');
                }
            }
        });
    }

    // --- LÓGICA PARA GERAR RELATÓRIO ---
    $(document).on('click', '#aj-generate-report-btn', function() {
        const $button = $(this);
        const originalText = $button.html();
        $button.html('<span class="dashicons dashicons-update-alt spin"></span> Gerando...').prop('disabled', true);

        const formData = $('#aj-search-form').serialize();
        const requestData = formData + '&action=aj_gerar_relatorio&_ajax_nonce=' + aj_object.search_nonce;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: requestData,
            success: function(response) {
                if (response.success) {
                    generateReportHtml(response.data);
                } else {
                    alert('Erro ao gerar o relatório: ' + (response.data.message || 'Resposta inválida do servidor.'));
                }
            },
            error: function() {
                alert('Ocorreu um erro de comunicação ao gerar o relatório.');
            },
            complete: function() {
                $button.html(originalText).prop('disabled', false);
            }
        });
    });

    /**
     * Gera um PDF de texto real com os dados do relatório e abre em uma pré-visualização.
     * @param {object} reportData - Os dados do relatório (atendimentos e estatísticas).
     */
    function generateReportHtml(reportData) {
        const { data, stats } = reportData;
        const { jsPDF } = window.jspdf; // Importa o construtor do jsPDF

        // Cria um novo documento PDF no formato A4
        const doc = new jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a4'
        });

        // Encontra o item com a maior contagem em um objeto de contagem
        const findMax = (obj) => Object.keys(obj).length ? Object.entries(obj).reduce((a, b) => obj[a[0]] > obj[b[0]] ? a : b)[0] : 'N/A';

        // --- CABEÇALHO DO DOCUMENTO ---
        doc.setFontSize(18);
        doc.text('Relatório de Atendimentos', 14, 22);
        doc.setFontSize(11);
        doc.text(`Gerado em: ${new Date().toLocaleString('pt-BR')}`, 14, 29);

        // --- SEÇÃO DE RESUMO ---
        const resumoBody = [
            ['Total de Atendimentos', stats.total],
            ['Advogado com mais atendimentos', `${findMax(stats.advogado_counts)} (${stats.advogado_counts[findMax(stats.advogado_counts)] || 0})`],
            ['Horário de pico', `${findMax(stats.horario_counts)} (${stats.horario_counts[findMax(stats.horario_counts)] || 0})`],
            ['Tipo mais comum', `${findMax(stats.tipo_counts)} (${stats.tipo_counts[findMax(stats.tipo_counts)] || 0})`],
        ];

        // Adiciona a tabela de resumo
        doc.autoTable({
            startY: 40,
            head: [['Resumo Geral', '']],
            body: resumoBody,
            theme: 'striped',
            headStyles: { fillColor: [41, 128, 185] },
        });

        // Adiciona a lista de status separadamente
        const statusText = Object.entries(stats.status_counts).map(([status, count]) => `${status}: ${count}`).join(' | ');
        doc.autoTable({
            startY: doc.autoTable.previous.finalY + 2,
            head: [['Atendimentos por Status', '']],
            body: [[statusText]],
            theme: 'grid',
        });

        // --- TABELA PRINCIPAL DE ATENDIMENTOS ---
        const tableHeaders = ['ID', 'Assunto', 'Protocolo', 'Sócio(s)', 'Advogado(s)', 'Data', 'Status'];
        const tableBody = data.map(item => [
            item.id,
            item.assunto,
            item.protocolo,
            item.socios,
            item.advogados,
            item.data_formatada,
            item.status
        ]);

        doc.autoTable({
            startY: doc.autoTable.previous.finalY + 10,
            head: [tableHeaders],
            body: tableBody,
            theme: 'grid',
            headStyles: { fillColor: [41, 128, 185] },
            styles: { fontSize: 8 },
            columnStyles: {
                0: { cellWidth: 10 }, // ID
                1: { cellWidth: 'auto' }, // Assunto
                2: { cellWidth: 30 }, // Protocolo
                3: { cellWidth: 25 }, // Sócio
                4: { cellWidth: 25 }, // Advogado
                5: { cellWidth: 25 }, // Data
                6: { cellWidth: 25 }  // Status
            }
        });

        // --- GERAÇÃO DA PRÉ-VISUALIZAÇÃO ---
        const blobUrl = doc.output('bloburl');
        const filename = `relatorio_atendimentos_${new Date().toISOString().slice(0,10)}.pdf`;

        const previewHtml = `
                <!DOCTYPE html>
                <html lang="pt-BR">
                <head>
                    <meta charset="UTF-8">
                    <title>Pré-visualização do Relatório</title>
                    <style>
                        body { margin: 0; padding: 0; font-family: sans-serif; background-color: #525659; display: flex; flex-direction: column; height: 100vh; }
                        .preview-header { background-color: #32373c; color: white; padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10; }
                        .preview-header h2 { margin: 0; font-size: 1.2em; }
                        .preview-actions button { background-color: #3498db; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer; font-size: 14px; margin-left: 10px; }
                        .preview-actions button:hover { background-color: #2980b9; }
                        .preview-actions .print-btn { background-color: #95a5a6; }
                        .preview-actions .print-btn:hover { background-color: #7f8c8d; }
                        .preview-body { flex-grow: 1; }
                        iframe { width: 100%; height: 100%; border: none; }
                    </style>
                </head>
                <body>
                    <div class="preview-header">
                        <h2>Pré-visualização do Relatório</h2>
                        <div class="preview-actions">
                            <button id="print-btn" class="print-btn">Imprimir</button>
                            <button id="download-btn">Baixar PDF</button>
                        </div>
                    </div>
                    <div class="preview-body">
                        <iframe src="${blobUrl}" type="application/pdf"></iframe>
                    </div>
                    <script>
                        document.getElementById('download-btn').addEventListener('click', function() {
                            const link = document.createElement('a');
                            link.href = '${blobUrl}';
                            link.download = '${filename}';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        });
                        document.getElementById('print-btn').addEventListener('click', function() {
                            const iframe = document.querySelector('iframe');
                            iframe.contentWindow.print();
                        });
                    <\/script>
                </body>
                </html>
            `;

        const previewWindow = window.open('', '_blank');
        previewWindow.document.write(previewHtml);
        previewWindow.document.close();
    }

    // --- LÓGICA DA LISTA DE ATENDIMENTOS (PESQUISA HÍBRIDA) ---

    var currentResults = []; // Armazena os resultados da última busca (avançada ou inicial)
    var currentPage = 1; // Adicionado para controlar a página atual

    /**
     * Renderiza a lista de atendimentos na tabela.
     * @param {Array} atendimentos - Array de objetos de atendimento para exibir.
     */
    function renderAtendimentos(atendimentos) {
        const $tbody = $('#the-list');
        $tbody.empty(); // Limpa a tabela antes de adicionar novos resultados

        if (atendimentos && atendimentos.length > 0) {
            atendimentos.forEach(function(item) {
                const statusClass = 'aj-status-' + item.status.toLowerCase().replace(/ /g, '-');
                const rowHtml = `
                    <tr class="aj-card-row">
                        <td class="socio column-socio" data-label="Sócio">${item.socios}</td>
                        <td class="assunto column-assunto" data-label="Assunto">
                            <strong><a class="row-title" href="${item.edit_url}">${item.assunto}</a></strong>
                        </td>
                        <td class="advogado column-advogado" data-label="Advogado">${item.advogados}</td>
                        <td class="protocolo column-protocolo" data-label="Protocolo">${item.protocolo}</td>
                        <td class="data column-data" data-label="Data">${item.data_formatada}</td>
                        <td class="status column-status" data-label="Status">
                            <span class="aj-status-badge ${statusClass}">${item.status}</span>
                        </td>
                        <td class="actions column-actions" data-label="Ações">
                            <div class="aj-actions-container">
                                <button type="button" class="aj-actions-button dashicons dashicons-ellipsis" title="Ações"></button>
                                <div class="aj-actions-dropdown" style="display: none;">
                                    <ul>
                                        <li><a href="#" class="aj-action-create-event"><span class="dashicons dashicons-calendar-alt"></span> Criar evento</a></li>
                                        <li><a href="#"><span class="dashicons dashicons-redo"></span> Converter em processo</a></li>
                                        <li class="aj-submenu-container">
                                            <a href="#"><span class="dashicons dashicons-portfolio"></span> Documentos <span class="dashicons dashicons-arrow-left-alt2"></span></a>
                                            <div class="aj-actions-submenu">
                                                <ul>
                                                    <li><a href="#"><span class="dashicons dashicons-printer"></span> Resumo</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-text-page"></span> Comprovante</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-whatsapp"></span> Enviar Via Whatsapp</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-admin-users"></span> Procuração</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-money-alt"></span> Contratos Honorarios</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-text-page"></span> Declaração Pobreza</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-admin-home"></span> Contrato AJI Trab.</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-groups"></span> Contrato AJI Prev.</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="aj-submenu-container">
                                            <a href="#"><span class="dashicons dashicons-admin-generic"></span> Ações <span class="dashicons dashicons-arrow-left-alt2"></span></a>
                                            <div class="aj-actions-submenu">
                                                <ul>
                                                    <li><a href="${item.edit_url}"><span class="dashicons dashicons-edit"></span> Editar</a></li>
                                                    <li><a href="${item.view_url}" class="aj-action-view"><span class="dashicons dashicons-visibility"></span> Visualizar</a></li>
                                                    <li><a href="#" class="aj-action-delete" data-id="${item.id}"><span class="dashicons dashicons-trash"></span> Excluir</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
                $tbody.append(rowHtml);
            });
        } else {
            $tbody.append('<tr class="no-items"><td class="colspanchange" colspan="7">Nenhum atendimento encontrado.</td></tr>');
        }
    }

    /**
     * Renderiza os controles de paginação.
     * @param {number} totalItems - O número total de itens.
     * @param {number} itemsPerPage - Itens por página.
     * @param {number} currentPage - A página atual.
     */
    function renderPagination(totalItems, itemsPerPage, currentPage) {
        const $paginationContainer = $('#aj-pagination-container');
        $paginationContainer.empty();

        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (totalPages <= 1) {
            return; // Não mostra paginação se só tem uma página
        }

        let paginationHtml = '<div class="aj-pagination">';

        // Seta "Anterior"
        if (currentPage > 1) {
            paginationHtml += `<a href="#" class="aj-page-nav aj-page-prev" data-page="${currentPage - 1}" title="Página Anterior"><span class="dashicons dashicons-arrow-left-alt2"></span></a>`;
        } else {
            paginationHtml += `<span class="aj-page-nav aj-page-disabled"><span class="dashicons dashicons-arrow-left-alt2"></span></span>`;
        }

        // Indicador da página atual
        paginationHtml += `<span class="aj-page-current">${currentPage}</span>`;

        // Seta "Próximo"
        if (currentPage < totalPages) {
            paginationHtml += `<a href="#" class="aj-page-nav aj-page-next" data-page="${currentPage + 1}" title="Próxima Página"><span class="dashicons dashicons-arrow-right-alt2"></span></a>`;
        } else {
            paginationHtml += `<span class="aj-page-nav aj-page-disabled"><span class="dashicons dashicons-arrow-right-alt2"></span></span>`;
        }

        paginationHtml += '</div>';
        $paginationContainer.html(paginationHtml);
    }

    /**
     * Realiza a busca AVANÇADA via AJAX e atualiza a tabela.
     * @param {number} page - O número da página a ser buscada.
     * @param {boolean} showNotice - Se a notificação de resultados deve ser exibida.
     */
    function performAdvancedSearch(page = 1, showNotice = true) {
        currentPage = page; // Atualiza a página atual

        // Efeito de loading
        $('#the-list').css('opacity', 0.5);

        // Coleta todos os dados do formulário
        const formData = $('#aj-search-form').serialize();
        // Adiciona o parâmetro 'page' à requisição
        const requestData = formData + '&action=aj_buscar_atendimentos&_ajax_nonce=' + aj_object.search_nonce + '&page=' + currentPage;

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: requestData,
            success: function(response) {
                if (response.success && typeof response.data === 'object') {
                    // A busca simples (keyup) agora é desativada quando há paginação,
                    // pois ela só funciona no conjunto de dados visível.
                    // Para uma experiência consistente, toda busca irá ao servidor.
                    currentResults = response.data.data; // Os resultados estão em response.data.data
                    renderAtendimentos(currentResults);
                    // Renderiza a paginação com base no total de itens e na página atual
                    renderPagination(response.data.total, 8, currentPage);

                    if (showNotice) {
                        // Exibe a notificação de resultados
                        const $notice = $('#aj-results-notice');
                        const message = `${response.data.total} resultado(s) encontrado(s).`; // Usa response.data.total
                        $notice.text(message).fadeIn(); // Efeito fadeIn

                        // Oculta a notificação após 3 segundos
                        setTimeout(function() {
                            $notice.fadeOut('slow'); // Efeito fadeOut
                        }, 3000);
                    }
                } else {
                    alert('Erro ao buscar atendimentos: ' + response.data.message);
                }
            },
            error: function() {
                alert('Ocorreu um erro de comunicação. Tente novamente.');
            },
            complete: function() {
                // Remove efeito de loading
                $('#the-list').css('opacity', 1);
            }
        });
    }

    // --- EVENTOS ---

    // Busca AVANÇADA (ao submeter o formulário ou ao clicar nos botões de paginação)
    $('#aj-search-form').on('submit', function(e) {
        e.preventDefault();
        performAdvancedSearch(1, true); // Nova busca, mostra a notificação.
    });

    // A busca simples (keyup) agora também vai ao servidor para consistência com a paginação.
    $('#aj-search-input').on('keyup', function() {
        // Para evitar muitas requisições, poderíamos adicionar um "debounce" aqui,
        // mas por simplicidade, vamos manter a busca a cada tecla.
        // A busca principal agora é feita pelo botão ou Enter no formulário.
    });

    // Limpar Filtros
    $('.aj-clear-filters-btn').on('click', function() {
        $('#aj-search-form')[0].reset();
        $('#aj-results-notice').hide();
        performAdvancedSearch(1, true); // Nova busca, mostra a notificação.
    });

    // Evento para cliques na paginação
    $(document).on('click', '.aj-page-nav', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        performAdvancedSearch(page, false); // Troca de página, não mostra a notificação.
        $('html, body').animate({ scrollTop: $('.aj-list-page-wrapper').offset().top }, 300); // Rola para o topo da lista
    });

    // --- INICIALIZAÇÃO ---
    $(window).on('load', function() {
        // Realiza a busca inicial ao carregar a página (que por padrão trará o mês atual)
        performAdvancedSearch();
    });

});
