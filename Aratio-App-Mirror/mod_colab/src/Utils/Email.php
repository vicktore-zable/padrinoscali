<?php
/**
 * Clase Email
 * Utilidad para el envío de correos electrónicos usando PHPMailer
 *
 * @package App\Utils
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Email
{
    /**
     * Enviar un correo electrónico
     *
     * @param string $to Destinatario
     * @param string $subject Asunto
     * @param string $body Cuerpo del mensaje (HTML)
     * @param string $altBody Cuerpo alternativo (Texto plano)
     * @return bool True si se envió con éxito
     * @throws Exception Si hay un error en el envío
     */
    public static function send(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor
            $mail->SMTPDebug = (APP_DEBUG && !is_ajax()) ? SMTP::DEBUG_OFF : SMTP::DEBUG_OFF; 
            $mail->isSMTP();
            $mail->Host       = MAIL_CONFIG['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_CONFIG['username'];
            $mail->Password   = MAIL_CONFIG['password'];
            $mail->SMTPSecure = MAIL_CONFIG['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = MAIL_CONFIG['port'];
            $mail->CharSet    = 'UTF-8';

            // Destinatarios
            $mail->setFrom(MAIL_CONFIG['from'], MAIL_CONFIG['from_name']);
            $mail->addAddress($to);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);

            return $mail->send();
        } catch (Exception $e) {
            Logger::error("Error al enviar email: {$mail->ErrorInfo}");
            if (APP_DEBUG) {
                throw $e;
            }
            return false;
        }
    }

    /**
     * Enviar correo de restablecimiento de contraseña
     *
     * @param string $to
     * @param string $token
     * @return bool
     */
    public static function sendPasswordReset(string $to, string $token): bool
    {
        $url = APP_URL . "/reset-password/" . $token;
        $subject = "Restablecer Contraseña - " . APP_NAME;
        
        $body = "
            <h2>Solicitud de restablecimiento de contraseña</h2>
            <p>Usted ha solicitado restablecer su contraseña para acceder al sistema " . APP_NAME . ".</p>
            <p>Haga clic en el siguiente enlace para continuar:</p>
            <p><a href='{$url}' style='background: #8b1538; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a></p>
            <p>Si el botón no funciona, copie y pegue la siguiente URL en su navegador:</p>
            <p>{$url}</p>
            <p>Este enlace expirará en 1 hora.</p>
            <p>Si usted no realizó esta solicitud, puede ignorar este mensaje.</p>
        ";

        return self::send($to, $subject, $body);
    }
}
