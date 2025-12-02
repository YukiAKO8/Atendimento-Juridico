<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retorna um único atendimento pelo seu ID.
 *
 * @param int $id O ID do atendimento.
 * @return object|null O objeto do atendimento ou nulo se não for encontrado.
 */
function aj_get_atendimento_by_id( $id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';
    return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ) );
}

/**
 * Retorna todos os atendimentos, ordenados pela data mais recente.
 *
 * @return array A lista de atendimentos.
 */
function aj_get_all_atendimentos() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';
    return $wpdb->get_results( "SELECT id, assunto, protocolo, advogados, socios, data_atendimento, status, tipo_atendimento FROM {$table_name} ORDER BY data_atendimento DESC" );
}

/**
 * Insere um novo atendimento no banco de dados.
 *
 * @param array $data Os dados a serem inseridos.
 * @return int|false O ID do novo registro ou false em caso de erro.
 */
function aj_insert_atendimento( $data ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';

    $result = $wpdb->insert( $table_name, $data );

    if ( $result === false ) {
        return false;
    }

    return $wpdb->insert_id;
}

/**
 * Atualiza um atendimento existente no banco de dados.
 *
 * @param int $id O ID do atendimento a ser atualizado.
 * @param array $data Os novos dados.
 * @return bool True em sucesso, false em erro.
 */
function aj_update_atendimento( $id, $data ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';

    $result = $wpdb->update( $table_name, $data, [ 'id' => $id ] );

    return $result !== false;
}

/**
 * Exclui um atendimento do banco de dados.
 *
 * @param int $id O ID do atendimento a ser excluído.
 * @return bool True em sucesso, false em erro.
 */
function aj_delete_atendimento( $id ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';

    $result = $wpdb->delete(
        $table_name,
        [ 'id' => $id ],
        [ '%d' ]
    );

    return $result !== false;
}

/**
 * Busca atendimentos no banco de dados com base em múltiplos critérios.
 *
 * @param array $args Argumentos para a busca.
 * @return array Um array contendo 'results' e 'total'.
 */
function aj_search_atendimentos( $args = [] ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';

    // Pega os argumentos com valores padrão
    $search_term     = isset( $args['s'] ) ? $args['s'] : '';
    $socio           = isset( $args['adv_socio'] ) ? $args['adv_socio'] : '';
    $advogado        = isset( $args['adv_advogado'] ) ? $args['adv_advogado'] : '';
    $tipo            = isset( $args['adv_tipo'] ) ? $args['adv_tipo'] : '';
    $status          = isset( $args['adv_status'] ) ? $args['adv_status'] : '';
    $data_inicio     = isset( $args['adv_data_inicio'] ) ? $args['adv_data_inicio'] : '';
    $data_fim        = isset( $args['adv_data_fim'] ) ? $args['adv_data_fim'] : '';
    $page            = isset( $args['page'] ) ? absint( $args['page'] ) : 1;
    $per_page        = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 8; // Itens por página
    $nopaging        = isset( $args['nopaging'] ) && $args['nopaging'];
    $offset          = ( $page - 1 ) * $per_page;

    $from_clause = "FROM $table_name";
    $where_clauses = [];
    $params = [];

    // Verifica se algum filtro foi aplicado
    $is_searching = ! empty( $search_term ) || ! empty( $socio ) || ! empty( $advogado ) || ! empty( $tipo ) || ! empty( $status ) || ! empty( $data_inicio ) || ! empty( $data_fim );

    if ( ! empty( $search_term ) ) {
        $like_term = '%' . $wpdb->esc_like( $search_term ) . '%';
        $where_clauses[] = "(assunto LIKE %s OR socios LIKE %s OR protocolo LIKE %s OR advogados LIKE %s)";
        // Adiciona o parâmetro 4 vezes, uma para cada campo no OR
        array_push( $params, $like_term, $like_term, $like_term, $like_term );
    }
    if ( ! empty( $socio ) ) {
        $where_clauses[] = "socios LIKE %s";
        $params[] = '%' . $wpdb->esc_like( $socio ) . '%';
    }
    if ( ! empty( $advogado ) ) {
        $where_clauses[] = "advogados LIKE %s";
        $params[] = '%' . $wpdb->esc_like( $advogado ) . '%';
    }
    if ( ! empty( $tipo ) ) {
        $where_clauses[] = "tipo_atendimento = %s";
        $params[] = $tipo;
    }
    if ( ! empty( $status ) ) {
        $where_clauses[] = "status = %s";
        $params[] = $status;
    }
    if ( ! empty( $data_inicio ) ) {
        $where_clauses[] = "DATE(data_atendimento) >= %s";
        $params[] = $data_inicio;
    }
    if ( ! empty( $data_fim ) ) {
        $where_clauses[] = "DATE(data_atendimento) <= %s";
        $params[] = $data_fim;
    }

    if ( ! $is_searching ) {
        // Comportamento padrão se nenhum filtro for aplicado: busca atendimentos do mês atual.
        $where_clauses[] = "MONTH(data_atendimento) = MONTH(CURDATE()) AND YEAR(data_atendimento) = YEAR(CURDATE())";
    }

    $where_query = "";
    if ( ! empty( $where_clauses ) ) {
        $where_query = " WHERE " . implode( ' AND ', $where_clauses );
    }

    // Query para contar o total de resultados
    $total_query = "SELECT COUNT(id) " . $from_clause . $where_query;
    $total = $wpdb->get_var( $wpdb->prepare( $total_query, $params ) );

    // Query para buscar os resultados da página atual
    $main_query = "SELECT * " . $from_clause . $where_query . " ORDER BY id DESC";

    if ( ! $nopaging ) {
        $main_query .= " LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
    }

    $results = $wpdb->get_results( $wpdb->prepare( $main_query, $params ) );

    return [ 'results' => $results, 'total' => (int) $total ];
}

/**
 * Registra o endpoint AJAX para buscar os nomes dos sócios.
 * Esta função é chamada pelo JavaScript para popular o dropdown dinâmico.
 */
add_action('wp_ajax_aj_get_socios', 'aj_retorna_nomes_socios');

/**
 * Busca todos os posts do Custom Post Type 'socio' e retorna seus títulos.
 * A função verifica o nonce para segurança.
 */
function aj_retorna_nomes_socios() {
    $socios = get_posts([
        'post_type'      => 'socio',
        'post_status'    => 'publish',
        'numberposts'    => -1, // -1 para buscar todos
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);

    // Extrai apenas os títulos (nomes) dos posts
    $nomes = wp_list_pluck($socios, 'post_title');

    wp_send_json_success($nomes);
}

/**
 * Registra o endpoint AJAX para buscar os nomes dos advogados.
 */
add_action('wp_ajax_aj_buscar_advogados', 'aj_buscar_advogados_handler');

/**
 * Busca nomes de advogados distintos da tabela de atendimentos.
 * Como o campo 'advogados' pode ter múltiplos nomes separados por vírgula,
 * esta função busca todos, os separa e retorna uma lista única.
 */
function aj_buscar_advogados_handler() {
    // Nonce não é verificado aqui pois a informação não é sensível,
    // mas poderia ser adicionado para maior segurança.
    global $wpdb;
    $table_name = $wpdb->prefix . 'aj_atendimentos';

    // Pega todos os valores não nulos da coluna 'advogados'
    $results = $wpdb->get_col("SELECT DISTINCT advogados FROM {$table_name} WHERE advogados IS NOT NULL AND advogados != ''");

    $all_names = [];
    foreach ($results as $result) {
        // Separa nomes por vírgula, remove espaços em branco e adiciona ao array principal
        $names = array_map('trim', explode(',', $result));
        $all_names = array_merge($all_names, $names);
    }

    // Remove duplicados, filtra valores vazios e re-indexa o array
    wp_send_json_success(array_values(array_unique(array_filter($all_names))));
}
