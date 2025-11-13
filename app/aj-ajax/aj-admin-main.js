jQuery(document).ready(function($) {

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

        // Fecha outros menus e remove a classe das outras linhas
        $('.aj-actions-dropdown').not($dropdown).hide();
        $('.aj-card-row').removeClass('actions-menu-open');

        // Alterna o menu atual e a classe na linha correspondente
        $dropdown.toggle();
        $row.toggleClass('actions-menu-open', $dropdown.is(':visible'));
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.aj-actions-container').length) {
            $('.aj-actions-dropdown').hide();
            $('.aj-card-row').removeClass('actions-menu-open'); // Remove a classe de todas as linhas
        }
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
                        <td class="id column-id" data-label="ID">${item.id}</td>
                        <td class="assunto column-assunto" data-label="Assunto">
                            <strong><a class="row-title" href="${item.edit_url}">${item.assunto}</a></strong>
                        </td>
                        <td class="protocolo column-protocolo" data-label="Protocolo">${item.protocolo}</td>
                        <td class="advogado column-advogado" data-label="Advogado">${item.advogados}</td>
                        <td class="socio column-socio" data-label="Sócio">${item.socios}</td>
                        <td class="data column-data" data-label="Data">${item.data_formatada}</td>
                        <td class="status column-status" data-label="Status">
                            <span class="aj-status-badge ${statusClass}">${item.status}</span>
                        </td>
                        <td class="actions column-actions" data-label="Ações">
                            <div class="aj-actions-container">
                                <button class="aj-actions-button dashicons dashicons-ellipsis"></button>
                                <div class="aj-actions-dropdown" style="display: none;">
                                    <ul>
                                        <li><a href="#"><span class="dashicons dashicons-calendar-alt"></span> Criar evento</a></li>
                                        <li><a href="#"><span class="dashicons dashicons-redo"></span> Converter em processo</a></li>
                                        <li class="aj-submenu-container">
                                            <a href="#"><span class="dashicons dashicons-portfolio"></span> Documentos <span class="dashicons dashicons-arrow-left-alt2"></span></a>
                                            <div class="aj-actions-submenu">
                                                <ul>
                                                    <li><a href="#"><span class="dashicons dashicons-printer"></span> Resumo</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-text-page"></span> Comprovante</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-whatsapp"></span> Enviar Via Whatsapp</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-admin-users"></span> Procuração</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-businessman"></span> Procuração Advogado</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-money-alt"></span> Contratos Honorarios</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-text-page"></span> Declaração Pobreza</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-admin-home"></span> Declaração Residencia</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-groups"></span> Acordo Extrajudicial</a></li>
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
            $tbody.append('<tr class="no-items"><td class="colspanchange" colspan="8">Nenhum atendimento encontrado.</td></tr>');
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
            paginationHtml += `<a href="#" class="aj-page-nav aj-page-prev" data-page="${currentPage - 1}"><span class="dashicons dashicons-arrow-left-alt2"></span></a>`;
        } else {
            paginationHtml += `<span class="aj-page-nav aj-page-disabled"><span class="dashicons dashicons-arrow-left-alt2"></span></span>`;
        }

        // Indicador da página atual
        paginationHtml += `<span class="aj-page-current">${currentPage}</span>`;

        // Seta "Próximo"
        if (currentPage < totalPages) {
            paginationHtml += `<a href="#" class="aj-page-nav aj-page-next" data-page="${currentPage + 1}"><span class="dashicons dashicons-arrow-right-alt2"></span></a>`;
        } else {
            paginationHtml += `<span class="aj-page-nav aj-page-disabled"><span class="dashicons dashicons-arrow-right-alt2"></span></span>`;
        }

        paginationHtml += '</div>';
        $paginationContainer.html(paginationHtml);
    }

    /**
     * Realiza a busca AVANÇADA via AJAX e atualiza a tabela.
     * @param {number} page - O número da página a ser buscada.
     */
    function performAdvancedSearch(page = 1) {
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

                    // Exibe a notificação de resultados
                    const $notice = $('#aj-results-notice');
                    const message = `${response.data.total} resultado(s) encontrado(s).`; // Usa response.data.total
                    $notice.text(message).fadeIn(); // Efeito fadeIn

                    // Oculta a notificação após 5 segundos
                    setTimeout(function() {
                        $notice.fadeOut('slow'); // Efeito fadeOut
                    }, 3000); // 3000 milissegundos = 3 segundos
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
        performAdvancedSearch(1); // Sempre volta para a primeira página ao fazer uma nova busca
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
        performAdvancedSearch(1); // Realiza a busca padrão no servidor
    });

    // Evento para cliques na paginação
    $(document).on('click', '.aj-page-nav', function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        performAdvancedSearch(page);
        $('html, body').animate({ scrollTop: $('.aj-list-page-wrapper').offset().top }, 300); // Rola para o topo da lista
    });

    // --- INICIALIZAÇÃO ---
    $(window).on('load', function() {
        // Realiza a busca inicial ao carregar a página (que por padrão trará o mês atual)
        performAdvancedSearch();
    });

});