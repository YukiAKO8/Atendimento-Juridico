jQuery(document).ready(function($) {

    // Verifica se o objeto aj_form_data, passado pelo PHP, existe.
    if (typeof aj_form_data === 'undefined') {
        console.error('Dados do formulário (aj_form_data) não foram localizados.');
        return;
    }

    // --- Início: Lógica para busca interativa de sócios (Pré-carregada) ---
    var todosOsSocios = []; // Array para armazenar a lista completa de sócios
    var sociosCarregados = false; // Flag para controlar se os sócios já foram carregados
    var sociosSuggestionsBox = $('#aj_socios_suggestions'); // Renomeado para clareza
    var sociosInput = $('#aj_socios');

    // 1. Carrega todos os sócios via AJAX assim que a página estiver pronta (em background)
    function carregarSocios() {
        $.ajax({
            url: ajaxurl, // ajaxurl é uma variável global do WordPress
            type: 'POST',
            data: {
                action: 'aj_get_socios', // Ação correta para buscar sócios
                _ajax_nonce: aj_form_data.nonce
            },
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    todosOsSocios = res.data; // Armazena a lista completa
                    sociosCarregados = true;
                }
            }
        });
    }
    carregarSocios(); // Executa a função de carregamento

    // 2. Ao focar no campo, exibe a lista pré-carregada
    sociosInput.on('focus', function() {
        if (sociosCarregados) {
            filtrarEExibirSocios($(this).val()); // Isso mostrará todos se o input estiver vazio
        } else {
            sociosSuggestionsBox.html('<div class="suggestion-item">Carregando...</div>').show();
        }
    });

    // 3. Ao digitar, filtra a lista já carregada
    sociosInput.on('input', function() { // Isso é para o comportamento de autocompletar
        var searchTerm = $(this).val();
        filtrarEExibirSocios(searchTerm);
    });

    // Função para filtrar e exibir os sócios
    function filtrarEExibirSocios(termo) {
        var sociosFiltrados = todosOsSocios;
        
        if (termo.length >= 3) {
            var termoLower = termo.toLowerCase();
            sociosFiltrados = todosOsSocios.map(function(socio) {
                var nomeLower = socio.nome.toLowerCase();
                var score = 0;
                if (nomeLower.startsWith(termoLower)) {
                    score = 2; // Maior prioridade se começar com o termo
                } else if (nomeLower.includes(termoLower)) {
                    score = 1; // Menor prioridade se apenas contiver o termo
                }
                return { ...socio, score: score };
            }).filter(function(socio) {
                return socio.score > 0;
            }).sort(function(a, b) {
                return b.score - a.score; // Ordena por score (maior primeiro)
            });
        } else {
            // Se o termo for menor que 3, simplesmente usamos a lista completa sem filtro.
            // A função exibirSocios será chamada com a lista completa.
            sociosFiltrados = todosOsSocios;
        }
        
        exibirSocios(sociosFiltrados);
    }

    // Função para popular a caixa de sugestões
    function exibirSocios(lista) { // Para autocompletar de sócios
        sociosSuggestionsBox.empty();
        if (lista.length > 0) {
            $.each(lista, function(index, socio) {
                sociosSuggestionsBox.append('<div class="suggestion-item" data-name="' + esc_attr(socio.nome) + '">' + esc_html(socio.nome) + '</div>');
            });
        } else {
            sociosSuggestionsBox.html('<div class="suggestion-item">Nenhum sócio encontrado.</div>');
        }
        sociosSuggestionsBox.show();
    }

    // 4. Ao clicar em uma sugestão, preenche o campo e esconde a lista
    $(document).on('click', '#aj_socios_suggestions .suggestion-item', function() {
        var selectedName = $(this).data('name');
        if (selectedName) {
            $('#aj_socios').val(selectedName); // Preenche o campo com o nome clicado
            sociosSuggestionsBox.hide(); // Esconde a caixa
        }
    });

    // 5. Ao clicar fora do campo, esconde a lista
    $(document).on('click', function(e) {
        // Verifica se o clique foi fora do input de sócios e sua caixa de sugestões
        if (!$(e.target).closest('.aj-socio-wrapper').length) {
            sociosSuggestionsBox.hide();
        }
        // Adicionado para fechar o dropdown de advogados também
        if (!$(e.target).closest('.aj-advogado-wrapper').length) {
            $('#aj_advogados_suggestions').hide();
        }
    });
    // --- Fim: Lógica para busca interativa de sócios ---

    // --- Início: Lógica para busca interativa de advogados (Autocompletar) ---
    var todosOsAdvogados = [];
    var advogadosCarregados = false;
    var advogadosInput = $('#aj_advogados');
    var advogadosSuggestionsBox = $('#aj_advogados_suggestions');

    function carregarAdvogados() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aj_buscar_advogados',
                _ajax_nonce: (typeof aj_vars !== 'undefined') ? aj_vars.nonce : aj_form_data.nonce
            },
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    todosOsAdvogados = res.data;
                    advogadosCarregados = true;
                }
            }
        });
    }
    carregarAdvogados();

    // Adiciona o evento de clique na seta para mostrar/esconder as sugestões
    $(document).on('click', '.aj-toggle-advogados', function(e) {
        e.stopPropagation(); // Impede que o clique feche o menu imediatamente
        if (advogadosSuggestionsBox.is(':visible')) {
            advogadosSuggestionsBox.hide();
        } else if (advogadosCarregados) {
            filtrarEExibirAdvogados(''); // Mostra todos os advogados
        }
    });

    advogadosInput.on('focus', function() {
        if (advogadosCarregados) {
            filtrarEExibirAdvogados($(this).val());
        } else {
            advogadosSuggestionsBox.html('<div class="suggestion-item">Carregando...</div>').show();
        }
    });

    advogadosInput.on('input', function() {
        var searchTerm = $(this).val();
        filtrarEExibirAdvogados(searchTerm);
    });

    function filtrarEExibirAdvogados(termo) {
        var termoLower = termo.toLowerCase();
        var advogadosFiltrados = todosOsAdvogados.filter(function(adv) {
            return adv.toLowerCase().includes(termoLower);
        });
        exibirAdvogados(advogadosFiltrados);
    }

    function exibirAdvogados(lista) {
        advogadosSuggestionsBox.empty();
        if (lista.length > 0) {
            $.each(lista, function(index, adv) {
                advogadosSuggestionsBox.append('<div class="suggestion-item" data-name="' + esc_attr(adv) + '">' + esc_html(adv) + '</div>');
            });
        } else {
            advogadosSuggestionsBox.append('<div class="suggestion-item">Nenhum advogado encontrado.</div>');
        }
        advogadosSuggestionsBox.show();
    }

    $(document).on('click', '#aj_advogados_suggestions .suggestion-item', function() {
        var selectedName = $(this).data('name');
        if (selectedName) {
            $('#aj_advogados').val(selectedName);
            advogadosSuggestionsBox.hide();
        }
    });
    // --- Fim: Lógica para busca interativa de advogados ---

    // Funções auxiliares para escapar caracteres e evitar XSS
    function esc_attr(str) {
        if (typeof str !== 'string') return '';
        return str.replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function esc_html(str) {
        if (typeof str !== 'string') return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
});