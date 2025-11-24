<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Verifica se a variável $atendimento existe e se o campo 'socios' não está vazio.
if ( ! isset( $atendimento ) || empty( $atendimento->socios ) ) {
	echo '<p>Nenhum histórico para exibir pois o sócio não foi especificado no atendimento atual.</p>';
	return;
}

$nome_socio = $atendimento->socios;
$current_atendimento_id = $atendimento->id;

// Busca atendimentos para o mesmo sócio.
$search_result = aj_search_atendimentos( [ 'adv_socio' => $nome_socio ] );
$atendimentos_historico = $search_result['results'];

// Filtra o atendimento atual da lista de histórico.
$atendimentos_historico = array_filter($atendimentos_historico, function($item) use ($current_atendimento_id) {
    return $item->id !== $current_atendimento_id;
});

?>

<?php if ( ! empty( $atendimentos_historico ) ) : ?>
    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-id">ID</th>
                <th scope="col" class="manage-column column-assunto">Assunto do atendimento</th>
                <th scope="col" class="manage-column column-protocolo">Protocolo</th>
                <th scope="col" class="manage-column column-advogado">Advogado</th>
                <th scope="col" class="manage-column column-data">Data e Hora</th>
                <th scope="col" class="manage-column column-status">Status</th>
                <th scope="col" class="manage-column column-actions">Ações</th>
            </tr>
        </thead>

        <tbody id="the-list-historico">
            <?php foreach ( $atendimentos_historico as $atendimento_hist ) : ?>
                <?php
                    $edit_url = add_query_arg( [ 'page' => 'atendimento-juridico', 'id' => $atendimento_hist->id ], admin_url('admin.php') );
                    $view_url = add_query_arg( [ 'page' => 'atendimento-juridico', 'action' => 'view', 'id' => $atendimento_hist->id ], admin_url('admin.php') );
                ?>
                <tr>
                    <td class="id column-id" data-label="ID"><?php echo esc_html( $atendimento_hist->id ); ?></td>
                    <td class="assunto column-assunto" data-label="Assunto">
                        <strong><a class="row-title" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $atendimento_hist->assunto ); ?></a></strong>
                    </td>
                    <td class="protocolo column-protocolo" data-label="Protocolo"><?php echo esc_html( $atendimento_hist->protocolo ); ?></td>
                    <td class="advogado column-advogado" data-label="Advogado"><?php echo esc_html( $atendimento_hist->advogados ); ?></td>
                    <td class="data column-data" data-label="Data"><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $atendimento_hist->data_atendimento ) ) ); ?></td>
                    <td class="status column-status" data-label="Status">
                        <?php
                            $status = $atendimento_hist->status;
                            $status_class = 'aj-status-' . sanitize_html_class( strtolower( $status ) );
                            echo '<span class="aj-status-badge ' . esc_attr( $status_class ) . '">' . esc_html( $status ) . '</span>';
                        ?>
                    </td>
                    <td class="actions column-actions" data-label="Ações">
                         <div class="aj-actions-container">
                            <a href="<?php echo esc_url( $edit_url ); ?>" title="Editar"><span class="dashicons dashicons-edit"></span></a>
                            <a href="<?php echo esc_url( $view_url ); ?>" class="aj-action-view" title="Visualizar"><span class="dashicons dashicons-visibility"></span></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>

    </table>
<?php else : ?>
    <div class="notice notice-info inline">
        <p>Não há outros atendimentos registrados para este sócio.</p>
    </div>
<?php endif; ?>