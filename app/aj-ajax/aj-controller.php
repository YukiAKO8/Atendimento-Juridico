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
    // Verifica se estamos na página de edição/criação ou na página de listagem.
    $atendimento_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : '';

    if ( $atendimento_id > 0 || $action === 'new' ) {
        // --- RENDERIZA A PÁGINA DE FORMULÁRIO (EDIÇÃO/NOVO) ---

        // Busca os dados do atendimento no banco de dados se for uma edição.
        $atendimento = null;
        $cadastrado_por_user = null;
        $alterado_por_user = null;

        if ( $atendimento_id > 0 ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'aj_atendimentos';
            $atendimento = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $atendimento_id ) );

            // Se o atendimento não for encontrado, exibe um erro.
            if ( ! $atendimento ) {
                wp_die( 'Atendimento não encontrado.' );
            }

            // Busca os dados dos usuários para exibição.
            if ( $atendimento->cadastrado_por ) {
                $cadastrado_por_user = get_userdata( $atendimento->cadastrado_por );
            }
            if ( $atendimento->alterado_por ) {
                $alterado_por_user = get_userdata( $atendimento->alterado_por );
            }
        }

        // Obtém a aba atual a partir desta mesma controller.
        $current_tab = aj_get_current_tab();
        // Obtém o usuário atual
        $current_user = wp_get_current_user();
        ?>
        <div class="wrap">
            <h1><?php echo $atendimento_id > 0 ? 'Editar Atendimento' : 'Adicionar Novo Atendimento'; ?></h1>

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
                <?php if ( $atendimento ) :  ?>
                    <span class="meta-item">
                        <strong>Cadastrado por:</strong> 
                        <span id="aj_cadastrado_por"><?php echo $cadastrado_por_user ? esc_html( $cadastrado_por_user->display_name ) : 'N/A'; ?></span>
                    </span>
                    <span class="meta-item">
                        <strong>Data do cadastro:</strong> 
                        <span id="aj_data_cadastro"><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_cadastro ) ) ); ?></span>
                    </span>
                    <span class="meta-item">
                        <strong>Alterado por:</strong> 
                        <span id="aj_alterado_por"><?php echo $alterado_por_user ? esc_html( $alterado_por_user->display_name ) : 'N/A'; ?></span>
                    </span>
                    <span class="meta-item">
                        <strong>Data da última alteração:</strong> 
                        <span id="aj_data_alteracao"><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_alteracao ) ) ); ?></span>
                    </span>
                <?php endif; ?>

            </div>

            <div class="form-actions">
                <button type="reset" class="button aj-btn-limpar"><span class="dashicons dashicons-trash"></span>Limpar</button>
                <a href="?page=atendimento-juridico" class="button aj-btn-cancelar"><span class="dashicons dashicons-no-alt"></span>Cancelar</a>
                <button type="submit" name="submit" id="submit" class="button aj-btn-salvar"><span class="dashicons dashicons-yes-alt"></span>Salvar Atendimento</button>
            </div>

            </form>
        </div>
        <?php
    } else {
     
        require_once plugin_dir_path( __DIR__ ) . 'aj-assets/aj-views/lista-atendimento.php';
    }
}

function aj_processar_formulario() {
  
    if ( ! isset( $_POST['submit'] ) || ! isset( $_POST['aj_atendimento_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['aj_atendimento_nonce'], 'aj_salvar_atendimento' ) ) {
        wp_die( 'A verificação de segurança falhou.' );
    }

    
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';

 
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
        $result = $wpdb->update( $table_name, $data, $where );

    } else {
       
        $data['cadastrado_por'] = get_current_user_id(); // Guarda quem cadastrou
        $result = $wpdb->insert(
            $table_name,
            $data
        );
        $atendimento_id = $wpdb->insert_id; // Pega o ID do novo registro
    }

 
    if ( $result === false ) {
        // Adiciona uma mensagem de erro
        add_action( 'admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>Ocorreu um erro ao salvar o atendimento.</p></div>';
        });
        error_log( "Erro ao salvar o atendimento: " . $wpdb->last_error );
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