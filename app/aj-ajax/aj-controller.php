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
    $allowed_tabs = array( 'dados', 'observacoes', 'documentos', 'historico' );
    $current_tab  = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dados';
    return in_array( $current_tab, $allowed_tabs, true ) ? $current_tab : 'dados';
}

/**
 * Gera protocolo único.
 */
function aj_generate_unique_protocol() {
    $month_year   = date( 'my' );
    $protocol_words = array( 'Creatio', 'Aequilibrium', 'Illuminatio' );
    $random_word  = $protocol_words[ array_rand( $protocol_words ) ];
    $shuffled     = str_shuffle( $random_word );
    $random_anagram = substr( $shuffled, 0, 4 );
    $magic_number   = '369';
    $placement_options = array( 'start', 'middle', 'end' );
    $random_placement  = $placement_options[ array_rand( $placement_options ) ];

    $protocol_core = '';
    switch ( $random_placement ) {
        case 'start':
            $protocol_core = $magic_number . $random_anagram;
            break;
        case 'middle':
            $len = strlen( $random_anagram );
            $mid = floor( $len / 2 );
            $protocol_core = substr( $random_anagram, 0, $mid ) . $magic_number . substr( $random_anagram, $mid );
            break;
        case 'end':
            $protocol_core = $random_anagram . $magic_number;
            break;
    }
    $base_protocol = strtoupper( $month_year . $protocol_core );
    $unique_suffix = substr( uniqid(), -3 );
    return $base_protocol . '-' . strtoupper( $unique_suffix );
}

/**
 * Renderizador da página principal.
 */
function aj_render_admin_page() {
    $atendimento_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    $action         = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
    $is_readonly    = ( $action === 'view' );

    /* ---------- RELATÓRIO INDIVIDUAL DO SÓCIO ---------- */
    if ( $action === 'relatorio_socio' && ! empty( $_GET['socio'] ) ) {
        $socio_nome = sanitize_text_field( $_GET['socio'] );

        // Busca todos atendimentos do sócio
        $historico_atendimentos = aj_search_atendimentos( array(
            'adv_socio' => $socio_nome,
            'nopaging'  => true,
        ) )['results'];

        // Estatísticas
        $total = count( $historico_atendimentos );
        $tipos = array();
        $canais = array();
        foreach ( $historico_atendimentos as $at ) {
            $tipos[ $at->tipo_atendimento ]   = ( $tipos[ $at->tipo_atendimento ] ?? 0 ) + 1;
            $canais[ $at->forma_atendimento ] = ( $canais[ $at->forma_atendimento ] ?? 0 ) + 1;
        }
        arsort( $tipos );
        arsort( $canais );

        $estatisticas = array(
            'total'              => $total,
            'tipo_mais_comum'    => $tipos ? array_key_first( $tipos ) : 'N/A',
            'canal_mais_comum'   => $canais ? array_key_first( $canais ) : 'N/A',
        );

        // Objeto sócio
        $socio = (object) array(
            'nome' => $socio_nome,
            'cpf'  => '--', // se houver CPF, adapte aqui
        );

        // Carrega a view
        require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-relatorio-socio.php';
        return; // <-- importante: para aqui e não carrega o resto
    }
    /* ---------------------------------------------------- */

    if ( $atendimento_id > 0 || $action === 'new' ) {
        // formulário de atendimento (view/edit/new)
        $atendimento = null;
        $cadastrado_por_user = null;
        $alterado_por_user   = null;

        if ( $atendimento_id > 0 ) {
            $atendimento = aj_get_atendimento_by_id( $atendimento_id );
            if ( ! $atendimento ) {
                wp_die( 'Atendimento não encontrado.' );
            }
            if ( $atendimento->cadastrado_por ) {
                $cadastrado_por_user = get_userdata( $atendimento->cadastrado_por );
            }
            if ( $atendimento->alterado_por ) {
                $alterado_por_user = get_userdata( $atendimento->alterado_por );
            }
        } else {
            $atendimento = (object) array(
                'id'        => 0,
                'protocolo' => aj_generate_unique_protocol(),
            );
        }

        $current_tab = aj_get_current_tab();
        require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-formulario-wrapper.php';
    } else {
        // listagem geral
        echo '<div class="aj-list-wrapper">';
        require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/lista-atendimento.php';
        echo '</div>';
    }
}

/* ---------- AJAX: RELATÓRIO INDIVIDUAL DO SÓCIO (PDF) ---------- */
function aj_relatorio_socio_pdf_ajax_handler() {
    check_ajax_referer( 'aj_buscar_nonce' );

    $socio_nome = isset( $_GET['socio'] ) ? sanitize_text_field( $_GET['socio'] ) : '';
    if ( ! $socio_nome ) {
        wp_send_json_error( 'Sócio não informado.' );
    }

    $historico = aj_search_atendimentos( array(
        'adv_socio' => $socio_nome,
        'nopaging'  => true,
    ) )['results'];

    $total  = count( $historico );
    $tipos  = array();
    $canais = array();

    foreach ( $historico as $at ) {
        $tipos[ $at->tipo_atendimento ]   = ( $tipos[ $at->tipo_atendimento ] ?? 0 ) + 1;
        $canais[ $at->forma_atendimento ] = ( $canais[ $at->forma_atendimento ] ?? 0 ) + 1;
    }
    arsort( $tipos );
    arsort( $canais );

    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    $user_role_name = ! empty( $user_roles ) ? translate_user_role( $user_roles[0] ) : 'N/A';
    $gerado_por = $current_user->display_name . ' (' . $user_role_name . ')';

    wp_send_json_success( array(
        'gerado_por' => $gerado_por,
        'socio' => array(
            'nome' => $socio_nome,
            'cpf'  => '--',
        ),
        'estatisticas' => array(
            'total'            => $total,
            'tipo_mais_comum'  => $tipos ? array_key_first( $tipos ) : 'N/A',
            'canal_mais_comum' => $canais ? array_key_first( $canais ) : 'N/A',
        ),
        'atendimentos' => array_map( function( $at ) {
            return array(
                'data'    => date( 'd/m/Y', strtotime( $at->data_atendimento ) ),
                'assunto' => $at->assunto,
                'status'  => $at->status,
                'tipo'    => $at->tipo_atendimento,
                'canal'   => $at->forma_atendimento,
                'obs'     => $at->observacoes,
            );
        }, $historico ),
    ) );
}
add_action( 'wp_ajax_aj_relatorio_socio_pdf', 'aj_relatorio_socio_pdf_ajax_handler' );

/* ---------- AJAX: RELATÓRIO INDIVIDUAL DO ATENDIMENTO (PDF) ---------- */
function aj_relatorio_atendimento_pdf_ajax_handler() {
    check_ajax_referer( 'aj_buscar_nonce' );

    $atendimento_id = isset( $_POST['atendimento_id'] ) ? absint( $_POST['atendimento_id'] ) : 0;

    if ( ! $atendimento_id ) {
        wp_send_json_error( 'ID do atendimento não informado.' );
    }

    $atendimento = aj_get_atendimento_by_id( $atendimento_id );

    if ( ! $atendimento ) {
        wp_send_json_error( 'Atendimento não encontrado.' );
    }

    // Formata os dados para não enviar dados brutos ou nulos.
    $dados_formatados = array();
    foreach ( (array) $atendimento as $chave => $valor ) {
        if ( ! empty( $valor ) ) {
            if ( $chave === 'data_atendimento' ) {
                $dados_formatados['Data do Atendimento'] = date( 'd/m/Y H:i', strtotime( $valor ) );
            } else {
                $chave_formatada = ucwords( str_replace( '_', ' ', $chave ) );
                $dados_formatados[ $chave_formatada ] = $valor;
            }
        }
    }

    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    $user_role_name = ! empty( $user_roles ) ? translate_user_role( $user_roles[0] ) : 'N/A';
    $gerado_por = $current_user->display_name . ' (' . $user_role_name . ')';

    // Adiciona o nome do usuário aos dados formatados para que apareça na tabela.
    $dados_formatados['Relatório Gerado Por'] = $gerado_por;
    wp_send_json_success( $dados_formatados );
}
add_action( 'wp_ajax_aj_relatorio_atendimento_pdf', 'aj_relatorio_atendimento_pdf_ajax_handler' );

/* ---------- RESTO DO CONTROLLER (SEM ALTERAÇÕES) ---------- */

function aj_processar_formulario() {
    if ( ! isset( $_POST['submit'] ) || ! isset( $_POST['aj_atendimento_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['aj_atendimento_nonce'], 'aj_salvar_atendimento' ) ) {
        $redirect_url = add_query_arg( array(
            'page'          => 'atendimento-juridico',
            'security_fail' => 'true',
        ), admin_url( 'admin.php' ) );
        wp_redirect( $redirect_url );
        exit;
    }

    $data = array();

    if ( isset( $_POST['aj_socios'] ) ) {
        $data['socios']            = sanitize_text_field( $_POST['aj_socios'] );
        $data['situacao']          = sanitize_text_field( $_POST['aj_situacao'] );
        $data['empresa']           = sanitize_text_field( $_POST['aj_empresa'] );
        $data['funcao']            = sanitize_text_field( $_POST['aj_funcao'] );
        $data['advogados']         = sanitize_text_field( $_POST['aj_advogados'] );
        $data['tipo_atendimento']  = sanitize_text_field( $_POST['aj_tipo_atendimento'] );
        $data['forma_atendimento'] = sanitize_text_field( $_POST['aj_forma_atendimento'] );
        $data['status']            = sanitize_text_field( $_POST['aj_status'] );
        $data['assunto']           = sanitize_textarea_field( $_POST['aj_assunto'] );
        $data['protocolo']         = sanitize_text_field( $_POST['aj_protocolo'] );
        $data['entrada_processo']  = isset( $_POST['aj_entrada_processo'] ) ? 1 : 0;

        $data_atendimento = sanitize_text_field( $_POST['aj_data_atendimento'] );
        $hora_atendimento = sanitize_text_field( $_POST['aj_hora_atendimento'] );
        $data['data_atendimento'] = $data_atendimento . ' ' . $hora_atendimento;
    }

    if ( isset( $_POST['aj_sumula_atendimento'] ) ) {
        $data['sumula_atendimento']      = sanitize_textarea_field( $_POST['aj_sumula_atendimento'] );
        $data['objeto_atendimento']      = sanitize_textarea_field( $_POST['aj_objeto_atendimento'] );
        $data['observacoes_atendimento'] = wp_kses_post( $_POST['aj_observacoes_atendimento'] );
    }

    if ( empty( $data ) ) {
        return;
    }

    // Procura o ID primeiro no POST (ao salvar) e depois no GET (ao carregar a página).
    $atendimento_id = 0;
    if ( isset( $_POST['atendimento_id'] ) && is_numeric( $_POST['atendimento_id'] ) ) {
        $atendimento_id = absint( $_POST['atendimento_id'] );
    } elseif ( isset( $_GET['id'] ) ) {
        $atendimento_id = absint( $_GET['id'] );
    }

    if ( $atendimento_id > 0 ) {
        $data['alterado_por'] = get_current_user_id();
        $result               = aj_update_atendimento( $atendimento_id, $data );
    } else {
        $data['cadastrado_por'] = get_current_user_id();
        $data['protocolo']      = aj_generate_unique_protocol();
        $atendimento_id         = aj_insert_atendimento( $data );
        $result                 = $atendimento_id !== false;
    }

    if ( $result === false ) {
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>Ocorreu um erro ao salvar o atendimento.</p></div>';
        } );
    } else {
        $redirect_url = add_query_arg( array(
            'page'    => 'atendimento-juridico',
            'updated' => 'true',
        ), admin_url( 'admin.php' ) );
        wp_redirect( $redirect_url );
        exit;
    }
}
add_action( 'admin_init', 'aj_processar_formulario' );

function aj_mostrar_notificacao_sucesso() {
    if ( isset( $_GET['updated'] ) && $_GET['updated'] === 'true' ) {
        echo '<div class="notice notice-success is-dismissible"><p>Atendimento salvo com sucesso!</p></div>';
    }
}
add_action( 'admin_notices', 'aj_mostrar_notificacao_sucesso' );

function aj_mostrar_notificacao_falha_seguranca() {
    if ( isset( $_GET['security_fail'] ) && $_GET['security_fail'] === 'true' ) {
        echo '<div class="notice notice-error is-dismissible"><p>A verificação de segurança falhou. Por favor, tente salvar o atendimento novamente.</p></div>';
    }
}
add_action( 'admin_notices', 'aj_mostrar_notificacao_falha_seguranca' );

function aj_excluir_atendimento_ajax_handler() {
    check_ajax_referer( 'aj_excluir_nonce' );
    if ( ! isset( $_POST['atendimento_id'] ) || ! is_numeric( $_POST['atendimento_id'] ) ) {
        wp_send_json_error( array( 'message' => 'ID do atendimento inválido.' ) );
    }
    $atendimento_id = absint( $_POST['atendimento_id'] );
    $result         = aj_delete_atendimento( $atendimento_id );
    if ( $result === false ) {
        wp_send_json_error( array( 'message' => 'Falha ao excluir o registro no banco de dados.' ) );
    } else {
        wp_send_json_success( array( 'message' => 'Atendimento excluído com sucesso.' ) );
    }
}
add_action( 'wp_ajax_aj_excluir_atendimento', 'aj_excluir_atendimento_ajax_handler' );

function aj_buscar_atendimentos_ajax_handler() {
    check_ajax_referer( 'aj_buscar_nonce' );
    $search_args = array(
        's'               => isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '',
        'adv_socio'       => isset( $_POST['adv_socio'] ) ? sanitize_text_field( $_POST['adv_socio'] ) : '',
        'adv_advogado'    => isset( $_POST['adv_advogado'] ) ? sanitize_text_field( $_POST['adv_advogado'] ) : '',
        'adv_tipo'        => isset( $_POST['adv_tipo'] ) ? sanitize_text_field( $_POST['adv_tipo'] ) : '',
        'adv_status'      => isset( $_POST['adv_status'] ) ? sanitize_text_field( $_POST['adv_status'] ) : '',
        'adv_data_inicio' => isset( $_POST['adv_data_inicio'] ) ? sanitize_text_field( $_POST['adv_data_inicio'] ) : '',
        'adv_data_fim'    => isset( $_POST['adv_data_fim'] ) ? sanitize_text_field( $_POST['adv_data_fim'] ) : '',
        'page'            => isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1,
        'per_page'        => 8,
    );
    $search_result = aj_search_atendimentos( $search_args );
    $atendimentos  = $search_result['results'];
    $data_to_send  = array();
    if ( ! empty( $atendimentos ) ) {
        foreach ( $atendimentos as $atendimento ) {
            $atendimento->edit_url       = esc_url( add_query_arg( array( 'page' => 'atendimento-juridico', 'id' => $atendimento->id ), admin_url( 'admin.php' ) ) );
            $atendimento->view_url       = esc_url( add_query_arg( array( 'page' => 'atendimento-juridico', 'action' => 'view', 'id' => $atendimento->id ), admin_url( 'admin.php' ) ) );
            $atendimento->data_formatada = date( 'd/m/Y H:i', strtotime( $atendimento->data_atendimento ) );
            $data_to_send[]              = $atendimento;
        }
    }
    wp_send_json_success( array(
        'data'  => $data_to_send,
        'total' => $search_result['total'],
    ) );
}
add_action( 'wp_ajax_aj_buscar_atendimentos', 'aj_buscar_atendimentos_ajax_handler' );

function aj_gerar_relatorio_ajax_handler() {
    check_ajax_referer( 'aj_buscar_nonce' );
    $search_args = array(
        's'               => isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '',
        'adv_socio'       => isset( $_POST['adv_socio'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_socio'] ) ) : '',
        'adv_advogado'    => isset( $_POST['adv_advogado'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_advogado'] ) ) : '',
        'adv_tipo'        => isset( $_POST['adv_tipo'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_tipo'] ) ) : '',
        'adv_status'      => isset( $_POST['adv_status'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_status'] ) ) : '',
        'adv_data_inicio' => isset( $_POST['adv_data_inicio'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_data_inicio'] ) ) : '',
        'adv_data_fim'    => isset( $_POST['adv_data_fim'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_data_fim'] ) ) : '',
        'nopaging'        => true,
    );
    $search_result = aj_search_atendimentos( $search_args );
    $atendimentos  = $search_result['results'];
    $stats         = array(
        'total'           => count( $atendimentos ),
        'status_counts'   => array(),
        'advogado_counts' => array(),
        'horario_counts'  => array(),
        'tipo_counts'     => array(),
    );
    foreach ( $atendimentos as $item ) {
        $item->data_formatada = date( 'd/m/Y H:i', strtotime( $item->data_atendimento ) );
        $stats['status_counts'][ $item->status ] = ( $stats['status_counts'][ $item->status ] ?? 0 ) + 1;
        $stats['tipo_counts'][ $item->tipo_atendimento ] = ( $stats['tipo_counts'][ $item->tipo_atendimento ] ?? 0 ) + 1;
        $advogados = array_map( 'trim', explode( ',', $item->advogados ) );
        foreach ( $advogados as $adv ) {
            if ( ! empty( $adv ) ) {
                $stats['advogado_counts'][ $adv ] = ( $stats['advogado_counts'][ $adv ] ?? 0 ) + 1;
            }
        }
        $hora = date( 'H:00', strtotime( $item->data_atendimento ) );
        $stats['horario_counts'][ $hora ] = ( $stats['horario_counts'][ $hora ] ?? 0 ) + 1;
    }

    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    $user_role_name = ! empty( $user_roles ) ? translate_user_role( $user_roles[0] ) : 'N/A';
    $gerado_por = $current_user->display_name . ' (' . $user_role_name . ')';

    wp_send_json_success( array( 'data' => $atendimentos, 'stats' => $stats, 'gerado_por' => $gerado_por ) );
}
add_action( 'wp_ajax_aj_gerar_relatorio', 'aj_gerar_relatorio_ajax_handler' );

/* ---------- SCRIPTS ---------- */
function aj_enqueue_admin_scripts( $hook ) {
    if ( 'toplevel_page_atendimento-juridico' !== $hook ) {
        return;
    }
    wp_enqueue_script( 'aj-jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array(), '2.5.1', true );
    wp_enqueue_script( 'aj-jspdf-autotable', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js', array( 'aj-jspdf' ), '3.8.2', true );

    // Passa ajaxurl e nonce para o JS
    wp_localize_script( 'aj-jspdf-autotable', 'aj_vars', array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'aj_buscar_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'aj_enqueue_admin_scripts' );