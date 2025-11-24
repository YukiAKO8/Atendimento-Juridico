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
    $allowed_tabs = array( 'dados', 'observacoes', 'documentos', 'historico' );
    $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'dados';

    return in_array( $current_tab, $allowed_tabs ) ? $current_tab : 'dados';
}

/**
 * Generates a complete and unique protocol string.
 * This protocol is independent of the database ID.
 *
 * Format: [MÊS + ANO][ANAGRAMA CURTO][369]-[SUFIXO ÚNICO]
 * Example: 1125LUP369-A4B1C
 *
 * @return string The generated unique protocol.
 */
function aj_generate_unique_protocol() {
 
    $month_year = date('my'); 

   
    $protocol_words = [
        'Creatio',
        'Aequilibrium',
        'Illuminatio'
    ];
   
    $random_word = $protocol_words[array_rand($protocol_words)];
   
    $shuffled_word = str_shuffle($random_word);
    $random_anagram = substr($shuffled_word, 0, 4);

   
    $magic_number = '369';
    $placement_options = ['start', 'middle', 'end'];
    $random_placement = $placement_options[array_rand($placement_options)];

    $protocol_core = '';
    switch ($random_placement) {
        case 'start':
           
            $protocol_core = $magic_number . $random_anagram;
            break;
        case 'middle':
        
            $len = strlen($random_anagram);
            $mid = floor($len / 2);
            $protocol_core = substr($random_anagram, 0, $mid) . $magic_number . substr($random_anagram, $mid);
            break;
        case 'end':
          
            $protocol_core = $random_anagram . $magic_number;
            break;
    }

    
    $base_protocol = strtoupper($month_year . $protocol_core);

  
    $unique_suffix = substr(uniqid(), -3);

   
    return $base_protocol . '-' . strtoupper($unique_suffix);
}



function aj_render_admin_page() {
   
    $atendimento_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';
    $is_readonly = ( $action === 'view' );


    if ( $atendimento_id > 0 || $action === 'new' ) {
        // --- Lógica do Controller: Preparar os dados ---
        $atendimento = null;
        $cadastrado_por_user = null;
        $alterado_por_user = null;

        if ( $atendimento_id > 0 ) {
            $atendimento = aj_get_atendimento_by_id( $atendimento_id );
            if ( ! $atendimento ) { wp_die( 'Atendimento não encontrado.' ); }
            if ( $atendimento->cadastrado_por ) { $cadastrado_por_user = get_userdata( $atendimento->cadastrado_por ); }
            if ( $atendimento->alterado_por ) { $alterado_por_user = get_userdata( $atendimento->alterado_por ); }
        } else {
            $atendimento = (object) [ 'id' => 0, 'protocolo' => aj_generate_unique_protocol() ];
        }

        $current_tab = aj_get_current_tab();
        
        // --- Lógica do Controller: Chamar a View ---
        require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/tela-formulario-wrapper.php';
    } else {
        // Adiciona um wrapper específico para a página de listagem
        echo '<div class="aj-list-wrapper">';
        require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/lista-atendimento.php';
        echo '</div>';
    }
}

function aj_processar_formulario() {
  
    if ( ! isset( $_POST['submit'] ) || ! isset( $_POST['aj_atendimento_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['aj_atendimento_nonce'], 'aj_salvar_atendimento' ) ) {
        // Em vez de encerrar, redireciona de volta com uma mensagem de erro.
        // Isso melhora a experiência do usuário, especialmente se os dados do formulário
        // estiverem salvos no localStorage, evitando a perda de dados.
        $redirect_url = add_query_arg(
            [
                'page'    => 'atendimento-juridico',
                'security_fail' => 'true'
            ],
            admin_url( 'admin.php' )
        );
        wp_redirect( $redirect_url );
        exit;
    }

 
    $data = [];

   
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

 
    $atendimento_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;

    if ( $atendimento_id > 0 ) {
      
        $data['alterado_por'] = get_current_user_id(); // Guarda quem alterou
        $where = [ 'id' => $atendimento_id ];
        $result = aj_update_atendimento( $atendimento_id, $data );
    } else {
       
        $data['cadastrado_por'] = get_current_user_id(); // Guarda quem cadastrou

        // Gera o protocolo único e completo no momento da criação.
        // O valor do POST é ignorado para garantir que seja sempre gerado pelo sistema.
        $data['protocolo'] = aj_generate_unique_protocol();

        $atendimento_id = aj_insert_atendimento( $data );
        $result = $atendimento_id !== false;
    }

 
    if ( $result === false ) {
        // Adiciona uma mensagem de erro
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>Ocorreu um erro ao salvar o atendimento.</p></div>';
        });
    } else {
     
        $redirect_url = add_query_arg(
            [
                'page'    => 'atendimento-juridico',
                'updated' => 'true'
            ],
            admin_url( 'admin.php' )
        );
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

/**
 * Manipulador AJAX para excluir um atendimento.
 */
function aj_excluir_atendimento_ajax_handler() {
    // 1. Verificação de segurança (nonce)
    check_ajax_referer( 'aj_excluir_nonce' );

    // 2. Validação do ID
    if ( ! isset( $_POST['atendimento_id'] ) || ! is_numeric( $_POST['atendimento_id'] ) ) {
        wp_send_json_error( [ 'message' => 'ID do atendimento inválido.' ] );
    }
    $atendimento_id = absint( $_POST['atendimento_id'] );

    // 3. Exclusão do banco de dados
    $result = aj_delete_atendimento( $atendimento_id );
    // 4. Envio da resposta
    if ( $result === false ) {
        wp_send_json_error( [ 'message' => 'Falha ao excluir o registro no banco de dados.' ] );
    } else {
        wp_send_json_success( [ 'message' => 'Atendimento excluído com sucesso.' ] );
    }
}
add_action( 'wp_ajax_aj_excluir_atendimento', 'aj_excluir_atendimento_ajax_handler' );

/**
 * Manipulador AJAX para buscar atendimentos.
 */
function aj_buscar_atendimentos_ajax_handler() {
    // 1. Verificação de segurança (nonce)
    check_ajax_referer( 'aj_buscar_nonce' );

    // Os argumentos da busca são passados para a função do Model.
    $search_args = [
        's'               => isset( $_POST['s'] ) ? sanitize_text_field( $_POST['s'] ) : '',
        'adv_socio'       => isset( $_POST['adv_socio'] ) ? sanitize_text_field( $_POST['adv_socio'] ) : '',
        'adv_advogado'    => isset( $_POST['adv_advogado'] ) ? sanitize_text_field( $_POST['adv_advogado'] ) : '',
        'adv_tipo'        => isset( $_POST['adv_tipo'] ) ? sanitize_text_field( $_POST['adv_tipo'] ) : '',
        'adv_status'      => isset( $_POST['adv_status'] ) ? sanitize_text_field( $_POST['adv_status'] ) : '',
        'adv_data_inicio' => isset( $_POST['adv_data_inicio'] ) ? sanitize_text_field( $_POST['adv_data_inicio'] ) : '',
        'adv_data_fim'    => isset( $_POST['adv_data_fim'] ) ? sanitize_text_field( $_POST['adv_data_fim'] ) : '',
        'page'            => isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1,
        'per_page'        => 8, // Definido como 8, conforme solicitado
    ];
    $search_result = aj_search_atendimentos( $search_args );
    $atendimentos = $search_result['results'];

    // Formata os dados para o front-end
    $data_to_send = [];
    if ( ! empty( $atendimentos ) ) {
        foreach ( $atendimentos as $atendimento ) {
            $atendimento->edit_url = esc_url( add_query_arg( [ 'page' => 'atendimento-juridico', 'id' => $atendimento->id ], admin_url('admin.php') ) );
            $atendimento->view_url = esc_url( add_query_arg( [ 'page' => 'atendimento-juridico', 'action' => 'view', 'id' => $atendimento->id ], admin_url('admin.php') ) );
            $atendimento->data_formatada = date( 'd/m/Y H:i', strtotime( $atendimento->data_atendimento ) );
            $data_to_send[] = $atendimento;
        }
    }

    wp_send_json_success( [
        'data'  => $data_to_send,
        'total' => $search_result['total']
    ] );
}
add_action( 'wp_ajax_aj_buscar_atendimentos', 'aj_buscar_atendimentos_ajax_handler' );

/**
 * Manipulador AJAX para buscar todos os dados para o relatório.
 */
function aj_gerar_relatorio_ajax_handler() {
    // 1. Verificação de segurança (nonce)
    check_ajax_referer( 'aj_buscar_nonce' );

    // 2. Coleta dos mesmos argumentos de busca, mas sem paginação
    $search_args = [
        's'               => isset( $_POST['s'] ) ? sanitize_text_field( wp_unslash( $_POST['s'] ) ) : '',
        'adv_socio'       => isset( $_POST['adv_socio'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_socio'] ) ) : '',
        'adv_advogado'    => isset( $_POST['adv_advogado'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_advogado'] ) ) : '',
        'adv_tipo'        => isset( $_POST['adv_tipo'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_tipo'] ) ) : '',
        'adv_status'      => isset( $_POST['adv_status'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_status'] ) ) : '',
        'adv_data_inicio' => isset( $_POST['adv_data_inicio'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_data_inicio'] ) ) : '',
        'adv_data_fim'    => isset( $_POST['adv_data_fim'] ) ? sanitize_text_field( wp_unslash( $_POST['adv_data_fim'] ) ) : '',
        'nopaging'        => true, // Chave para buscar todos os resultados
    ];

    // 3. Busca no banco de dados
    $search_result = aj_search_atendimentos( $search_args );
    $atendimentos = $search_result['results'];

    // 4. Processamento dos dados para as estatísticas
    $stats = [
        'total' => count( $atendimentos ),
        'status_counts' => [],
        'advogado_counts' => [],
        'horario_counts' => [],
        'tipo_counts' => [],
    ];

    foreach ( $atendimentos as $item ) {
        // Formata a data para o relatório
        $item->data_formatada = date( 'd/m/Y H:i', strtotime( $item->data_atendimento ) );

        // Contagem para estatísticas
        $stats['status_counts'][$item->status] = ( $stats['status_counts'][$item->status] ?? 0 ) + 1;
        $stats['tipo_counts'][$item->tipo_atendimento] = ( $stats['tipo_counts'][$item->tipo_atendimento] ?? 0 ) + 1;

        // Divide os advogados e conta individualmente
        $advogados = array_map( 'trim', explode( ',', $item->advogados ) );
        foreach ( $advogados as $adv ) {
            if ( ! empty( $adv ) ) {
                $stats['advogado_counts'][$adv] = ( $stats['advogado_counts'][$adv] ?? 0 ) + 1;
            }
        }

        $hora = date( 'H:00', strtotime( $item->data_atendimento ) );
        $stats['horario_counts'][$hora] = ( $stats['horario_counts'][$hora] ?? 0 ) + 1;
    }

    wp_send_json_success( [ 'data' => $atendimentos, 'stats' => $stats ] );
}
add_action( 'wp_ajax_aj_gerar_relatorio', 'aj_gerar_relatorio_ajax_handler' );

/**
 * Enfileira scripts e estilos para a área de administração.
 */
function aj_enqueue_admin_scripts( $hook ) {
    // Garante que o script só seja carregado na página do nosso plugin
    if ( 'toplevel_page_atendimento-juridico' !== $hook ) {
        return;
    }

    // Adiciona a biblioteca jsPDF
    wp_enqueue_script( 'aj-jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], '2.5.1', true );
    // Adiciona o plugin jsPDF-AutoTable
    wp_enqueue_script( 'aj-jspdf-autotable', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js', ['aj-jspdf'], '3.8.2', true );
}
add_action( 'admin_enqueue_scripts', 'aj_enqueue_admin_scripts' );