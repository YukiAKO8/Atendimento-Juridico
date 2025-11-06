<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <div class="aj-form-container">
        <form id="aj-atendimento-form" method="post">
            <!-- Linha 1: Sócios, Situação, Empresa -->
            <div class="form-row">
                <div class="form-group">
                    <label for="aj_socios">Sócios *</label>
                    <input type="text" id="aj_socios" name="aj_socios" required>
                </div>
                <div class="form-group">
                    <label for="aj_situacao">Situação</label>
                    <input type="text" id="aj_situacao" name="aj_situacao">
                </div>
                <div class="form-group">
                    <label for="aj_empresa">Empresa</label>
                    <input type="text" id="aj_empresa" name="aj_empresa">
                </div>
            </div>

            <!-- Linha 2: Função, Advogados, Tipo de atendimento -->
            <div class="form-row">
                <div class="form-group">
                    <label for="aj_funcao">Função</label>
                    <input type="text" id="aj_funcao" name="aj_funcao">
                </div>
                <div class="form-group">
                    <label for="aj_advogados">Advogados *</label>
                    <input type="text" id="aj_advogados" name="aj_advogados" required>
                </div>
                <div class="form-group">
                    <label for="aj_tipo_atendimento">Tipo de atendimento *</label>
                    <select id="aj_tipo_atendimento" name="aj_tipo_atendimento" required>
                        <option value="">-- Selecione --</option>
                        <option value="AEROPORTOS">AEROPORTOS</option>
                        <option value="AII">AII</option>
                        <option value="ANDAMENTO PROCESSUAL">ANDAMENTO PROCESSUAL</option>
                        <option value="DECLARACAO DE ASSOCIACAO">DECLARAÇÃO DE ASSOCIAÇÃO</option>
                        <option value="DEMANDAS ANAC">DEMANDAS ANAC</option>
                        <option value="DEMANDAS INSS">DEMANDAS INSS</option>
                        <option value="DENUNCIA">DENÚNCIA</option>
                        <option value="DUVIDAS RESCISAO">DÚVIDAS RESCISÃO</option>
                        <option value="EMISSAO PPP">EMISSÃO PPP</option>
                        <option value="OUTROS">OUTROS</option>
                        <option value="PASSE LIVRE">PASSE LIVRE</option>
                        <option value="PLANEJAMENTO PREVIDENCIARIO">PLANEJAMENTO PREVIDENCIÁRIO</option>
                        <option value="REGULAMENTACAO">REGULAMENTAÇÃO</option>
                    </select>
                </div>
            </div>

            <!-- Linha 3: Forma de atendimento, Status, Assunto do atendimento -->
            <div class="form-row">
                <div class="form-group">
                    <label for="aj_forma_atendimento">Forma de atendimento *</label>
                    <select id="aj_forma_atendimento" name="aj_forma_atendimento" required>
                        <option value="">-- Selecione --</option>
                        <option value="PRESENCIAL">PRESENCIAL</option>
                        <option value="TELEFONE">TELEFONE</option>
                        <option value="VIDEOCHAMADA">VIDEOCHAMADA</option>
                        <option value="E-MAIL">E-MAIL</option>
                        <option value="WHATSAPP">WHATSAPP</option>
                    </select>
                </div>
                <div class="form-group form-group-status">
                    <label for="aj_status">Status *</label>
                    <select id="aj_status" name="aj_status" required>
                        <option value="">-- Selecione --</option>
                        <option value="AGUARDANDO">AGUARDANDO</option>
                        <option value="PENDENTE">PENDENTE</option>
                        <option value="CANCELADO">CANCELADO</option>
                        <option value="ATENDIDO">ATENDIDO</option>
                        <option value="INDEFERIDO">INDEFERIDO</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="aj_assunto">Assunto do atendimento *</label>
                    <textarea id="aj_assunto" name="aj_assunto" rows="1" required></textarea>
                </div>
            </div>

            <!-- Linha 4: Nº protocolo, Entrada de processo?, Data e Hora -->
            <div class="form-row">
                <div class="form-group">
                    <label for="aj_protocolo">Nº protocolo</label>
                    <input type="text" id="aj_protocolo" name="aj_protocolo">
                </div>
                <div class="form-group">
                    <label>Entrada de processo?</label>
                    <div class="form-group-checkbox" tabindex="0">
                        <input type="checkbox" id="aj_entrada_processo" name="aj_entrada_processo" value="1">
                        <span>Não</span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="aj_data_atendimento">Data do atendimento *</label>
                    <input type="date" id="aj_data_atendimento" name="aj_data_atendimento" required>
                </div>
                <div class="form-group">
                    <label for="aj_hora_atendimento">Hora do atendimento *</label>
                    <input type="time" id="aj_hora_atendimento" name="aj_hora_atendimento" required>
                </div>
            </div>

            <?php submit_button('Salvar Atendimento'); ?>
        </form>
    </div>
</div>