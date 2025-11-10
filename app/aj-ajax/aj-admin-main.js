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

   
    $('.aj-actions-button').on('click', function(e) {
        e.stopPropagation(); 
       
        $('.aj-actions-dropdown').not($(this).next('.aj-actions-dropdown')).hide();
  
        $(this).next('.aj-actions-dropdown').toggle();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.aj-actions-container').length) {
            $('.aj-actions-dropdown').hide();
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
                    _ajax_nonce: aj_object.nonce // Usando o nonce que vamos adicionar
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
});