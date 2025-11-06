<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
    <h1>Documentos do Atendimento</h1>

    <div class="aj-form-container">
        <form id="aj-documentos-form" method="post" enctype="multipart/form-data">
            <div id="aj-upload-area" class="aj-upload-area">
                <label for="aj_file_input" class="aj-upload-button">+ Adicionar documento</label>
                <input type="file" id="aj_file_input" name="aj_documentos[]" multiple style="display: none;">
            </div>
            <div id="aj-file-preview-area"></div>
        </form>
    </div>
</div>