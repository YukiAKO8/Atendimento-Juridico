<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Este arquivo será responsável por todas as interações com o banco de dados.

/**
 * Cria a tabela personalizada para os atendimentos jurídicos no banco de dados
 * durante a ativação do plugin.
 */
function aj_criar_tabela_atendimentos() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        
        -- Campos da aba 'Dados'
        socios varchar(255) NOT NULL,
        situacao varchar(100) DEFAULT NULL,
        empresa varchar(255) DEFAULT NULL,
        funcao varchar(100) DEFAULT NULL,
        advogados varchar(255) NOT NULL,
        tipo_atendimento varchar(100) NOT NULL,
        forma_atendimento varchar(100) NOT NULL,
        status varchar(50) NOT NULL,
        assunto text NOT NULL,
        protocolo varchar(100) DEFAULT NULL,
        entrada_processo tinyint(1) DEFAULT 0,
        data_atendimento datetime NOT NULL,

        -- Campos da aba 'Observações'
        sumula_atendimento text DEFAULT NULL,
        objeto_atendimento text DEFAULT NULL,
        observacoes_atendimento longtext DEFAULT NULL,

        -- Campos de Metadados (Cadastro e Alteração)
        cadastrado_por bigint(20) UNSIGNED DEFAULT NULL,
        data_cadastro datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        alterado_por bigint(20) UNSIGNED DEFAULT NULL,
        data_alteracao datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        PRIMARY KEY  (id),
        KEY cadastrado_por (cadastrado_por),
        KEY alterado_por (alterado_por)
    ) $charset_collate;";

    // Inclui o arquivo necessário para a função dbDelta.
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    // Executa a query para criar a tabela.
    // A função dbDelta verifica se a tabela já existe e a atualiza se necessário.
    dbDelta( $sql );
}

/**
 * Função para ser chamada na desativação do plugin (opcional).
 * Pode ser usada para limpar a tabela, se desejado.
 */
// function aj_remover_tabela_atendimentos() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'aj_atendimentos';
//     $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
// }