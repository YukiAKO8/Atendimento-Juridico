jQuery(document).ready(function($) {
    
    // Adiciona o HTML do modal ao corpo da página, inicialmente oculto.
    $('body').append(`
        <div id="guia-ajuda-modal" style="display:none;">
            <div class="ajuda-modal-overlay"></div>
            <div class="ajuda-modal-content">
                <button class="ajuda-modal-close">&times;</button>
                <iframe id="guia-ajuda-iframe" src="" style="width:100%; height:100%; border:0;"></iframe>
            </div>
        </div>
    `);

    // --- IMPORTANTE ---
    // Altere o seletor abaixo para corresponder exatamente ao seu botão de ajuda ("?").
    // Use o inspetor de elementos do navegador para encontrar o ID ou a classe do link.
    // Este seletor procura por um link que contenha 'GuiaAtendimentoJuridico.pdf' no href.
    const seletorBotaoAjuda = 'a[href*="GuiaAtendimentoJuridico.pdf"]';

    // Intercepta o clique no botão de ajuda
    $(document).on('click', seletorBotaoAjuda, function(e) {
        // Previne a ação padrão (abrir o link do PDF)
        e.preventDefault();

        // Pega a URL do PDF diretamente do atributo 'href' do elemento <a> clicado.
        const pdfUrl = $(this).attr('href');

        // Define o SRC do iframe com a URL do PDF.
        // A maioria dos navegadores modernos consegue renderizar um PDF em um iframe.
        // Se o navegador não tiver um visualizador de PDF embutido, pode ser que ele inicie o download.
        // Para uma melhor compatibilidade, pode-se usar bibliotecas como PDF.js, mas isso exigiria mais arquivos.
        $('#guia-ajuda-iframe').attr('src', pdfUrl); 
        
        // Exibe o modal
        $('#guia-ajuda-modal').fadeIn(200);
    });

    // Função para fechar o modal
    function fecharModal() {
        $('#guia-ajuda-modal').fadeOut(200);
        $('#guia-ajuda-iframe').attr('src', ''); // Limpa o src para parar qualquer carregamento
    }

    // Fecha o modal ao clicar no botão de fechar ou no fundo escuro
    $(document).on('click', '.ajuda-modal-close, .ajuda-modal-overlay', fecharModal);
});