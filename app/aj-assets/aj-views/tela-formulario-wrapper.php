<?php
/**
 * View principal que envolve o formulário de atendimento (abas, botões, etc.).
 *
 * @package AtendimentoJuridico
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Nome do sócio para o botão PDF
$socio_nome = isset( $atendimento->socios ) ? esc_attr( $atendimento->socios ) : '';
?>
<div class="wrap">
    <div class="aj-form-wrapper">
        <br><br><br><br>
        <h1>
            <?php 
                if ($is_readonly) { echo 'Visualizar Atendimento'; }
                elseif ($atendimento_id > 0) { echo 'Editar Atendimento'; }
                else { echo 'Adicionar Novo Atendimento'; }
            ?>
        </h1>
        <br><br><br><br>

        <form id="aj-main-form" method="post">
            <?php wp_nonce_field( 'aj_salvar_atendimento', 'aj_atendimento_nonce' ); ?>

            <nav class="aj-nav-tab-wrapper">
                <?php
                    $base_tab_url_params = [ 'page' => 'atendimento-juridico' ];
                    if ( $atendimento_id > 0 ) {
                        $base_tab_url_params['id'] = $atendimento_id;
                    } elseif ( isset($_GET['action']) && $_GET['action'] === 'new' ) {
                        $base_tab_url_params['action'] = 'new';
                    }
            
                    $base_url = esc_url(add_query_arg($base_tab_url_params, admin_url('admin.php')));
                ?>
                <div data-tab="dados" data-url="<?php echo esc_url(add_query_arg(['tab' => 'dados'], $base_url)); ?>" class="nav-tab nav-tab-dados <?php echo $current_tab == 'dados' ? 'nav-tab-active' : ''; ?>">Dados</div>
                <div data-tab="observacoes" data-url="<?php echo esc_url(add_query_arg(['tab' => 'observacoes'], $base_url)); ?>" class="nav-tab nav-tab-observacoes <?php echo $current_tab == 'observacoes' ? 'nav-tab-active' : ''; ?>">Observações</div>
                <div data-tab="documentos" data-url="<?php echo esc_url(add_query_arg(['tab' => 'documentos'], $base_url)); ?>" class="nav-tab nav-tab-documentos <?php echo $current_tab == 'documentos' ? 'nav-tab-active' : ''; ?>">Documentos</div>
                <div data-tab="historico" data-url="<?php echo esc_url(add_query_arg(['tab' => 'historico'], $base_url)); ?>" class="nav-tab nav-tab-historico <?php echo $current_tab == 'historico' ? 'nav-tab-active' : ''; ?>">Histórico</div>
            </nav>

            <div class="aj-form-container">
                <div id="tab-content-dados" class="tab-content <?php echo $current_tab === 'dados' ? 'active' : ''; ?>">
                    <?php require_once __DIR__ . '/tela-dados.php'; ?>
                </div>
                <div id="tab-content-observacoes" class="tab-content <?php echo $current_tab === 'observacoes' ? 'active' : ''; ?>">
                    <?php require_once __DIR__ . '/tela-outros.php'; ?>
                </div>
                <div id="tab-content-documentos" class="tab-content <?php echo $current_tab === 'documentos' ? 'active' : ''; ?>">
                    <?php require_once __DIR__ . '/tela-documentos.php'; ?>
                </div>
                <div id="tab-content-historico" class="tab-content <?php echo $current_tab === 'historico' ? 'active' : ''; ?>">
                    <?php require_once __DIR__ . '/tela-historico.php'; ?>
                </div>
            </div>

            <div class="aj-meta-container">
                <?php if ( $atendimento_id > 0 && $atendimento ) : ?>
                    <span class="meta-item"><strong>Cadastrado por:</strong> <span id="aj_cadastrado_por"><?php echo $cadastrado_por_user ? esc_html( $cadastrado_por_user->display_name ) : 'N/A'; ?></span></span>
                    <span class="meta-item"><strong>Data do cadastro:</strong> <span id="aj_data_cadastro"><?php echo $atendimento->data_cadastro ? esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_cadastro ) ) ) : 'N/A'; ?></span></span>
                    <span class="meta-item"><strong>Alterado por:</strong> <span id="aj_alterado_por"><?php echo $alterado_por_user ? esc_html( $alterado_por_user->display_name ) : 'N/A'; ?></span></span>
                    <span class="meta-item"><strong>Data da última alteração:</strong> <span id="aj_data_alteracao"><?php echo $atendimento->data_alteracao ? esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_alteracao ) ) ) : 'N/A'; ?></span></span>
                <?php endif; ?>
            </div>

            <br><br>
            <div class="form-actions">
                <?php if ( $is_readonly ) : ?>
                    <?php if ( $atendimento_id > 0 ) : ?>
                        <button type="button" class="button aj-btn-relatorio-atendimento" data-atendimento-id="<?php echo esc_attr($atendimento_id); ?>">
                            <span class="dashicons dashicons-media-default"></span>Gerar Relatório do Atendimento
                        </button>
                    <?php endif; ?>
                    <button type="button" class="button aj-btn-relatorio-socio" data-socio="<?php echo esc_attr($socio_nome); ?>">
                        <span class="dashicons dashicons-printer"></span>Relatório do Sócio
                    </button>
                    <a href="?page=atendimento-juridico" class="button aj-btn-voltar"><span class="dashicons dashicons-arrow-left-alt"></span>Voltar à Lista</a>
                <?php else : ?>
                    <button type="reset" class="button aj-btn-limpar"><span class="dashicons dashicons-trash"></span>Limpar</button>
                    <?php if ( $atendimento_id > 0 ) : ?>
                        <button type="button" class="button aj-btn-relatorio-atendimento" data-atendimento-id="<?php echo esc_attr($atendimento_id); ?>">
                            <span class="dashicons dashicons-media-default"></span>Gerar Relatório do Atendimento
                        </button>
                    <?php endif; ?>
                    <button type="button" class="button aj-btn-relatorio-socio" data-socio="<?php echo esc_attr($socio_nome); ?>">
                        <span class="dashicons dashicons-printer"></span>Relatório do Sócio
                    </button>
                    <a href="?page=atendimento-juridico" class="button aj-btn-cancelar"><span class="dashicons dashicons-no-alt"></span>Cancelar</a>
                    <button type="submit" name="submit" id="submit" class="button aj-btn-salvar"><span class="dashicons dashicons-yes-alt"></span>Salvar Atendimento</button>
                <?php endif; ?>
            </div>
            <br><br>
        </form>
        
        <a href="<?php echo esc_url( plugin_dir_url( dirname( __FILE__, 3 ) ) . 'app/aj-assets/manual-sistema.pdf' ); ?>" download="manual-sistema.pdf" class="aj-fab-help dashicons dashicons-editor-help" title="Manual do Sistema"></a>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
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
                socio: socioNome, // Note que esta action é GET no controller
                _ajax_nonce: '<?php echo wp_create_nonce( 'aj_buscar_nonce' ); ?>'
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
            type: 'POST', // A action que criamos espera um POST
            data: {
                action: 'aj_relatorio_atendimento_pdf',
                atendimento_id: atendimentoId,
                _ajax_nonce: '<?php echo wp_create_nonce( 'aj_buscar_nonce' ); ?>'
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

        // Título
        doc.setFontSize(18);
        doc.text('Relatório de Atendimentos - ' + data.socio.nome, 14, 20);

        // Estatísticas
        doc.setFontSize(12);
        doc.text('Total de Atendimentos: ' + data.estatisticas.total, 14, 30);
        doc.text('Tipo mais comum: ' + data.estatisticas.tipo_mais_comum, 14, 36);
        doc.text('Canal mais comum: ' + data.estatisticas.canal_mais_comum, 14, 42);
        doc.text('Relatório gerado por: ' + data.gerado_por, 14, 48);

        // Tabela de atendimentos
        const rows = data.atendimentos.map(at => [
            at.data,
            at.assunto,
            at.status,
            at.tipo,
            at.canal
        ]);

        doc.autoTable({
            head: [['Data', 'Assunto', 'Status', 'Tipo', 'Canal']],
            body: rows,
            startY: 55, // Aumentado para dar espaço ao novo texto
            theme: 'grid',
            headStyles: { fillColor: [0, 90, 156] },
            margin: { top: 55 },
            didDrawPage: function (data) {
                // Rodapé
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(100);

                // Data de geração
                const dataGeracao = new Date().toLocaleString('pt-BR');
                doc.text('Gerado em: ' + dataGeracao, data.settings.margin.left, doc.internal.pageSize.height - 10);

                // Número da página
                const str = 'Página ' + doc.internal.getCurrentPageInfo().pageNumber + ' de ' + pageCount;
                const textWidth = doc.getStringUnitWidth(str) * doc.internal.getFontSize() / doc.internal.scaleFactor;
                doc.text(str, doc.internal.pageSize.width - data.settings.margin.right - textWidth, doc.internal.pageSize.height - 10);
            }
        });

        // Abrir em nova aba
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

        // Título
        doc.setFontSize(18);
        doc.text('Relatório do Atendimento', 14, 20);
        doc.setFontSize(12);

        // Extrai o nome do usuário e o protocolo dos dados
        const geradoPor = data['Relatório Gerado Por'] || 'N/A';
        const protocolo = data.Protocolo || 'N/A';
        delete data['Relatório Gerado Por']; // Remove para não duplicar na tabela

        doc.text('Protocolo: ' + protocolo, 14, 28);
        doc.text('Relatório gerado por: ' + geradoPor, 14, 34);

        // Transforma o objeto de dados em um array para a tabela
        const rows = Object.entries(data).map(([key, value]) => [key, value]);

        doc.autoTable({
            head: [['Campo', 'Valor']],
            body: rows,
            startY: 42, // Aumentado para dar espaço ao novo texto
            theme: 'grid',
            headStyles: { fillColor: [0, 90, 156] },
            columnStyles: { 0: { fontStyle: 'bold' } },
            didDrawPage: function (data) {
                // Rodapé
                const pageCount = doc.internal.getNumberOfPages();
                doc.setFontSize(8);
                doc.setTextColor(100);

                // Data de geração
                const dataGeracao = new Date().toLocaleString('pt-BR');
                doc.text('Gerado em: ' + dataGeracao, data.settings.margin.left, doc.internal.pageSize.height - 10);

                // Número da página
                const str = 'Página ' + doc.internal.getCurrentPageInfo().pageNumber + ' de ' + pageCount;
                const textWidth = doc.getStringUnitWidth(str) * doc.internal.getFontSize() / doc.internal.scaleFactor;
                doc.text(str, doc.internal.pageSize.width - data.settings.margin.right - textWidth, doc.internal.pageSize.height - 10);
            }
        });

        doc.save(`atendimento-${data.Protocolo || 'relatorio'}.pdf`);
    }

    // Ao clicar no botão "Gerar Relatório" da tela de listagem
    $(document).on('click', '#aj-generate-report-btn', function(e) {
        e.preventDefault();
        
        // Coleta os dados dos filtros do formulário de pesquisa
        const form = $('#aj-search-form');
        const formData = {
            action: 'aj_gerar_relatorio',
            s: form.find('input[name="s"]').val(),
            adv_socio: form.find('input[name="adv_socio"]').val(),
            adv_advogado: form.find('input[name="adv_advogado"]').val(),
            adv_tipo: form.find('select[name="adv_tipo"]').val(),
            adv_status: form.find('select[name="adv_status"]').val(),
            adv_data_inicio: form.find('input[name="adv_data_inicio"]').val(),
            adv_data_fim: form.find('input[name="adv_data_fim"]').val(),
            _ajax_nonce: '<?php echo wp_create_nonce( 'aj_buscar_nonce' ); ?>'
        };

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(res) {
                if (res.success) {
                    gerarPDFRelatorioGeral(res);
                } else {
                    alert('Erro ao gerar relatório geral.');
                }
            },
            error: function() {
                alert('Erro de conexão ao gerar relatório geral.');
            }
        });
    });

    /**
     * Gera o PDF para o relatório geral de atendimentos.
     * @param {object} response - O objeto de resposta completo do AJAX.
     */
    function gerarPDFRelatorioGeral(response) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape' });
        const data = response.data;
        const stats = response.stats;
        const geradoPor = response.gerado_por;

        doc.setFontSize(18);
        doc.text('Relatório Geral de Atendimentos', 14, 20);
        doc.setFontSize(10);
        doc.text('Total de Atendimentos: ' + stats.total, 14, 30);
        doc.text('Relatório gerado por: ' + geradoPor, 14, 36);

        const rows = data.map(at => [
            at.protocolo, at.socios, at.assunto, at.advogados, at.data_formatada, at.status
        ]);

        doc.autoTable({
            head: [['Protocolo', 'Sócio', 'Assunto', 'Advogado(s)', 'Data', 'Status']],
            body: rows,
            startY: 42,
            theme: 'grid',
            headStyles: { fillColor: [0, 90, 156] }
        });

        doc.save('relatorio-geral-atendimentos.pdf');
    }
    // Registra o AJAX para usuários logados
add_action('wp_ajax_aj_get_socios', 'aj_retorna_nomes_socios');

function aj_retorna_nomes_socios() {
    // Exemplo: busca todos os posts do CPT 'socio'
    $socios = get_posts([
        'post_type'      => 'socio',   // ajuste para o seu CPT
        'post_status'    => 'publish',
        'numberposts'    => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    $nomes = array_map(function($s) {
        return $s->post_title;
    }, $socios);

    wp_send_json_success($nomes);
}
});
</script>