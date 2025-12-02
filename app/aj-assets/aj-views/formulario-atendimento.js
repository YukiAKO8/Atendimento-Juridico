jQuery(document).ready(function($) {

    // Verifica se o objeto aj_form_data, passado pelo PHP, existe.
    if (typeof aj_form_data === 'undefined') {
        console.error('Dados do formulário (aj_form_data) não foram localizados.');
        return;
    }

    // --- Início: Lógica para busca interativa de sócios (Pré-carregada) ---
    var todosOsSocios = []; // Array para armazenar a lista completa de sócios
    var sociosCarregados = false; // Flag para controlar se os sócios já foram carregados
    var suggestionsBox = $('#aj_socios_suggestions');
    var sociosInput = $('#aj_socios');

    // 1. Carrega todos os sócios via AJAX assim que a página estiver pronta (em background)
    function carregarSocios() {
        $.ajax({
            url: ajaxurl, // ajaxurl é uma variável global do WordPress
            type: 'POST',
            data: {
                action: 'aj_buscar_socios',
                q: '',
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
            filtrarEExibirSocios($(this).val());
        } else {
            suggestionsBox.html('<div class="suggestion-item">Carregando...</div>').show();
        }
    });

    // 3. Ao digitar, filtra a lista já carregada
    sociosInput.on('input', function() {
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
    function exibirSocios(lista) {
        suggestionsBox.empty();
        if (lista.length > 0) {
            $.each(lista, function(index, socio) {
                suggestionsBox.append('<div class="suggestion-item" data-name="' + esc_attr(socio.nome) + '">' + esc_html(socio.nome) + '</div>');
            });
        } else {
            suggestionsBox.html('<div class="suggestion-item">Nenhum sócio encontrado.</div>');
        }
        suggestionsBox.show();
    }

    // 4. Ao clicar em uma sugestão, preenche o campo e esconde a lista
    $(document).on('click', '#aj_socios_suggestions .suggestion-item', function() {
        var selectedName = $(this).data('name');
        if (selectedName) {
            $('#aj_socios').val(selectedName); // Preenche o campo com o nome clicado
            $('#aj_socios_suggestions').hide(); // Esconde a caixa
        }
    });

    // 5. Ao clicar fora do campo, esconde a lista
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.form-group').length) {
            suggestionsBox.hide();
        }
    });
    // --- Fim: Lógica para busca interativa de sócios ---

    // Ao clicar no botão "Gerar Relatório do Sócio (PDF)"
    $(document).on('click', '.aj-btn-relatorio-socio-pdf', function(e) {
        e.preventDefault();
        const socioNome = $(this).data('socio');
        if (!socioNome) {
            alert('Nome do sócio não encontrado.');
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'aj_relatorio_socio_pdf',
                socio: socioNome,
                _ajax_nonce: aj_form_data.nonce
            },
            success: function(res) {
                if (res.success) {
                    gerarPDFSocio(res.data);
                } else {
                    alert('Erro ao gerar relatório: ' + res.data);
                }
            },
            error: function() {
                alert('Erro de conexão ao gerar relatório.');
            }
        });
    });

    // Ao clicar no botão "Gerar Relatório do Atendimento (PDF)"
    $(document).on('click', '.aj-btn-relatorio-atendimento-pdf', function(e) {
        e.preventDefault();
        const atendimentoId = $(this).data('atendimento-id');
        if (!atendimentoId) {
            alert('ID do atendimento não encontrado.');
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'aj_relatorio_atendimento_pdf',
                atendimento_id: atendimentoId,
                _ajax_nonce: aj_form_data.nonce
            },
            success: function(res) {
                if (res.success) {
                    gerarPDFAtendimento(res.data);
                } else {
                    alert('Erro ao gerar relatório: ' + res.data);
                }
            },
            error: function() {
                alert('Erro de conexão ao gerar relatório do atendimento.');
            }
        });
    });

    /**
     * Gera o PDF para o relatório do sócio.
     * @param {object} data - Os dados recebidos do AJAX.
     */
    function gerarPDFSocio(data) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(18);
        doc.text('Relatório de Atendimentos - ' + data.socio.nome, 14, 20);
        doc.setFontSize(12);
        doc.text('Total de Atendimentos: ' + data.estatisticas.total, 14, 30);
        doc.text('Tipo mais comum: ' + data.estatisticas.tipo_mais_comum, 14, 36);
        doc.text('Canal mais comum: ' + data.estatisticas.canal_mais_comum, 14, 42);
        doc.text('Relatório gerado por: ' + data.gerado_por, 14, 48);

        const rows = data.atendimentos.map(at => [at.data, at.assunto, at.status, at.tipo, at.canal]);

        doc.autoTable({
            head: [['Data', 'Assunto', 'Status', 'Tipo', 'Canal']],
            body: rows,
            startY: 55,
            theme: 'grid',
            headStyles: { fillColor: [0, 90, 156] },
            margin: { top: 55 },
            didDrawPage: function (data) {
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(100);
                const dataGeracao = new Date().toLocaleString('pt-BR');
                doc.text('Gerado em: ' + dataGeracao, data.settings.margin.left, doc.internal.pageSize.height - 10);
                const str = 'Página ' + doc.internal.getCurrentPageInfo().pageNumber + ' de ' + pageCount;
                const textWidth = doc.getStringUnitWidth(str) * doc.internal.getFontSize() / doc.internal.scaleFactor;
                doc.text(str, doc.internal.pageSize.width - data.settings.margin.right - textWidth, doc.internal.pageSize.height - 10);
            }
        });

        const pdfBlob = doc.output('blob');
        const pdfUrl = URL.createObjectURL(pdfBlob);
        window.open(pdfUrl, '_blank');
    }

    /**
     * Gera o PDF para o relatório de um atendimento específico.
     * @param {object} data - Os dados do atendimento (chave-valor).
     */
    function gerarPDFAtendimento(data) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        doc.setFontSize(18);
        doc.text('Relatório do Atendimento', 14, 20);
        doc.setFontSize(12);

        const geradoPor = data['Relatório Gerado Por'] || 'N/A';
        const protocolo = data.Protocolo || 'N/A';
        delete data['Relatório Gerado Por'];

        doc.text('Protocolo: ' + protocolo, 14, 28);
        doc.text('Relatório gerado por: ' + geradoPor, 14, 34);

        const rows = Object.entries(data).map(([key, value]) => [key, value]);

        doc.autoTable({
            head: [['Campo', 'Valor']],
            body: rows,
            startY: 42,
            theme: 'grid',
            headStyles: { fillColor: [0, 90, 156] },
            columnStyles: { 0: { fontStyle: 'bold' } },
            didDrawPage: function (data) {
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(100);
                const dataGeracao = new Date().toLocaleString('pt-BR');
                doc.text('Gerado em: ' + dataGeracao, data.settings.margin.left, doc.internal.pageSize.height - 10);
                const str = 'Página ' + doc.internal.getCurrentPageInfo().pageNumber + ' de ' + pageCount;
                const textWidth = doc.getStringUnitWidth(str) * doc.internal.getFontSize() / doc.internal.scaleFactor;
                doc.text(str, doc.internal.pageSize.width - data.settings.margin.right - textWidth, doc.internal.pageSize.height - 10);
            }
        });

        doc.save(`atendimento-${data.Protocolo || 'relatorio'}.pdf`);
    }

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