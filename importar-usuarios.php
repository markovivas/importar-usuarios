<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

// Força o PHP a detectar corretamente as quebras de linha (Mac/Windows/Linux)
ini_set('auto_detect_line_endings', true);

// Define a localidade para tratar corretamente caracteres UTF-8 no CSV
setlocale(LC_ALL, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil', 'portuguese');

require_once('wp-load.php');

ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');
set_time_limit(0);

wp_suspend_cache_invalidation(true);
remove_all_actions('profile_update');
remove_all_actions('user_register');
add_filter('send_password_change_email', '__return_false');
add_filter('send_email_change_email', '__return_false');

// ========== IN�CIO DO HTML ==========
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importação de Usuários</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 900px;
            overflow: hidden;
            margin: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 30px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            animation: fadeIn 0.5s ease;
            border-left: 4px solid;
        }
        
        .success {
            background: #f0f9ff;
            border-left-color: #10b981;
            color: #065f46;
        }
        
        .error {
            background: #fef2f2;
            border-left-color: #ef4444;
            color: #991b1b;
        }
        
        .info {
            background: #fefce8;
            border-left-color: #f59e0b;
            color: #92400e;
        }
        
        .message-icon {
            margin-right: 12px;
            font-size: 20px;
        }
        
        .progress-container {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
        }
        
        .progress-bar {
            background: #e5e7eb;
            border-radius: 10px;
            height: 12px;
            overflow: hidden;
            margin: 15px 0;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #10b981, #34d399);
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 10px;
        }
        
        .progress-text {
            font-size: 14px;
            color: #6b7280;
            margin-top: 10px;
        }
        
        .summary {
            background: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
            border-top: 3px solid #667eea;
        }
        
        .summary h3 {
            color: #374151;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .counter {
            font-size: 42px;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .status-running {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #e5e7eb;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Importação de Usuários</h1>
            <p>Sistema de importação em lote - Arquivo: cadastro_usuario.csv</p>
        </div>
        
        <div class="content">
            <div class="message info">
                <span class="message-icon">ℹ️</span>
                <div>
                    <strong>Iniciando importação...</strong>
                    <span class="status-badge status-running">
                        <span class="loading-spinner"></span>
                        EM ANDAMENTO
                    </span>
                </div>
            </div>
            
            <?php
            // ========== CONTINUA��O DO PHP ==========
            $arquivo_csv = 'cadastro_usuario.csv';

            if (!file_exists($arquivo_csv)) {
                echo '<div class="message error">';
                echo '<span class="message-icon">❌</span>';
                echo "<div><strong>Erro Fatal:</strong> O arquivo <code>$arquivo_csv</code> não foi encontrado.<br>";
                echo "<small>Certifique-se de fazer o upload do arquivo CSV para a mesma pasta deste script.</small></div>";
                echo '</div></div></div></body></html>';
                exit;
            }

            $handle = fopen($arquivo_csv, 'r');
            $header = fgetcsv($handle, 0, ';');

            $batch = 200;
            $contador = 0;
            $total = 0;
            
            // Contar total de linhas primeiro (opcional)
            $temp_handle = fopen($arquivo_csv, 'r');
            $total_linhas = -1; // Desconta o cabeçalho
            while (fgets($temp_handle) !== false) $total_linhas++;
            fclose($temp_handle);

            while (($linha = fgetcsv($handle, 0, ';')) !== false) {

                // Verifica se a linha tem dados suficientes (mnimo 4 colunas)
                if (empty($linha) || count($linha) < 4) {
                    continue;
                }

                // Ordem esperada das colunas: Matricula, Nome, Nascimento, Secretaria
                list($matricula, $nome, $nascimento, $secretaria) = $linha;
                $nascimento = trim($nascimento); // Remove espaos em branco para evitar erros no plugin

                $senha = preg_replace('/\D/', '', $nascimento);
                $matricula = ltrim($matricula, '0');

                $partes = explode(" ", trim($nome));
                $first_name = $partes[0];
                $last_name = count($partes) > 1 ? implode(" ", array_slice($partes, 1)) : '';

                $userdata = [
                    'user_login'    => $matricula,
                    'user_pass'     => $senha,
                    'user_nicename' => $matricula,
                    'user_email'    => $matricula . '@trescoracoes.mg.gov.br',
                    'display_name'  => $nome,
                    'first_name'    => $first_name,
                    'last_name'     => $last_name,
                    'role'          => 'subscriber',
                ];

                // Verifica se o usuário já existe
                $user_existente = get_user_by('login', $matricula);
                
                if ($user_existente) {
                    // Se existe, pegamos o ID para atualizar
                    $user_id = $user_existente->ID;
                    $userdata['ID'] = $user_id;
                    wp_update_user($userdata);
                } else {
                    // Se não existe, cria um novo
                    $user_id = wp_insert_user($userdata);
                }

                if (!is_wp_error($user_id)) {
                    // Salva a data de nascimento para o plugin de aniversariantes
                    // Converte a data para o formato AAAA-MM-DD, que é o padrão mais seguro para plugins.
                    $nascimento_formatado = '';
                    // Tenta criar um objeto de data a partir do formato brasileiro (dd/mm/aaaa)
                    $date_obj = DateTime::createFromFormat('d/m/Y', $nascimento);
                    if ($date_obj) {
                        // Se for bem-sucedido, formata para o padrão internacional (aaaa-mm-dd)
                        $nascimento_formatado = $date_obj->format('Y-m-d');
                    }

                    // Salva no banco de dados apenas se a data for válida
                    if (!empty($nascimento_formatado)) {
                        update_user_meta($user_id, 'data_nascimento', $nascimento_formatado);
                    }

                    // Salva a secretaria
                    if (!empty(trim($secretaria))) {
                        update_user_meta($user_id, 'secretaria', trim($secretaria));
                    }

                    echo '<div class="message success">';
                    echo '<span class="message-icon">✅</span>';
                    echo "<div><strong>$nome</strong> <span style='color:#6b7280;'>($matricula)</span></div>";
                    echo '</div>';
                } else {
                    echo '<div class="message error">';
                    echo '<span class="message-icon">❌</span>';
                    echo "<div><strong>Erro na matrícula $matricula</strong><br>";
                    echo "<small>" . $user_id->get_error_message() . "</small></div>";
                    echo '</div>';
                }

                $contador++;
                $progress = $total_linhas > 0 ? round(($contador / $total_linhas) * 100) : 0;
                
                if ($contador % $batch === 0) {
                    echo '<div class="message info">';
                    echo '<span class="message-icon">🔄</span>';
                    echo "<div><strong>Lote processado:</strong> $contador registros";
                    echo "<div class='progress-text'>Progresso: $progress%</div></div>";
                    echo '</div>';
                    ?>
                    <script>
                        document.querySelector('.progress-fill').style.width = '<?php echo $progress; ?>%';
                    </script>
                    <?php
                    flush();
                    ob_flush();
                    sleep(1);
                }
                
                // Atualiza barra de progresso continuamente
                echo '<script>';
                echo 'document.querySelector(".progress-fill").style.width = "' . $progress . '%";';
                echo 'document.querySelector(".counter").textContent = "' . $contador . '";';
                echo '</script>';
                flush();
                ob_flush();
            }
            
            fclose($handle);
            ?>
            
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text">
                    Processando registros: <?php echo $contador . ($total_linhas > 0 ? " de $total_linhas" : ''); ?>
                </div>
            </div>
        </div>
        
        <div class="summary">
            <h3>✅ Importação Concluída</h3>
            <div class="counter"><?php echo $contador; ?></div>
            <p>usuários processados com sucesso</p>
            <div style="margin-top: 20px; padding: 15px; background: #e0e7ff; border-radius: 6px; display: inline-block;">
                <strong>Status:</strong> 
                <span style="color: #10b981; font-weight: bold;">✅ Concluído</span>
            </div>
        </div>
    </div>
    
    <script>
        // Rola automaticamente para o final
        function scrollToBottom() {
            const content = document.querySelector('.content');
            content.scrollTop = content.scrollHeight;
        }
        
        // Atualiza a cada novo registro
        setInterval(scrollToBottom, 500);
        
        // Quando terminar
        document.addEventListener('DOMContentLoaded', function() {
            const lastMessage = document.querySelector('.summary');
            if(lastMessage) {
                scrollToBottom();
                
                // Altera o status
                const statusBadge = document.querySelector('.status-running');
                if(statusBadge) {
                    statusBadge.innerHTML = '✅ CONCLUÍDO';
                    statusBadge.classList.remove('status-running');
                    statusBadge.style.background = '#d1fae5';
                    statusBadge.style.color = '#065f46';
                }
            }
        });
    </script>
</body>
</html>
<?php
echo "<!-- Importação concluída! $contador usuários criados. -->";
?>