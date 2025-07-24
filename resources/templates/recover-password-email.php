<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
        }
        .logo {
            max-width: 250px;
            height: auto;
        }
        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .code-container {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .code {
            font-size: 36px;
            font-weight: bold;
            color: #002850;
            letter-spacing: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eeeeee;
            color: #666666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="<?php echo $logo; ?>" alt="IEADEME" class="logo">
        </div>
        
        <div class="content">
            <h2>Recuperação de Senha</h2>
            
            <p>Olá,</p>
            
            <p>Recebemos uma solicitação para recuperar sua senha. Para continuar com o processo, utilize o código abaixo:</p>
            
            <div class="code-container">
                <div class="code"><?php echo $codigo; ?></div>
            </div>
            
            <p>Este código é válido por 10 minutos. Se você não solicitou a recuperação de senha, por favor, ignore este e-mail.</p>
            
            <p>Atenciosamente,<br>Equipe IEADEME</p>
        </div>
        
        <div class="footer">
            <p>Este é um e-mail automático. Por favor, não responda.</p>
            <p>&copy; <?php echo date('Y'); ?> IEADEME - Igreja Evangélica Assembleia de Deus em Messejana </p>
        </div>
    </div>
</body>
</html>
