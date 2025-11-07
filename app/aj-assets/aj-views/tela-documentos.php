<?php
// Prevenção de acesso direto ao arquivo.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h1>Documentos do Atendimento</h1>
<div id="aj-upload-area" class="aj-upload-area">
    <label for="aj_file_input" class="aj-upload-button">+ Adicionar documento</label>
    <input type="file" id="aj_file_input" name="aj_documentos[]" multiple style="display: none;">
</div>
<div id="aj-file-preview-area"></div>