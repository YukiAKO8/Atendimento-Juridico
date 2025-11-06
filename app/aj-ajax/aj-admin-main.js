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
});