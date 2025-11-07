<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// A variável $atendimento é definida no controller (aj-controller.php) quando estamos editando.
$sumula_atendimento = isset( $atendimento->sumula_atendimento ) ? $atendimento->sumula_atendimento : '';
$objeto_atendimento = isset( $atendimento->objeto_atendimento ) ? $atendimento->objeto_atendimento : '';
$observacoes_atendimento = isset( $atendimento->observacoes_atendimento ) ? $atendimento->observacoes_atendimento : '';
?>

    <div class="form-row">
        <div class="form-group">
            <label for="aj_sumula_atendimento">Súmula do atendimento</label>
            <textarea id="aj_sumula_atendimento" name="aj_sumula_atendimento" rows="6"><?php echo esc_textarea( $sumula_atendimento ); ?></textarea>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="aj_objeto_atendimento">Objeto do atendimento</label>
            <textarea id="aj_objeto_atendimento" name="aj_objeto_atendimento" rows="6"><?php echo esc_textarea( $objeto_atendimento ); ?></textarea>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="aj_observacoes_atendimento">Observações do atendimento</label>
            <?php
            $content = $observacoes_atendimento;
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