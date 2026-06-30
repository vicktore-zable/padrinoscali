<?php

class WhatsAppApi
{
    private $apiUrl;
    private $apiKey;

    private $templates = [
        'lider' => "¡Feliz cumpleaños, {nombres}! 🎉🌟 Gracias por ser un líder excepcional en nuestra comunidad. Tu compromiso con Padrinos Cali inspira a todos. ¡Que tengas un día lleno de bendiciones! 🎂🎈",
        'simpatizante' => "¡Feliz cumpleaños, {nombres}! 🎂🎈 Te deseamos un día maravilloso lleno de alegría. Gracias por ser parte de esta gran familia Padrinos Cali. ¡Un abrazo enorme! 🤗",
        'movilizador' => "¡Feliz cumpleaños, {nombres}! 🚀💪 Tu energía y compromiso con nuestra causa son admirables. ¡Gracias por movilizar el cambio en Cali! Que este nuevo año esté lleno de logros. 🎉",
        'familia' => "¡Feliz cumpleaños, {nombres}! 🏡💛 Que este día especial esté rodeado del amor de tu familia. Gracias por ser parte de Padrinos Cali. ¡Muchas felicidades! 🎂",
        'default' => "¡Feliz cumpleaños, {nombres}! 🎉 Te deseamos un día maravilloso. Gracias por ser parte de Padrinos Cali. 🎂🎈"
    ];

    public function __construct()
    {
        $this->apiUrl = defined('WATI_API_URL') ? WATI_API_URL : '';
        $this->apiKey = defined('WATI_API_KEY') ? WATI_API_KEY : '';
    }

    public function formatPhone($numero): string
    {
        $numero = preg_replace('/[^0-9]/', '', $numero);
        if (strlen($numero) === 10) {
            $numero = '57' . $numero;
        }
        return $numero;
    }

    public function getTemplate(?string $perfil): string
    {
        $perfil = strtolower(trim($perfil ?? ''));
        if (strpos($perfil, 'lider') !== false || strpos($perfil, 'líder') !== false) {
            return $this->templates['lider'];
        }
        if (strpos($perfil, 'simpatizante') !== false) {
            return $this->templates['simpatizante'];
        }
        if (strpos($perfil, 'movilizador') !== false) {
            return $this->templates['movilizador'];
        }
        if (strpos($perfil, 'familia') !== false) {
            return $this->templates['familia'];
        }
        return $this->templates['default'];
    }

    public function fillTemplate(string $template, string $nombres): string
    {
        return str_replace('{nombres}', $nombres, $template);
    }

    public function send(string $telefono, string $mensaje): array
    {
        if (empty($this->apiKey) || empty($this->apiUrl)) {
            return ['success' => false, 'error' => 'WATI no configurado'];
        }

        $payload = [
            'phone' => $telefono,
            'body' => $mensaje
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Curl error: ' . $curlError];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return ['success' => true, 'response' => $response];
        }

        return [
            'success' => false,
            'error' => 'HTTP ' . $httpCode . ': ' . $response
        ];
    }

    public function log(PDO $db, array $colaborador, string $templateName, string $mensaje, string $estado, ?string $error = null): void
    {
        $stmt = $db->prepare("
            INSERT INTO whatsapp_log 
                (colaborador_id, colaborador_nombre, telefono_whatsapp, perfil, template_used, mensaje_enviado, estado, error_msg, sent_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $colaborador['id'],
            ($colaborador['nombres'] ?? '') . ' ' . ($colaborador['apellidos'] ?? ''),
            $colaborador['telefono_whatsapp'] ?? ($colaborador['telefono'] ?? ''),
            $colaborador['perfil'] ?? '',
            $templateName,
            $mensaje,
            $estado,
            $error
        ]);
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }
}
