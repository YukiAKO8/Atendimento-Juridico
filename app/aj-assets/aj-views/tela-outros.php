<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form id="aj-outros-form" method="post">

    <div class="form-row">
        <div class="form-group">
            <label for="aj_sumula_atendimento">Súmula do atendimento</label>
            <textarea id="aj_sumula_atendimento" name="aj_sumula_atendimento" rows="6"></textarea>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="aj_objeto_atendimento">Objeto do atendimento</label>
            <textarea id="aj_objeto_atendimento" name="aj_objeto_atendimento" rows="6"></textarea>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="aj_observacoes_atendimento">Observações do atendimento</label>
            <?php
            $content = '';
            $editor_id = 'aj_observacoes_atendimento';
            $settings = array(
                'textarea_name' => 'aj_observacoes_atendimento',
                'textarea_rows' => 10,
                'media_buttons' => false,
                'quicktags'     => false, // Esta linha desativa a aba "Texto" (código)
            );
            wp_editor( $content, $editor_id, $settings );
            ?>
        </div>
    </div>

    <?php submit_button('Salvar Informações'); ?>

</form>