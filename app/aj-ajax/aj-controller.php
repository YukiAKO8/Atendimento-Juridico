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
       

   
        $atendimento = null;
        $cadastrado_por_user = null;
        $alterado_por_user = null;

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
         
            $atendimento = (object) [
                'id' => 0,
                'protocolo' => aj_generate_unique_protocol(),
                'cadastrado_por' => null,
                'data_cadastro' => null,
                'alterado_por' => null,
                'data_alteracao' => null,
            ];
        }

     
        $current_tab = aj_get_current_tab();
     
        $current_user = wp_get_current_user();
        ?>
        <div class="wrap">
            <div class="aj-form-wrapper"> <!-- Container para centralização -->
                <br>
                <br>
                <br>
                <br>
                <h1>
                    <?php 
                        if ($is_readonly) { echo 'Visualizar Atendimento'; }
                        elseif ($atendimento_id > 0) { echo 'Editar Atendimento'; }
                        else { echo 'Adicionar Novo Atendimento'; }
                    ?>
                </h1>
                <br>
                <br>
                <br>
                <br>
    
            <form id="aj-main-form" method="post">
                <?php wp_nonce_field( 'aj_salvar_atendimento', 'aj_atendimento_nonce' ); ?>

            <nav class="aj-nav-tab-wrapper">
                <?php
                   
                    $base_tab_url_params = [ 'page' => 'atendimento-juridico' ];
                    if ( $atendimento_id > 0 ) {
                        $base_tab_url_params['id'] = $atendimento_id;
                    } elseif ( $action === 'new' ) {
                        $base_tab_url_params['action'] = 'new';
                    }
            
                    $base_url = esc_url(add_query_arg($base_tab_url_params));
                ?>
                <div data-tab="dados" data-url="<?php echo esc_url(add_query_arg(['tab' => 'dados'], $base_url)); ?>" class="nav-tab nav-tab-dados <?php echo $current_tab == 'dados' ? 'nav-tab-active' : ''; ?>">Dados</div>
                <div data-tab="observacoes" data-url="<?php echo esc_url(add_query_arg(['tab' => 'observacoes'], $base_url)); ?>" class="nav-tab nav-tab-observacoes <?php echo $current_tab == 'observacoes' ? 'nav-tab-active' : ''; ?>">Observações</div>
                <div data-tab="documentos" data-url="<?php echo esc_url(add_query_arg(['tab' => 'documentos'], $base_url)); ?>" class="nav-tab nav-tab-documentos <?php echo $current_tab == 'documentos' ? 'nav-tab-active' : ''; ?>">Documentos</div>
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
            </div>

          
            <div class="aj-meta-container">
                <?php if ( $atendimento_id > 0 && $atendimento ) :  ?>
                    <span class="meta-item">
                        <strong>Cadastrado por:</strong> 
                        <span id="aj_cadastrado_por"><?php echo $cadastrado_por_user ? esc_html( $cadastrado_por_user->display_name ) : 'N/A'; ?></span>
                    </span>
                    <span class="meta-item">
                        <strong>Data do cadastro:</strong> 
                        <span id="aj_data_cadastro"><?php echo $atendimento->data_cadastro ? esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_cadastro ) ) ) : 'N/A'; ?></span>
                    </span>
                    <span class="meta-item">
                        <strong>Alterado por:</strong> 
                        <span id="aj_alterado_por"><?php echo $alterado_por_user ? esc_html( $alterado_por_user->display_name ) : 'N/A'; ?></span>
                    </span>
                    <span class="meta-item">
                        <strong>Data da última alteração:</strong> 
                        <span id="aj_data_alteracao"><?php echo $atendimento->data_alteracao ? esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_alteracao ) ) ) : 'N/A'; ?></span>
                    </span>
                <?php endif; ?>

            </div>

<br>
<br>

            <div class="form-actions">
                <?php if ( $is_readonly ) : ?>
                    <a href="?page=atendimento-juridico" class="button aj-btn-voltar"><span class="dashicons dashicons-arrow-left-alt"></span>Voltar à Lista</a>
                <?php else : ?>
                    <button type="reset" class="button aj-btn-limpar"><span class="dashicons dashicons-trash"></span>Limpar</button>
                    <a href="?page=atendimento-juridico" class="button aj-btn-cancelar"><span class="dashicons dashicons-no-alt"></span>Cancelar</a>
                    <button type="submit" name="submit" id="submit" class="button aj-btn-salvar"><span class="dashicons dashicons-yes-alt"></span>Salvar Atendimento</button>
                <?php endif; ?>
            </div>
<br>
<br>

            </form>
            </div> <!-- Fim do .aj-form-wrapper -->
        </div>
        <?php
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