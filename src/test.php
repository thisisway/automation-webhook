<?php
// Página de teste para o webhook
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automation Webhook - Test Interface</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .info {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .info h3 {
            margin-top: 0;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Automation Webhook</h1>
        
        <div class="info">
            <h3>Como usar:</h3>
            <p><strong>Endpoint:</strong> <code>POST /src/index.php</code></p>
            <p><strong>Payload:</strong></p>
            <pre>{
  "client": "jurandir",
  "vcpu": 2,
  "mem": 4096,
  "soft": "n8n"
}</pre>
            <p><strong>Softwares disponíveis:</strong> n8n, evoapi</p>
        </div>

        <form id="webhookForm">
            <div class="form-group">
                <label for="client">Nome do Cliente:</label>
                <input type="text" id="client" name="client" placeholder="ex: jurandir" required>
            </div>
            
            <div class="form-group">
                <label for="vcpu">CPU (vCPU):</label>
                <input type="number" id="vcpu" name="vcpu" min="1" max="16" value="2" required>
            </div>
            
            <div class="form-group">
                <label for="mem">Memória (MB):</label>
                <input type="number" id="mem" name="mem" min="512" max="32768" step="512" value="4096" required>
            </div>
            
            <div class="form-group">
                <label for="soft">Software:</label>
                <select id="soft" name="soft" required>
                    <option value="">Selecione...</option>
                    <option value="n8n">N8N (Automação)</option>
                    <option value="evoapi">Evolution API (WhatsApp)</option>
                </select>
            </div>
            
            <button type="submit">🚀 Criar Serviço</button>
        </form>
        
        <div id="result" class="result">
            <pre id="resultContent"></pre>
        </div>
    </div>

    <script>
        document.getElementById('webhookForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                client: formData.get('client'),
                vcpu: parseInt(formData.get('vcpu')),
                mem: parseInt(formData.get('mem')),
                soft: formData.get('soft')
            };
            
            const resultDiv = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            const button = document.querySelector('button[type="submit"]');
            
            button.disabled = true;
            button.textContent = '⏳ Criando...';
            
            try {
                const response = await fetch('index.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                resultDiv.className = 'result ' + (result.success ? 'success' : 'error');
                resultContent.textContent = JSON.stringify(result, null, 2);
                resultDiv.style.display = 'block';
                
                if (result.success) {
                    // Limpar o formulário em caso de sucesso
                    document.getElementById('client').value = '';
                    document.getElementById('vcpu').value = '2';
                    document.getElementById('mem').value = '4096';
                    document.getElementById('soft').value = '';
                }
                
            } catch (error) {
                resultDiv.className = 'result error';
                resultContent.textContent = 'Erro na requisição: ' + error.message;
                resultDiv.style.display = 'block';
            }
            
            button.disabled = false;
            button.textContent = '🚀 Criar Serviço';
        });
    </script>
</body>
</html>
