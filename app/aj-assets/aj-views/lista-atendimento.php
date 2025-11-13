<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Prepara os argumentos para a busca inicial (se houver filtros na URL)
$initial_search_args = [
    's'               => isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
    'adv_socio'       => isset( $_GET['adv_socio'] ) ? sanitize_text_field( $_GET['adv_socio'] ) : '',
    'adv_advogado'    => isset( $_GET['adv_advogado'] ) ? sanitize_text_field( $_GET['adv_advogado'] ) : '',
    'adv_tipo'        => isset( $_GET['adv_tipo'] ) ? sanitize_text_field( $_GET['adv_tipo'] ) : '',
    'adv_status'      => isset( $_GET['adv_status'] ) ? sanitize_text_field( $_GET['adv_status'] ) : '',
    'adv_data_inicio' => isset( $_GET['adv_data_inicio'] ) ? sanitize_text_field( $_GET['adv_data_inicio'] ) : '',
    'adv_data_fim'    => isset( $_GET['adv_data_fim'] ) ? sanitize_text_field( $_GET['adv_data_fim'] ) : '',
];

// Carrega os atendimentos iniciais usando a função de busca do Model
$search_result = aj_search_atendimentos( $initial_search_args );
$atendimentos = $search_result['results'];

$add_new_url = add_query_arg( [ 'page' => 'atendimento-juridico', 'action' => 'new' ] );

// Recupera os valores dos filtros da URL para preencher os campos, se existirem.
$search_term      = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$adv_socio        = isset( $_GET['adv_socio'] ) ? sanitize_text_field( $_GET['adv_socio'] ) : '';
$adv_advogado     = isset( $_GET['adv_advogado'] ) ? sanitize_text_field( $_GET['adv_advogado'] ) : '';
$adv_tipo         = isset( $_GET['adv_tipo'] ) ? sanitize_text_field( $_GET['adv_tipo'] ) : '';
$adv_status       = isset( $_GET['adv_status'] ) ? sanitize_text_field( $_GET['adv_status'] ) : '';
$adv_data_inicio  = isset( $_GET['adv_data_inicio'] ) ? sanitize_text_field( $_GET['adv_data_inicio'] ) : '';
$adv_data_fim     = isset( $_GET['adv_data_fim'] ) ? sanitize_text_field( $_GET['adv_data_fim'] ) : '';
?>
<div class="wrap aj-list-page-wrapper">
    <div class="aj-list-header">
        <div class="aj-header-title-section">
            <img src="<?php echo esc_url( plugins_url( 'atj.png', __FILE__ ) ); ?>" alt="Logo Atendimento Jurídico" class="aj-header-logo">
            <h1 class="aj-list-title">Controle de atendimentos jurídicos</h1>
        </div>
        <p class="aj-list-subtitle">Organize e acompanhe todos os atendimentos realizados. Visualize históricos, consulte informações importantes e registre novos atendimentos de forma simples e rápida.</p>
    </div>

    <form method="get" id="aj-search-form">
        <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />


        <!-- Container da Pesquisa Avançada -->
        <div class="aj-top-container collapsed">
            <div class="aj-top-container-header">
                <h4>Pesquisa avançada</h4>
                <span class="dashicons dashicons-arrow-down-alt2 aj-toggle-icon"></span>
            </div>
            <div class="aj-top-container-body" style="display: none;">
                <div class="aj-advanced-filters">
                    <div class="aj-filter-group">
                        <label for="aj-search-input">Assunto</label>
                        <input type="search" id="aj-search-input" name="s" value="<?php echo esc_attr( $search_term ); ?>" placeholder="Pesquisar por assunto, protocolo...">
                    </div>
                    <div class="aj-filter-group">
                        <label>Período de atendimento</label>
                        <div class="aj-date-range">
                            <input type="date" name="adv_data_inicio" value="<?php echo esc_attr($adv_data_inicio); ?>">
                            <span>até</span>
                            <input type="date" name="adv_data_fim" value="<?php echo esc_attr($adv_data_fim); ?>">
                        </div>
                    </div>

                    <!-- Nova linha de filtros -->
                    <div class="aj-filter-group">
                        <label for="adv_status">Status</label>
                        <select id="adv_status" name="adv_status">
                            <option value="">Todos</option>
                            <option value="AGUARDANDO" <?php selected($adv_status, 'AGUARDANDO'); ?>>AGUARDANDO</option>
                            <option value="PENDENTE" <?php selected($adv_status, 'PENDENTE'); ?>>PENDENTE</option>
                            <option value="CANCELADO" <?php selected($adv_status, 'CANCELADO'); ?>>CANCELADO</option>
                            <option value="ATENDIDO" <?php selected($adv_status, 'ATENDIDO'); ?>>ATENDIDO</option>
                            <option value="INDEFERIDO" <?php selected($adv_status, 'INDEFERIDO'); ?>>INDEFERIDO</option>
                        </select>
                    </div>
                    <div class="aj-filter-group">
                        <label for="adv_tipo">Tipo de atendimento</label>
                        <select id="adv_tipo" name="adv_tipo">
                            <option value="">Todos</option>
                            <option value="AEROPORTOS" <?php selected($adv_tipo, 'AEROPORTOS'); ?>>AEROPORTOS</option>
                            <option value="AII" <?php selected($adv_tipo, 'AII'); ?>>AII</option>
                            <option value="ANDAMENTO PROCESSUAL" <?php selected($adv_tipo, 'ANDAMENTO PROCESSUAL'); ?>>ANDAMENTO PROCESSUAL</option>
                            <option value="OUTROS" <?php selected($adv_tipo, 'OUTROS'); ?>>OUTROS</option>
                        </select>
                    </div>

                    <!-- Nova linha de filtros -->
                    <div class="aj-filter-group">
                        <label for="adv_advogado">Advogado</label>
                        <input type="text" id="adv_advogado" name="adv_advogado" value="<?php echo esc_attr($adv_advogado); ?>">
                    </div>
                    <div class="aj-filter-group">
                        <label for="adv_socio">Sócio</label>
                        <input type="text" id="adv_socio" name="adv_socio" value="<?php echo esc_attr($adv_socio); ?>">
                    </div>
                </div>
                <div class="aj-advanced-filters-actions">
                    <button type="button" class="button aj-clear-filters-btn">Limpar Filtros</button>
                    <button type="submit" class="button aj-advanced-search-btn" id="aj-advanced-search-btn">
                        <span class="dashicons dashicons-search"></span> Realizar pesquisa
                    </button>
                </div>
            </div>
        </div>

    <div id="aj-results-notice" class="aj-search-results-notice" style="display: none;"></div>

    <div class="aj-form-container"> <!-- Adicionando o container para o estilo -->
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <th scope="col" id="id" class="manage-column column-id">ID</th>
                <th scope="col" id="assunto" class="manage-column column-assunto">Assunto do atendimento</th>
                <th scope="col" id="protocolo" class="manage-column column-protocolo">Protocolo</th>
                <th scope="col" id="advogado" class="manage-column column-advogado">Advogado</th>
                <th scope="col" id="socio" class="manage-column column-socio">Sócio</th>
                <th scope="col" id="data" class="manage-column column-data">Data e Hora</th>
                <th scope="col" id="status" class="manage-column column-status">Status</th>
                <th scope="col" id="actions" class="manage-column column-actions">Ações</th>
            </tr>
        </thead>

        <tbody id="the-list" class="ui-sortable">
            <?php if ( ! empty( $atendimentos ) ) : ?>
                <?php foreach ( $atendimentos as $atendimento ) : ?>
                    <?php
                        // Garante que os URLs de edição e visualização apontem para admin.php
                        $edit_url = add_query_arg( [ 'page' => 'atendimento-juridico', 'id' => $atendimento->id ], admin_url('admin.php') );
                        $view_url = add_query_arg( [ 'page' => 'atendimento-juridico', 'action' => 'view', 'id' => $atendimento->id ], admin_url('admin.php') );
                    ?>
                    <tr class="aj-card-row">
                        <td class="id column-id" data-label="ID"><?php echo is_object( $atendimento ) ? esc_html( $atendimento->id ) : ''; ?></td>
                        <td class="assunto column-assunto" data-label="Assunto">
                            <strong><a class="row-title" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $atendimento->assunto ); ?></a></strong>
                        </td>
                        <td class="protocolo column-protocolo" data-label="Protocolo"><?php echo esc_html( $atendimento->protocolo ); ?></td>
                        <td class="advogado column-advogado" data-label="Advogado"><?php echo esc_html( $atendimento->advogados ); ?></td>
                        <td class="socio column-socio" data-label="Sócio"><?php echo esc_html( $atendimento->socios ); ?></td>
                        <td class="data column-data" data-label="Data"><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_atendimento ) ) ); ?></td>
                        <td class="status column-status" data-label="Status">
                            <?php
                                $status = $atendimento->status;
                                $status_class = 'aj-status-' . sanitize_html_class( strtolower( $status ) );
                                echo '<span class="aj-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( $status ) . '</span>';
                            ?>
                        </td>
                        <td class="actions column-actions" data-label="Ações">
                            <div class="aj-actions-container">
                                <button class="aj-actions-button dashicons dashicons-ellipsis"></button>
                                <div class="aj-actions-dropdown" style="display: none;">
                                    <ul>
                                        <li><a href="#"><span class="dashicons dashicons-calendar-alt"></span> Criar evento</a></li>
                                        <li><a href="#"><span class="dashicons dashicons-redo"></span> Converter em processo</a></li>
                                        <li class="aj-submenu-container">
                                            <a href="#"><span class="dashicons dashicons-portfolio"></span> Documentos <span class="dashicons dashicons-arrow-left-alt2"></span></a>
                                            <div class="aj-actions-submenu">
                                                <ul>
                                                    <li><a href="#"><span class="dashicons dashicons-printer"></span> Resumo</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-text-page"></span> Comprovante</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-whatsapp"></span> Enviar Via Whatsapp</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-admin-users"></span> Procuração</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-businessman"></span> Procuração Advogado</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-money-alt"></span> Contratos Honorarios</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-text-page"></span> Declaração Pobreza</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-admin-home"></span> Declaração Residencia</a></li>
                                                    <li><a href="#"><span class="dashicons dashicons-groups"></span> Acordo Extrajudicial</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="aj-submenu-container">
                                            <a href="#"><span class="dashicons dashicons-admin-generic"></span> Ações <span class="dashicons dashicons-arrow-left-alt2"></span></a>
                                            <div class="aj-actions-submenu">
                                                <ul>
                                                    <li><a href="<?php echo esc_url( $edit_url ); ?>"><span class="dashicons dashicons-edit"></span> Editar</a></li>
                                                    <li><a href="<?php echo esc_url( $view_url ); ?>" class="aj-action-view"><span class="dashicons dashicons-visibility"></span> Visualizar</a></li>
                                                    <li><a href="#" class="aj-action-delete" data-id="<?php echo esc_attr( $atendimento->id ); ?>"><span class="dashicons dashicons-trash"></span> Excluir</a></li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="no-items">
                    <td class="colspanchange" colspan="8">Nenhum atendimento encontrado.</td>
                </tr>
            <?php endif; ?>
        </tbody>

        <tfoot>
           
            <?php // Você pode copiar o conteúdo de <thead> aqui se desejar. ?>
        </tfoot>
    </table>

    <div id="aj-pagination-container"></div>

    </div> <!-- Fim do .aj-form-container -->
    <!-- Botão flutuante para adicionar novo atendimento -->
    <a href="<?php echo esc_url( $add_new_url ); ?>" class="aj-fab-add-new dashicons dashicons-plus-alt"></a>
</div>