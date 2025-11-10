<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'aj_atendimentos';


$atendimentos = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC" );


$add_new_url = add_query_arg( [ 'page' => 'atendimento-juridico', 'action' => 'new' ] );

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Atendimentos Jurídicos</h1>
    <a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action">Adicionar Novo</a>

    <hr class="wp-header-end">

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

        <tbody id="the-list">
            <?php if ( ! empty( $atendimentos ) ) : ?>
                <?php foreach ( $atendimentos as $atendimento ) : ?>
                    <?php
                     
                        $edit_url = add_query_arg( [ 'page' => 'atendimento-juridico', 'id' => $atendimento->id ] );
                    ?>
                    <tr>
                        <td class="id column-id"><?php echo esc_html( $atendimento->id ); ?></td>
                        <td class="assunto column-assunto">
                            <strong><a class="row-title" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $atendimento->assunto ); ?></a></strong>
                        </td>
                        <td class="protocolo column-protocolo"><?php echo esc_html( $atendimento->protocolo ); ?></td>
                        <td class="advogado column-advogado"><?php echo esc_html( $atendimento->advogados ); ?></td>
                        <td class="socio column-socio"><?php echo esc_html( $atendimento->socios ); ?></td>
                        <td class="data column-data"><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $atendimento->data_atendimento ) ) ); ?></td>
                        <td class="status column-status">
                            <?php
                                $status = $atendimento->status;
                                $status_class = 'aj-status-' . sanitize_html_class( strtolower( $status ) );
                                echo '<span class="aj-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( $status ) . '</span>';
                            ?>
                        </td>
                        <td class="actions column-actions">
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
                                                    <li><a href="#"><span class="dashicons dashicons-visibility"></span> Visualizar</a></li>
                                                    <li><a href="#" class="aj-action-delete"><span class="dashicons dashicons-trash"></span> Excluir</a></li>
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
            <!-- O rodapé da tabela é igual ao cabeçalho -->
            <?php // Você pode copiar o conteúdo de <thead> aqui se desejar. ?>
        </tfoot>
    </table>
    </div> <!-- Fim do .aj-form-container -->
</div>