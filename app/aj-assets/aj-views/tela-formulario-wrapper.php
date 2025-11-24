<?php
/**
 * View principal que envolve o formulário de atendimento (abas, botões, etc.).
 *
 * @package AtendimentoJuridico
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
    <div class="aj-form-wrapper"> <!-- Container para centralização -->
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
                    <?php require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-dados.php'; ?>
                </div>
                <div id="tab-content-observacoes" class="tab-content <?php echo $current_tab === 'observacoes' ? 'active' : ''; ?>">
                    <?php require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-outros.php'; ?>
                </div>
                <div id="tab-content-documentos" class="tab-content <?php echo $current_tab === 'documentos' ? 'active' : ''; ?>">
                    <?php require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-documentos.php'; ?>
                </div>
                <div id="tab-content-historico" class="tab-content <?php echo $current_tab === 'historico' ? 'active' : ''; ?>">
                    <?php require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-historico.php'; ?>
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
                    <a href="?page=atendimento-juridico" class="button aj-btn-voltar"><span class="dashicons dashicons-arrow-left-alt"></span>Voltar à Lista</a>
                <?php else : ?>
                    <button type="reset" class="button aj-btn-limpar"><span class="dashicons dashicons-trash"></span>Limpar</button>
                    <a href="?page=atendimento-juridico" class="button aj-btn-cancelar"><span class="dashicons dashicons-no-alt"></span>Cancelar</a>
                    <button type="submit" name="submit" id="submit" class="button aj-btn-salvar"><span class="dashicons dashicons-yes-alt"></span>Salvar Atendimento</button>
                <?php endif; ?>
            </div>
            <br><br>
        </form>
        
        <a href="<?php echo esc_url( plugin_dir_url( dirname( __FILE__, 3 ) ) . 'app/aj-assets/manual-sistema.pdf' ); ?>" download="manual-sistema.pdf" class="aj-fab-help dashicons dashicons-editor-help" title="Manual do Sistema"></a>
    </div>
</div>