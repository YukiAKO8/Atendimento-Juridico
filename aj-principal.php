<?php
/**
 * Plugin Name:       Atendimento Juridico
 * Plugin URI:        https://example.com/
 * Description:       Plugin para gerenciar atendimentos jurídicos.
 * Version:           1.0.0
 * Author:            Seu Nome
 * Author URI:        https://example.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       atendimento-juridico
 * Domain Path:       /languages
 */

// Se este arquivo for chamado diretamente, aborte.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * O código que é executado durante a ativação do plugin.
 */
function activate_atendimento_juridico() {
	// Coloque seu código de ativação aqui.
}
register_activation_hook( __FILE__, 'activate_atendimento_juridico' );

/**
 * O código que é executado durante a desativação do plugin.
 */
function deactivate_atendimento_juridico() {
	// Coloque seu código de desativação aqui.
}
register_deactivation_hook( __FILE__, 'deactivate_atendimento_juridico' );

// Inclui os arquivos do Controller e do Banco de Dados
require_once plugin_dir_path( __FILE__ ) . 'app/aj-ajax/aj-controller.php';
require_once plugin_dir_path( __FILE__ ) . 'utils/aj-db.php';


/**
 * Adiciona o menu principal do plugin no painel de administração do WordPress.
 */
function aj_atendimento_juridico_menu() {
    add_menu_page(
        'Atendimento Jurídico',             // Título da página que aparece na tag <title>
        'Atendimento Jurídico',             // Título do menu que aparece na barra lateral
        'manage_options',                   // Capacidade necessária para ver o menu (ex: 'manage_options' para administradores)
        'atendimento-juridico',             // Slug único do menu
        'aj_atendimento_juridico_page_content', // Função de callback para exibir o conteúdo da página
        'dashicons-groups',                 // Ícone do menu (usando um Dashicon do WordPress)
        6                                   // Posição no menu (número menor = mais acima)
    );
}
add_action( 'admin_menu', 'aj_atendimento_juridico_menu' );

/**
 * Função de callback para exibir o conteúdo da página do plugin.
 */
function aj_atendimento_juridico_page_content() {
    // A responsabilidade de renderizar a página foi delegada para o controller.
    if ( function_exists( 'aj_render_admin_page' ) ) {
        aj_render_admin_page();
    }
}

/**
 * Enfileira os estilos necessários para o painel de administração.
 */
function aj_atendimento_juridico_enqueue_admin_assets($hook) {
    // O $hook é o sufixo da página de admin. Para nossa página, será 'toplevel_page_atendimento-juridico'.
    // Isso garante que os scripts e estilos só sejam carregados na página do nosso plugin.
    if ( 'toplevel_page_atendimento-juridico' != $hook ) {
        return;
    }

    // Enfileira o arquivo CSS
    wp_enqueue_style(
        'aj-atendimento-juridico-style',
        plugin_dir_url( __FILE__ ) . 'app/aj-assets/style.css',
        array(),
        '1.0.0'
    );

    // Enfileira o arquivo JavaScript
    wp_enqueue_script(
        'aj-atendimento-juridico-main-script',
        plugin_dir_url( __FILE__ ) . 'app/aj-ajax/aj-admin-main.js',
        array('jquery'), // Dependência do jQuery
        '1.0.0',
        true // Carrega o script no rodapé
    );
}
add_action( 'admin_enqueue_scripts', 'aj_atendimento_juridico_enqueue_admin_assets' );