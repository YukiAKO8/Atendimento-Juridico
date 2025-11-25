<?php
/**
 * View para o relatório/dossiê completo de um sócio.
 *
 * @package AtendimentoJuridico
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// As variáveis $socio, $estatisticas e $historico_atendimentos são passadas pelo controller.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Atendimentos - <?php echo esc_html( $socio->nome ?? 'Sócio' ); ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background-color: #f0f0f1;
            color: #1d2327;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .relatorio-container {
            max-width: 900px;
            margin: 20px auto;
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #005a9c;
            border-bottom: 2px solid #e5e5e5;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        h1 {
            font-size: 28px;
            text-align: center;
        }
        h2 {
            font-size: 22px;
        }
        h3 {
            font-size: 18px;
            border-bottom-style: dashed;
        }
        .dados-socio, .estatisticas-grid {
            background-color: #f9f9f9;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .dados-socio p, .estatisticas-grid p {
            margin: 0 0 10px;
            font-size: 16px;
        }
        .dados-socio strong, .estatisticas-grid strong {
            color: #3c434a;
        }
        .estatisticas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .atendimento-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            page-break-inside: avoid; /* Evita que o item quebre entre páginas na impressão */
        }
        .atendimento-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .atendimento-header span {
            font-weight: bold;
        }
        .atendimento-observacoes, .atendimento-documentos {
            margin-top: 15px;
        }
        .atendimento-observacoes p {
            background-color: #fffbe6;
            border-left: 4px solid #ffb900;
            padding: 10px 15px;
        }
        .atendimento-documentos ul {
            list-style: none;
            padding-left: 0;
        }
        .atendimento-documentos li a {
            text-decoration: none;
            color: #007cba;
        }
        .atendimento-documentos li a:hover {
            text-decoration: underline;
        }
        .no-data {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .print-button {
            display: block;
            width: 150px;
            margin: 30px auto 0;
            padding: 12px 20px;
            background-color: #4B0082;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
        }
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .relatorio-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                border-radius: 0;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="relatorio-container">
        <h1>Relatório de Atendimentos</h1>

        <div class="dados-socio">
            <p><strong>Sócio(a):</strong> <?php echo esc_html( $socio->nome ?? 'Não encontrado' ); ?></p>
            <p><strong>CPF:</strong> <?php echo esc_html( $socio->cpf ?? 'Não encontrado' ); ?></p>
        </div>

        <h2>Estatísticas Gerais</h2>
        <div class="estatisticas-grid">
            <p><strong>Total de Atendimentos:</strong> <?php echo esc_html( $estatisticas['total'] ?? 0 ); ?></p>
            <p><strong>Principal Tipo de Atendimento:</strong> <?php echo esc_html( $estatisticas['tipo_mais_comum'] ?? 'N/A' ); ?></p>
            <p><strong>Principal Canal de Atendimento:</strong> <?php echo esc_html( $estatisticas['canal_mais_comum'] ?? 'N/A' ); ?></p>
        </div>

        <h2>Histórico de Atendimentos</h2>
        <?php if ( ! empty( $historico_atendimentos ) ) : ?>
            <?php foreach ( $historico_atendimentos as $atendimento ) : ?>
                <div class="atendimento-item">
                    <div class="atendimento-header">
                        <span><?php echo esc_html( date( 'd/m/Y', strtotime( $atendimento->data_atendimento ) ) ); ?></span>
                        <span><strong>Assunto:</strong> <?php echo esc_html( $atendimento->assunto ); ?></span>
                        <span><strong>Status:</strong> <?php echo esc_html( $atendimento->status ); ?></span>
                    </div>
                    <?php if ( ! empty( $atendimento->observacoes ) ) : ?>
                        <div class="atendimento-observacoes">
                            <h3>Observações</h3>
                            <p><?php echo nl2br( esc_html( $atendimento->observacoes ) ); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $atendimento->documentos ) ) : ?>
                        <div class="atendimento-documentos">
                            <h3>Documentos</h3>
                            <ul>
                                <?php foreach ( $atendimento->documentos as $doc ) : ?>
                                    <li><a href="<?php echo esc_url( $doc->url ); ?>" target="_blank"><?php echo esc_html( $doc->nome_arquivo ); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="no-data">Nenhum atendimento encontrado para este sócio.</p>
        <?php endif; ?>

        <button class="print-button" onclick="window.print()">Imprimir</button>
    </div>
</body>
</html>

```

### Próximo Passo

Agora, precisamos criar a lógica que irá buscar os dados e chamar este arquivo de visualização. Você pode me fornecer o arquivo PHP que atualmente gerencia a página `atendimento-juridico` (onde ficam as ações de `new`, `edit`, etc.)?

Se você não souber qual é, podemos criar um novo arquivo `aj-relatorio-controller.php` e incluí-lo no seu plugin principal. Apenas me diga como prefere prosseguir!<!--
[PROMPT_SUGGESTION]Ok, vamos criar o arquivo `aj-relatorio-controller.php` e integrá-lo.[/PROMPT_SUGGESTION]
[PROMPT_SUGGESTION]Explique o que o código no arquivo `tela-relatorio-socio.php` faz.[/PROMPT_SUGGESTION]
