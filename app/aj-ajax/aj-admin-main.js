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

    // --- LÓGICA DA LISTA DE ATENDIMENTOS (CLIENT-SIDE) ---

    var allAtendimentos = []; // Armazena todos os atendimentos carregados da página
    var displayedAtendimentos = []; // Armazena os atendimentos atualmente filtrados e exibidos
    var itemsPerPage = 8; // Quantidade de itens por página
    var currentPage = 1; // Página atual

    /**
     * Renderiza os atendimentos para a página especificada.
     */
    function renderPage(page) {
        currentPage = page;
        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;

        const $tbody = $('#the-list');
        let $noItemsRow = $tbody.find('.no-items');

        // Esconde todos os atendimentos (todos os elementos que foram carregados inicialmente)
        allAtendimentos.forEach(function(item) {
            item.element.hide();
        });

        if (displayedAtendimentos.length === 0) {
            if ($noItemsRow.length === 0) {
                $tbody.append('<tr class="no-items"><td class="colspanchange" colspan="8">Nenhum atendimento encontrado.</td></tr>');
                $noItemsRow = $tbody.find('.no-items'); // Get reference to the newly added row
            }
            $noItemsRow.show();
        } else {
            $noItemsRow.hide();
        }

        // Mostra apenas as linhas para a página atual
        var pageItems = displayedAtendimentos.slice(startIndex, endIndex);
        pageItems.forEach(function(item) {
            item.element.show();
        });

        updatePagination(currentPage); // Corrected function call
    }

    /**
     * Renderiza os controles de paginação.
     */
    function updatePagination(currentPage) {
        var totalItems = displayedAtendimentos.length;
        var totalPages = Math.ceil(totalItems / itemsPerPage);
        var $paginationContainer = $('#aj-pagination-container');
        var paginationHtml = '';

        if (totalPages > 1) {
            paginationHtml += '<div class="aj-pagination">';
            if (currentPage > 1) {
                paginationHtml += `<a href="#" class="aj-page-nav prev-page" data-page="${currentPage - 1}">&lt;</a>`;
            }
            paginationHtml += `<span class="aj-page-current">${currentPage}</span>`;
            if (currentPage < totalPages) {
                paginationHtml += `<a href="#" class="aj-page-nav next-page" data-page="${currentPage + 1}">&gt;</a>`;
            }
            paginationHtml += '</div>';
        }

        $paginationContainer.html(paginationHtml);
    }

    /**
     * Atualiza a lista de exibição com base nos filtros e renderiza a primeira página.
     */
    function updateDisplay(filteredItems) {
        displayedAtendimentos = filteredItems;
        renderPage(1);
    }

    /**
     * Filtra os atendimentos com base nos valores dos campos de busca.
     */
    function filterAndDisplay() {
        // Efeito de loading
        $('#the-list').css('opacity', 0.5);

        // Coleta os valores dos filtros
        const searchTerm = $('#aj-search-input').val().toLowerCase();
        const dataInicio = $('#adv_data_inicio').val();
        const dataFim = $('#adv_data_fim').val();
        const status = $('#adv_status').val();
        const tipo = $('#adv_tipo').val();
        const advogado = $('#adv_advogado').val().toLowerCase();
        const socio = $('#adv_socio').val().toLowerCase();

        const isAdvancedSearch = dataInicio || dataFim || status || tipo || advogado || socio;

        let filtered = allAtendimentos.filter(function(item) {
            const itemData = item.data;

            // Filtro de busca simples
            if (searchTerm && !(
                itemData.assunto.toLowerCase().includes(searchTerm) ||
                itemData.socios.toLowerCase().includes(searchTerm) ||
                itemData.protocolo.toLowerCase().includes(searchTerm) ||
                itemData.advogados.toLowerCase().includes(searchTerm)
            )) {
                return false;
            }

            // Filtros avançados
            if (dataInicio && itemData.data_atendimento < dataInicio + ' 00:00:00') return false;
            if (dataFim && itemData.data_atendimento > dataFim + ' 23:59:59') return false;
            if (status && itemData.status !== status) return false;
            if (tipo && itemData.tipo_atendimento !== tipo) return false;
            if (advogado && !itemData.advogados.toLowerCase().includes(advogado)) return false;
            if (socio && !itemData.socios.toLowerCase().includes(socio)) return false;

            return true;
        });

        // Exibe a notificação de resultados
        const $notice = $('#aj-results-notice');
        if (isAdvancedSearch || searchTerm) {
            const message = `${filtered.length} resultado(s) encontrado(s).`;
            $notice.text(message).show();
        } else {
            $notice.hide();
        }

        updateDisplay(filtered);

        // Remove efeito de loading
        $('#the-list').css('opacity', 1);
    }

    // --- EVENTOS ---

    // Paginação
    $(document).on('click', '.aj-page-nav', function(e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        renderPage(page);
    });

    // Busca (simples e avançada)
    $('#aj-search-form').on('submit', function(e) {
        e.preventDefault();
        filterAndDisplay();
    });

    // Limpar Filtros
    $('.aj-clear-filters-btn').on('click', function() {
        $('#aj-search-form')[0].reset();
        $('#aj-results-notice').hide();
        
        // Filtra pelo mês atual como padrão
        const now = new Date();
        const currentMonth = now.getMonth();
        const currentYear = now.getFullYear();

        let monthFiltered = allAtendimentos.filter(function(item) {
            const itemDate = new Date(item.data.data_atendimento);
            return itemDate.getMonth() === currentMonth && itemDate.getFullYear() === currentYear;
        });

        updateDisplay(monthFiltered);
    });

    // --- INICIALIZAÇÃO ---
    $(window).on('load', function() {
        // 1. Carrega todos os atendimentos do DOM para o array JS
        $('.aj-card-row').each(function(index) {
            const element = $(this);
            allAtendimentos.push({
                element: element,
                data: element.data()
            });
        });

        // 2. Dispara o "Limpar Filtros" para aplicar o filtro inicial do mês corrente
        $('.aj-clear-filters-btn').trigger('click');
    });

});