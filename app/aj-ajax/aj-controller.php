<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Determina a aba atual com base no parâmetro 'tab' da URL.
 *
 * @return string A aba atual. O padrão é 'dados'.
 */
function aj_get_current_tab() {
    // Lista de abas permitidas para segurança.
    $allowed_tabs = array( 'dados', 'observacoes', 'documentos' );
    $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dados';

    return in_array( $current_tab, $allowed_tabs ) ? $current_tab : 'dados';
}

/**
 * Renderiza o conteúdo completo da página de administração do plugin.
 * Inclui a navegação por abas e carrega a view da aba correta.
 */
function aj_render_admin_page() {
    // Obtém a aba atual a partir desta mesma controller.
    $current_tab = aj_get_current_tab();

    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <nav class="aj-nav-tab-wrapper">
            <a href="?page=atendimento-juridico&tab=dados" class="nav-tab nav-tab-dados <?php echo $current_tab == 'dados' ? 'nav-tab-active' : ''; ?>">Dados</a>
            <a href="?page=atendimento-juridico&tab=observacoes" class="nav-tab nav-tab-observacoes <?php echo $current_tab == 'observacoes' ? 'nav-tab-active' : ''; ?>">Observações</a>
            <a href="?page=atendimento-juridico&tab=documentos" class="nav-tab nav-tab-documentos <?php echo $current_tab == 'documentos' ? 'nav-tab-active' : ''; ?>">Documentos</a>
        </nav>

        <div class="aj-form-container">
            <?php
            // Carrega o conteúdo da aba correspondente.
            if ( $current_tab == 'dados' ) {
                require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-dados.php';
            } elseif ( $current_tab == 'observacoes' ) {
                require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-outros.php';
            } elseif ( $current_tab == 'documentos' ) {
                require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-documentos.php';
            }
            ?>
        </div>
    </div>
    <?php
}