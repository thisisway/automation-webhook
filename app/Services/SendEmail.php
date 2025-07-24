<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SendEmail {
    public static function send($to, $subject, $template, $data) {
        try {
            $mail = new PHPMailer(true);
            
            // Configurações do servidor
            $mail->isSMTP();
            $mail->Host = getenv('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('MAIL_USERNAME');
            $mail->Password = getenv('MAIL_PASSWORD');
            $mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
            $mail->Port = getenv('MAIL_PORT');
            $mail->CharSet = 'UTF-8';

            // Remetente
            $mail->setFrom(getenv('MAIL_FROM_ADDRESS'), getenv('MAIL_FROM_NAME'));
            
            // Destinatário
            $mail->addAddress($to);

            // Conteúdo
            $mail->isHTML(true);
            $mail->Subject = $subject;

            // Extrair os dados para o template
            extract($data);

            // Capturar o conteúdo do template

            $template = str_replace('.php', '', $template);

            ob_start();
            $logo = 'https://gestor.ieademe.com.br/images/logo_horizontal_azul_escuro.svg';
            $codigo = $data['code'];
            include __DIR__ . '/../../resources/templates/' . $template . '.php';
            $content = ob_get_clean();

            $mail->Body = $content;
            
            // Versão em texto plano
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $content));

            return $mail->send();
        } catch (Exception $e) {
            throw new \Exception('Erro ao enviar e-mail: ' . $e->getMessage());
        }
    }
}