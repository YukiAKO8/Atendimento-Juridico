<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Inicializa as variáveis com valores existentes ou vazios para novos atendimentos
$socios = isset( $atendimento->socios ) ? $atendimento->socios : '';
$data_atendimento_val = isset( $atendimento->data_atendimento ) ? date( 'Y-m-d', strtotime( $atendimento->data_atendimento ) ) : '';
$hora_atendimento_val = isset( $atendimento->data_atendimento ) ? date( 'H:i', strtotime( $atendimento->data_atendimento ) ) : '';
?>

    <div class="form-row">
        <!-- Adiciona um wrapper para o posicionamento correto do dropdown -->
        <div class="form-group aj-socio-wrapper">
            <div id="aj-pesquisar-socio-inicial">
            <label for="aj_socios">Sócios *</label>
            <input type="text" id="aj_socios" name="aj_socios" value="<?php echo esc_attr( $socios ); ?>" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
            <button type="button" class="aj-socio-search-button dashicons dashicons-search" <?php echo $is_readonly ? 'disabled' : ''; ?>></button>
            </div>
                        <div id="aj-socio-selecionado" style="display:none;">
            <label for="aj_socios">Sócios *</label>
            <button type="button" class="aj-socio-search-button dashicons dashicons-search" <?php echo $is_readonly ? 'disabled' : ''; ?>></button>
            <input type="text" id="aj-resultado-socio-selecionado" name="aj-resultado-socio-selecionado"" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
           <input type="hidden" id="aj_socios_id" name="aj_socios_id">
            </div><!-- A caixa de sugestões é controlada via CSS e JS -->
            <div id="aj_socios_suggestions"></div>
        </div>
        <div class="form-group">
            <label for="aj_situacao">Situação</label>
            <input type="text" id="aj_situacao" name="aj_situacao" value="<?php echo isset( $atendimento->situacao ) ? esc_attr( $atendimento->situacao ) : ''; ?>" <?php echo $is_readonly ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="aj_empresa">Empresa</label>
            <input type="text" id="aj_empresa" name="aj_empresa" value="<?php echo isset( $atendimento->empresa ) ? esc_attr( $atendimento->empresa ) : ''; ?>" <?php echo $is_readonly ? 'disabled' : ''; ?>>
        </div>
    </div>


    <div class="form-row">
        <div class="form-group">
            <label for="aj_funcao">Função</label>
            <input type="text" id="aj_funcao" name="aj_funcao" value="<?php echo isset( $atendimento->funcao ) ? esc_attr( $atendimento->funcao ) : ''; ?>" <?php echo $is_readonly ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group aj-advogado-wrapper">
            <label for="aj_advogados">Advogados *</label>
            <select id="aj_advogados" name="aj_advogados" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
                <option value="">-- Selecione um Advogado --</option>
                <?php
                $advogados_lista = [
                    "BRENDA HELLEN DE SOUZA AZEVEDO",
                    "DIEGO BOUCHARDET",
                    "DIEGO GARCIA MENDONÇA",
                    "DOUGLAS NEWTON QUEIROZ",
                    "ESTER CASTRO FERNANDES",
                    "FLÁVIA MARIA GOMES PEREIRA",
                    "GIOVANA LABIGALINI MARTINS",
                    "IGOR MARTINS DIAS",
                    "JULIANA DOS SANTOS",
                    "KAIQUE FERREIRA DOS SANTOS HARADA",
                    "KARINA PREMERO",
                    "KARINA TAVARES",
                    "LEONARDO LINS CAMELO DA SILVA",
                    "LUCAS GABRIEL AGUIAR CASANTE FERREIRA",
                    "LUIZ ROQUE",
                    "LUIZ ROQUE MIRANDA CARDIA",
                    "MARIA EDUARDA TEIXEIRA FAUSTINO",
                    "MARIANA RAMOS ANDRADE",
                    "MÁRCIA CRISTINA GEMAQUE FURTADO",
                    "NÃO INFORMADO",
                    "PEDRO DANIEL BLANCO ALVES",
                    "RAFAEL BARBOSA DA SILVA",
                    "RAFAEL VARJÃO DOS SANTOS MOURA",
                    "RAFAEL VELOSO FREITAS",
                    "ROBERT FARIAS",
                    "TONY DIONIZIO ALVES COSTA",
                    "VIVIAN OROSCO MICELLI",
                    "VITÓRIA SILVÉRIO",
                ];
                $advogado_selecionado = isset( $atendimento->advogados ) ? $atendimento->advogados : '';
                foreach ( $advogados_lista as $adv ) {
                    echo '<option value="' . esc_attr( $adv ) . '" ' . selected( $advogado_selecionado, $adv, false ) . '>' . esc_html( $adv ) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="aj_tipo_atendimento">Tipo de atendimento *</label>
            <select id="aj_tipo_atendimento" name="aj_tipo_atendimento" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
                <option value="">-- Selecione --</option>
                <option value="AEROPORTOS" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'AEROPORTOS' ); ?>>AEROPORTOS</option>
                <option value="AII" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'AII' ); ?>>AII</option>
                <option value="ANDAMENTO PROCESSUAL" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'ANDAMENTO PROCESSUAL' ); ?>>ANDAMENTO PROCESSUAL</option>
                <option value="DECLARACAO DE ASSOCIACAO" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'DECLARACAO DE ASSOCIACAO' ); ?>>DECLARAÇÃO DE ASSOCIAÇÃO</option>
                <option value="DEMANDAS ANAC" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'DEMANDAS ANAC' ); ?>>DEMANDAS ANAC</option>
                <option value="DEMANDAS INSS" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'DEMANDAS INSS' ); ?>>DEMANDAS INSS</option>
                <option value="DENUNCIA" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'DENUNCIA' ); ?>>DENÚNCIA</option>
                <option value="DUVIDAS RESCISAO" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'DUVIDAS RESCISAO' ); ?>>DÚVIDAS RESCISÃO</option>
                <option value="EMISSAO PPP" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'EMISSAO PPP' ); ?>>EMISSÃO PPP</option>
                <option value="OUTROS" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'OUTROS' ); ?>>OUTROS</option>
                <option value="PASSE LIVRE" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'PASSE LIVRE' ); ?>>PASSE LIVRE</option>
                <option value="PLANEJAMENTO PREVIDENCIARIO" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'PLANEJAMENTO PREVIDENCIARIO' ); ?>>PLANEJAMENTO PREVIDENCIÁRIO</option>
                <option value="REGULAMENTACAO" <?php selected( isset( $atendimento->tipo_atendimento ) ? $atendimento->tipo_atendimento : '', 'REGULAMENTACAO' ); ?>>REGULAMENTAÇÃO</option>
            </select>
        </div>
    </div>


    <div class="form-row">
        <div class="form-group">
            <label for="aj_forma_atendimento">Forma de atendimento *</label>
            <select id="aj_forma_atendimento" name="aj_forma_atendimento" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
                <option value="">-- Selecione --</option>
                <option value="PRESENCIAL" <?php selected( isset( $atendimento->forma_atendimento ) ? $atendimento->forma_atendimento : '', 'PRESENCIAL' ); ?>>PRESENCIAL</option>
                <option value="TELEFONE" <?php selected( isset( $atendimento->forma_atendimento ) ? $atendimento->forma_atendimento : '', 'TELEFONE' ); ?>>TELEFONE</option>
                <option value="VIDEOCHAMADA" <?php selected( isset( $atendimento->forma_atendimento ) ? $atendimento->forma_atendimento : '', 'VIDEOCHAMADA' ); ?>>VIDEOCHAMADA</option>
                <option value="E-MAIL" <?php selected( isset( $atendimento->forma_atendimento ) ? $atendimento->forma_atendimento : '', 'E-MAIL' ); ?>>E-MAIL</option>
                <option value="WHATSAPP" <?php selected( isset( $atendimento->forma_atendimento ) ? $atendimento->forma_atendimento : '', 'WHATSAPP' ); ?>>WHATSAPP</option>
            </select>
        </div>
        <div class="form-group form-group-status">
            <label for="aj_status">Status *</label>
            <select id="aj_status" name="aj_status" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
                <option value="">-- Selecione --</option>
                <option value="AGUARDANDO" <?php selected( isset( $atendimento->status ) ? $atendimento->status : '', 'AGUARDANDO' ); ?>>AGUARDANDO</option>
                <option value="PENDENTE" <?php selected( isset( $atendimento->status ) ? $atendimento->status : '', 'PENDENTE' ); ?>>PENDENTE</option>
                <option value="CANCELADO" <?php selected( isset( $atendimento->status ) ? $atendimento->status : '', 'CANCELADO' ); ?>>CANCELADO</option>
                <option value="ATENDIDO" <?php selected( isset( $atendimento->status ) ? $atendimento->status : '', 'ATENDIDO' ); ?>>ATENDIDO</option>
                <option value="INDEFERIDO" <?php selected( isset( $atendimento->status ) ? $atendimento->status : '', 'INDEFERIDO' ); ?>>INDEFERIDO</option>
            </select>
        </div>
        <div class="form-group">
            <label for="aj_assunto">Assunto do atendimento *</label>
            <textarea id="aj_assunto" name="aj_assunto" rows="1" required <?php echo $is_readonly ? 'disabled' : ''; ?>><?php echo isset( $atendimento->assunto ) ? esc_textarea( $atendimento->assunto ) : ''; ?></textarea>
        </div>
    </div>


    <div class="form-row">
        <div class="form-group">
            <label for="aj_protocolo">Nº protocolo</label>
            <input type="text" id="aj_protocolo" name="aj_protocolo" value="<?php echo isset( $atendimento->protocolo ) ? esc_attr( $atendimento->protocolo ) : ''; ?>" readonly disabled>
        </div>
        <div class="form-group">
            <label>Entrada de processo?</label>
            <div class="form-group-checkbox" tabindex="0">
                <input type="checkbox" id="aj_entrada_processo" name="aj_entrada_processo" value="1" <?php checked( isset( $atendimento->entrada_processo ) ? $atendimento->entrada_processo : 0, 1 ); ?> <?php echo $is_readonly ? 'disabled' : ''; ?>>
                <span>Não</span>
            </div>
        </div>
        <div class="form-group">
            <label for="aj_data_atendimento">Data do atendimento *</label>
            <input type="date" id="aj_data_atendimento" name="aj_data_atendimento" value="<?php echo esc_attr( $data_atendimento_val ); ?>" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
        </div>
        <div class="form-group">
            <label for="aj_hora_atendimento">Hora do atendimento *</label>
            <input type="time" id="aj_hora_atendimento" name="aj_hora_atendimento" value="<?php echo esc_attr( $hora_atendimento_val ); ?>" required <?php echo $is_readonly ? 'disabled' : ''; ?>>
        </div>
    </div>