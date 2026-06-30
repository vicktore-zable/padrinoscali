<?php

class WhatsAppCloudApi
{
    private string $token;
    private string $phoneId;
    private string $apiVersion;
    private string $verifyToken;

    public function __construct()
    {
        $this->token = defined('META_WHATSAPP_TOKEN') ? META_WHATSAPP_TOKEN : '';
        $this->phoneId = defined('META_WHATSAPP_PHONE_ID') ? META_WHATSAPP_PHONE_ID : '';
        $this->apiVersion = defined('META_API_VERSION') ? META_API_VERSION : 'v22.0';
        $this->verifyToken = defined('META_WEBHOOK_VERIFY_TOKEN') ? META_WEBHOOK_VERIFY_TOKEN : '';
    }

    public function isConfigured(): bool
    {
        return !empty($this->token) && !empty($this->phoneId);
    }

    public function sendText(string $phone, string $text): array
    {
        if (!$this->isConfigured()) {
            return $this->fallback($phone, $text);
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhone($phone),
            'type' => 'text',
            'text' => ['body' => $text]
        ];

        return $this->post($payload);
    }

    public function sendTemplate(string $phone, string $templateName, array $variables = []): array
    {
        if (!$this->isConfigured()) {
            return $this->fallback($phone, $templateName . ' ' . implode(', ', $variables));
        }

        $components = [
            'type' => 'body',
            'parameters' => []
        ];

        foreach ($variables as $var) {
            $components['parameters'][] = ['type' => 'text', 'text' => $var];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhone($phone),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => 'es'],
                'components' => [$components]
            ]
        ];

        return $this->post($payload);
    }

    public function markAsRead(string $messageId): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        return $this->post($payload);
    }

    public function verifyWebhook(string $mode, string $token, string $challenge): ?string
    {
        if ($mode === 'subscribe' && $token === $this->verifyToken) {
            return $challenge;
        }
        return null;
    }

    public function processWebhook(array $payload): array
    {
        $messages = [];

        if (!isset($payload['entry'])) return $messages;

        foreach ($payload['entry'] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') continue;

                $value = $change['value'] ?? [];
                foreach ($value['messages'] ?? [] as $msg) {
                    $messages[] = [
                        'wa_message_id' => $msg['id'] ?? '',
                        'from' => $msg['from'] ?? '',
                        'type' => $msg['type'] ?? 'text',
                        'text' => $msg['text']['body'] ?? $msg[$msg['type'] ?? 'text']['body'] ?? '',
                        'timestamp' => $msg['timestamp'] ?? '',
                        'raw' => $msg
                    ];
                }
            }
        }

        return $messages;
    }

    private function post(array $payload): array
    {
        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneId}/messages";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
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

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300 && ($data['messages'][0]['id'] ?? false)) {
            return [
                'success' => true,
                'message_id' => $data['messages'][0]['id'],
                'response' => $data
            ];
        }

        return [
            'success' => false,
            'error' => $data['error']['message'] ?? 'HTTP ' . $httpCode . ': ' . $response,
            'response' => $data
        ];
    }

    private function fallback(string $phone, string $message): array
    {
        if (defined('WHATSAPP_PROVIDER') && WHATSAPP_PROVIDER === 'wati') {
            try {
                $wati = new WhatsAppApi();
                $formatted = $wati->formatPhone($phone);
                return $wati->send($formatted, $message);
            } catch (Throwable $e) {
                return ['success' => false, 'error' => 'Fallback error: ' . $e->getMessage()];
            }
        }
        return ['success' => false, 'error' => 'WhatsApp Cloud API no configurado. Define META_WHATSAPP_TOKEN y META_WHATSAPP_PHONE_ID en root_config.php'];
    }

    public function formatPhone(string $numero): string
    {
        $numero = preg_replace('/[^0-9]/', '', $numero);
        if (strlen($numero) === 10) {
            $numero = '57' . $numero;
        }
        return $numero;
    }

    public function getVerifyToken(): string
    {
        return $this->verifyToken;
    }
}
