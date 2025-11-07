jQuery(document).ready(function($) {

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
    
    // Evento de clique no container do checkbox
    $('.form-group-checkbox').on('click', function() {
        var checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
    });
    // Evento de mudança no checkbox (necessário para o clique funcionar)
    $('#aj_entrada_processo').on('change', function() {
        updateCheckboxState();
    });
    // Garante que o estado inicial esteja correto ao carregar a página
    updateCheckboxState();

    // --- Confirmação antes de limpar ou cancelar o formulário ---
    $('.aj-btn-limpar').on('click', function(e) {
        if (!confirm('Tem certeza que deseja limpar o formulário? Todos os dados serão perdidos.')) {
            e.preventDefault(); // Impede a ação padrão do botão
        }
    });

    $('.aj-btn-cancelar').on('click', function(e) {
        if (!confirm('Tem certeza que deseja cancelar? As alterações não serão salvas.')) {
            e.preventDefault(); // Impede a ação padrão do link
        }
    });

    // --- Lógica para o menu de ações da lista de atendimentos ---
    $('.aj-actions-button').on('click', function(e) {
        e.stopPropagation(); // Impede que o clique se propague para o document
        // Fecha outros dropdowns abertos
        $('.aj-actions-dropdown').not($(this).next('.aj-actions-dropdown')).hide();
        // Alterna o dropdown atual
        $(this).next('.aj-actions-dropdown').toggle();
    });

    // Fecha o dropdown se clicar em qualquer lugar fora dele
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.aj-actions-container').length) {
            $('.aj-actions-dropdown').hide();
        }
    });


    // --- Lógica para navegação por abas sem recarregar a página ---
    $('.aj-nav-tab-wrapper .nav-tab').on('click', function() {
        const tab = $(this).data('tab');
        const url = $(this).data('url');

        // Atualiza a classe ativa nas abas
        $('.aj-nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        // Mostra/esconde o conteúdo da aba
        $('.tab-content').removeClass('active');
        $('#tab-content-' + tab).addClass('active');

        // Atualiza a URL no navegador sem recarregar a página
        if (history.pushState) {
            history.pushState({path: url}, '', url);
        }
    });


    // --- Preenchimento do WP Editor ---
    // O wp_editor pode precisar de um tempo para inicializar.
    // Esta verificação garante que o conteúdo seja inserido após a inicialização.
    if (typeof tinymce !== 'undefined') {
        tinymce.on('addeditor', function(event) {
            // O conteúdo já é passado via PHP, mas isso pode ajudar em casos de reinicialização via AJAX.
        });
    }

    // --- Lógica para persistir dados do formulário em localStorage ao criar novo atendimento ---
    const form = $('#aj-main-form'); // O formulário principal agora tem o ID aj-main-form
    const isNewForm = new URLSearchParams(window.location.search).has('action');

    if (isNewForm && form.length) {
        const formId = form.attr('id');
        const storageKey = 'aj_new_atendimento_data';

        // Função para carregar dados do localStorage nos campos
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
                // Atualiza o estado do checkbox customizado
                updateCheckboxState();
            }
        }

        // Função para salvar dados no localStorage
        function saveFormData() {
            const data = form.serializeArray().reduce((obj, item) => {
                obj[item.name] = item.value;
                return obj;
            }, {});
            localStorage.setItem(storageKey, JSON.stringify(data));
        }

        // Carrega os dados ao iniciar a página
        loadFormData();

        // Salva os dados sempre que um campo for alterado
        form.on('change keyup', 'input, select, textarea', saveFormData);

        // Limpa o localStorage quando o formulário é submetido (botão Salvar)
        form.on('submit', function() {
            localStorage.removeItem(storageKey);
        });

        // Limpa o localStorage quando o botão Limpar é clicado
        $('.aj-btn-limpar').on('click', function() {
            localStorage.removeItem(storageKey);
            // O 'reset' do botão já limpa os campos visíveis, mas o localStorage garante a persistência.
            // Se o usuário clicar em limpar e depois navegar, os campos estarão vazios.
        });

        // Se a página carregou com a notificação de sucesso (após um salvamento),
        // o localStorage já foi limpo no submit, mas esta é uma verificação extra.
        // Removemos o redirecionamento daqui, pois o PHP já cuida disso.
        if ($('.notice-success').length && localStorage.getItem(storageKey)) {
            localStorage.removeItem(storageKey);
        }
    }
});