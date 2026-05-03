<?php
/**
 * Clase Helpers
 * Funciones auxiliares y helpers del sistema
 *
 * @package App\Utils
 * @author Sistema de Gestión de Colaboradores
 * @version 1.0
 */

namespace App\Utils;

class Helpers {
    /**
     * Formatear fecha en español
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function formatDate(string $date, string $format = 'd/m/Y'): string {
        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Formatear fecha y hora
     *
     * @param string|null $datetime
     * @param string $format
     * @return string
     */
    public static function formatDateTime(?string $datetime, string $format = 'd/m/Y H:i'): string {
        if (empty($datetime)) {
            return 'N/A';
        }
        $timestamp = strtotime($datetime);
        return date($format, $timestamp);
    }

    /**
     * Formatear fecha con texto en español
     *
     * @param string $date
     * @return string
     */
    public static function formatDateText(string $date): string {
        $meses = [
            1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
            'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'
        ];

        $timestamp = strtotime($date);
        $dia = date('d', $timestamp);
        $mes = $meses[(int)date('n', $timestamp)];
        $anio = date('Y', $timestamp);

        return "$dia de $mes de $anio";
    }

    /**
     * Obtener edad desde fecha de nacimiento
     *
     * @param string $birthdate
     * @return int
     */
    public static function getAge(string $birthdate): int {
        $birth = new \DateTime($birthdate);
        $now = new \DateTime();
        return $birth->diff($now)->y;
    }

    /**
     * Calcular grupo etáreo
     *
     * @param int $edad
     * @return string
     */
    public static function getGrupoEtareo(int $edad): string {
        if ($edad <= 12) return 'Infancia (0-12 años)';
        if ($edad <= 17) return 'Adolescencia (13-17 años)';
        if ($edad <= 28) return 'Juventud (18-28 años)';
        if ($edad <= 40) return 'Adultez Joven (29-40 años)';
        if ($edad <= 60) return 'Adultez Media (41-60 años)';
        return 'Adultez Mayor (61+ años)';
    }

    /**
     * Calcular estado del colaborador
     *
     * @param int $potencial
     * @param int $historico
     * @return string
     */
    public static function calculateEstado(int $potencial, int $historico): string {
        if ($potencial == 0) return 'Nuevo';
        if ($historico == 0) return 'Desvinculado';
        if ($historico > $potencial) return 'Creció';
        if ($historico < $potencial) return 'Decrece';
        return 'Igual';
    }

    /**
     * Formatear número con separadores
     *
     * @param float $number
     * @param int $decimals
     * @return string
     */
    public static function formatNumber(float $number, int $decimals = 0): string {
        return number_format($number, $decimals, ',', '.');
    }

    /**
     * Formatear moneda (pesos colombianos)
     *
     * @param float $amount
     * @return string
     */
    public static function formatCurrency(float $amount): string {
        return '$ ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Formatear porcentaje
     *
     * @param float $value
     * @param int $decimals
     * @return string
     */
    public static function formatPercent(float $value, int $decimals = 1): string {
        return number_format($value, $decimals, ',', '.') . '%';
    }

    /**
     * Calcular porcentaje
     *
     * @param float $part
     * @param float $total
     * @param int $decimals
     * @return float
     */
    public static function percentage(float $part, float $total, int $decimals = 2): float {
        if ($total == 0) return 0;
        return round(($part / $total) * 100, $decimals);
    }

    /**
     * Truncar texto
     *
     * @param string $text
     * @param int $length
     * @param string $suffix
     * @return string
     */
    public static function truncate(string $text, int $length = 100, string $suffix = '...'): string {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . $suffix;
    }

    /**
     * Slugificar texto (para URLs)
     *
     * @param string $text
     * @return string
     */
    public static function slug(string $text): string {
        // Convertir a minúsculas
        $text = strtolower($text);

        // Reemplazar caracteres especiales
        $text = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $text
        );

        // Remover caracteres no alfanuméricos
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);

        // Reemplazar espacios y guiones múltiples
        $text = preg_replace('/[\s-]+/', '-', $text);

        return trim($text, '-');
    }

    /**
     * Generar iniciales de un nombre
     *
     * @param string $name
     * @return string
     */
    public static function getInitials(string $name): string {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= mb_substr($word, 0, 1);
        }

        return mb_strtoupper($initials);
    }

    /**
     * Formatear nombre completo
     *
     * @param string $nombres
     * @param string $apellidos
     * @return string
     */
    public static function formatFullName(string $nombres, string $apellidos): string {
        return ucwords(strtolower(trim($nombres . ' ' . $apellidos)));
    }

    /**
     * Crear breadcrumbs HTML
     *
     * @param array $items [['label' => 'Home', 'url' => '/'], ...]
     * @return string
     */
    public static function breadcrumbs(array $items): string {
        $html = '<nav class="breadcrumbs"><ol>';

        foreach ($items as $index => $item) {
            $isLast = $index === count($items) - 1;

            if ($isLast) {
                $html .= '<li class="active">' . e($item['label']) . '</li>';
            } else {
                $html .= '<li><a href="' . e($item['url']) . '">' . e($item['label']) . '</a></li>';
            }
        }

        $html .= '</ol></nav>';
        return $html;
    }

    /**
     * Obtener tiempo relativo (hace X minutos/horas/días)
     *
     * @param string $datetime
     * @return string
     */
    public static function timeAgo(string $datetime): string {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'hace unos segundos';
        } elseif ($diff < 3600) {
            $min = floor($diff / 60);
            return "hace $min " . ($min == 1 ? 'minuto' : 'minutos');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "hace $hours " . ($hours == 1 ? 'hora' : 'horas');
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "hace $days " . ($days == 1 ? 'día' : 'días');
        } elseif ($diff < 2592000) {
            $weeks = floor($diff / 604800);
            return "hace $weeks " . ($weeks == 1 ? 'semana' : 'semanas');
        } elseif ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return "hace $months " . ($months == 1 ? 'mes' : 'meses');
        } else {
            $years = floor($diff / 31536000);
            return "hace $years " . ($years == 1 ? 'año' : 'años');
        }
    }

    /**
     * Crear select HTML de un enum
     *
     * @param string $name
     * @param array $options
     * @param string $selected
     * @param array $attributes
     * @return string
     */
    public static function selectEnum(
        string $name,
        array $options,
        string $selected = '',
        array $attributes = []
    ): string {
        $attrs = '';
        foreach ($attributes as $key => $value) {
            $attrs .= " $key=\"" . e($value) . "\"";
        }

        $html = "<select name=\"$name\" id=\"$name\"$attrs>";
        $html .= '<option value="">Seleccionar...</option>';

        foreach ($options as $value => $label) {
            $sel = $value === $selected ? ' selected' : '';
            $html .= '<option value="' . e($value) . '"' . $sel . '>' . e($label) . '</option>';
        }

        $html .= '</select>';
        return $html;
    }


    /**
     * Crear avatar con iniciales
     *
     * @param string $name
     * @param int $size
     * @return string
     */
    public static function avatarInitials(string $name, int $size = 40): string {
        $initials = self::getInitials($name);
        $color = self::stringToColor($name);

        return '<div class="avatar" style="width: ' . $size . 'px; height: ' . $size . 'px; background-color: ' . $color . '">'
            . '<span>' . $initials . '</span>'
            . '</div>';
    }

    /**
     * Generar color desde string (para avatares)
     *
     * @param string $string
     * @return string
     */
    private static function stringToColor(string $string): string {
        $hash = md5($string);
        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        return "rgb($r, $g, $b)";
    }

    /**
     * Convertir array a CSV
     *
     * @param array $data
     * @param string $delimiter
     * @return string
     */
    public static function arrayToCsv(array $data, string $delimiter = ','): string {
        $output = fopen('php://temp', 'r+');

        foreach ($data as $row) {
            fputcsv($output, $row, $delimiter);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Exportar a Excel usando HTML
     *
     * @param array $data
     * @param array $headers
     * @param string $filename
     */
    public static function exportToExcel(array $data, array $headers, string $filename): void {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '.xls"');

        echo '<table border="1">';
        echo '<thead><tr>';
        foreach ($headers as $header) {
            echo '<th>' . e($header) . '</th>';
        }
        echo '</tr></thead><tbody>';

        foreach ($data as $row) {
            echo '<tr>';
            foreach ($row as $cell) {
                echo '<td>' . e($cell) . '</td>';
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    /**
     * Crear opciones de select desde tabla
     *
     * @param string $table
     * @param string $valueField
     * @param string $labelField
     * @param string $selected
     * @return string
     */
    public static function selectFromTable(
        string $table,
        string $valueField,
        string $labelField,
        string $selected = ''
    ): string {
        $db = \Database::getInstance();
        $rows = $db->fetchAll("SELECT $valueField, $labelField FROM $table ORDER BY $labelField");

        $html = '<option value="">Seleccionar...</option>';

        foreach ($rows as $row) {
            $value = $row[$valueField];
            $label = $row[$labelField];
            $sel = $value === $selected ? ' selected' : '';
            $html .= '<option value="' . e($value) . '"' . $sel . '>' . e($label) . '</option>';
        }

        return $html;
    }

    /**
     * Validar si es solicitud AJAX
     *
     * @return bool
     */
    public static function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Respuesta JSON
     *
     * @param mixed $data
     * @param int $statusCode
     */
    public static function jsonResponse($data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Respuesta de éxito JSON
     *
     * @param mixed $data
     * @param string $message
     */
    public static function jsonSuccess($data = null, string $message = 'Operación exitosa'): void {
        self::jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Respuesta de error JSON
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     */
    public static function jsonError(string $message, array $errors = [], int $statusCode = 400): void {
        self::jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $statusCode);
    }

    /**
     * Cargar vista
     *
     * @param string $view
     * @param array $data
     * @return string
     */
    public static function view(string $view, array $data = []): string {
        extract($data);

        ob_start();
        include view_path($view);
        return ob_get_clean();
    }

    /**
     * Generar array de años para select
     *
     * @param int $startYear
     * @param int|null $endYear
     * @param bool $reverse
     * @return array
     */
    public static function yearRange(int $startYear, ?int $endYear = null, bool $reverse = true): array {
        $endYear = $endYear ?? date('Y');
        $years = range($startYear, $endYear);

        if ($reverse) {
            $years = array_reverse($years);
        }

        return array_combine($years, $years);
    }

    /**
     * Obtener array de meses en español
     *
     * @return array
     */
    public static function months(): array {
        return [
            1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
    }

    /**
     * Obtener array de días de la semana
     *
     * @return array
     */
    public static function daysOfWeek(): array {
        return [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado'
        ];
    }

    /**
     * Debug dump formateado
     *
     * @param mixed $data
     */
    public static function dump(...$data): void {
        echo '<pre style="background: #f4f4f4; padding: 10px; border: 1px solid #ddd; margin: 10px 0;">';
        foreach ($data as $item) {
            var_dump($item);
        }
        echo '</pre>';
    }

    /**
     * Generar badge según estado/perfil
     *
     * @param string $value
     * @param string $type 'perfil'|'estado'|'nivel'
     * @return string HTML del badge
     */
    public static function estadoBadge(string $value, string $type = 'auto'): string {
        // Auto-detectar tipo si no se especifica
        if ($type === 'auto') {
            if (array_key_exists($value, PERFILES)) {
                $type = 'perfil';
            } elseif (in_array($value, ['Nuevo', 'Creció', 'Igual', 'Decrece', 'Desvinculado'])) {
                $type = 'estado';
            } elseif (array_key_exists($value, NIVELES_PARTICIPACION)) {
                $type = 'nivel';
            }
        }

        // Definir colores según tipo
        $colorMap = [
            'perfil' => COLORES_PERFIL ?? [],
            'estado' => COLORES_ESTADO ?? []
        ];

        // Obtener color
        $color = $colorMap[$type][$value] ?? '#6b7280';

        // Convertir color hex a clases de Tailwind
        $bgClass = self::hexToTailwindBg($color);
        $textClass = self::hexToTailwindText($color);

        return "<span class=\"badge {$bgClass} {$textClass}\">{$value}</span>";
    }

    /**
     * Convertir color hex a clase de fondo de Tailwind
     *
     * @param string $hex
     * @return string
     */
    private static function hexToTailwindBg(string $hex): string {
        $map = [
            '#3b82f6' => 'bg-blue-100',
            '#10b981' => 'bg-green-100',
            '#f59e0b' => 'bg-yellow-100',
            '#f97316' => 'bg-orange-100',
            '#ef4444' => 'bg-red-100',
            '#8b5cf6' => 'bg-purple-100',
            '#ec4899' => 'bg-pink-100',
            '#06b6d4' => 'bg-cyan-100',
            '#6b7280' => 'bg-gray-100',
        ];

        return $map[$hex] ?? 'bg-gray-100';
    }

    /**
     * Convertir color hex a clase de texto de Tailwind
     *
     * @param string $hex
     * @return string
     */
    private static function hexToTailwindText(string $hex): string {
        $map = [
            '#3b82f6' => 'text-blue-800',
            '#10b981' => 'text-green-800',
            '#f59e0b' => 'text-yellow-800',
            '#f97316' => 'text-orange-800',
            '#ef4444' => 'text-red-800',
            '#8b5cf6' => 'text-purple-800',
            '#ec4899' => 'text-pink-800',
            '#06b6d4' => 'text-cyan-800',
            '#6b7280' => 'text-gray-800',
        ];

        return $map[$hex] ?? 'text-gray-800';
    }

    /**
     * Generar paginación HTML
     *
     * @param int $currentPage
     * @param int $totalPages
     * @param string $baseUrl
     * @param array $params
     * @return string HTML de paginación
     */
    public static function pagination(int $currentPage, int $totalPages, string $baseUrl, array $params = []): string {
        if ($totalPages <= 1) {
            return '';
        }

        $html = '<nav class="flex items-center justify-between">';
        $html .= '<div class="flex-1 flex justify-between sm:hidden">';

        // Mobile: Previous/Next buttons
        if ($currentPage > 1) {
            $prevUrl = self::buildPaginationUrl($baseUrl, $currentPage - 1, $params);
            $html .= "<a href=\"{$prevUrl}\" class=\"btn btn-outline\">Anterior</a>";
        } else {
            $html .= '<span class=\"btn btn-outline opacity-50 cursor-not-allowed\">Anterior</span>';
        }

        if ($currentPage < $totalPages) {
            $nextUrl = self::buildPaginationUrl($baseUrl, $currentPage + 1, $params);
            $html .= "<a href=\"{$nextUrl}\" class=\"btn btn-outline\">Siguiente</a>";
        } else {
            $html .= '<span class=\"btn btn-outline opacity-50 cursor-not-allowed\">Siguiente</span>';
        }

        $html .= '</div>';

        // Desktop: Full pagination
        $html .= '<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">';
        $html .= '<div><p class="text-sm text-gray-700">Página <span class="font-medium">' . $currentPage . '</span> de <span class="font-medium">' . $totalPages . '</span></p></div>';
        $html .= '<div><nav class="inline-flex -space-x-px rounded-md shadow-sm">';

        // Previous button
        if ($currentPage > 1) {
            $prevUrl = self::buildPaginationUrl($baseUrl, $currentPage - 1, $params);
            $html .= "<a href=\"{$prevUrl}\" class=\"relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50\">";
            $html .= '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>';
            $html .= '</a>';
        }

        // Page numbers
        $range = 2;
        for ($i = max(1, $currentPage - $range); $i <= min($totalPages, $currentPage + $range); $i++) {
            $url = self::buildPaginationUrl($baseUrl, $i, $params);
            $activeClass = $i === $currentPage
                ? 'z-10 bg-primary-600 text-white'
                : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50';

            $html .= "<a href=\"{$url}\" class=\"relative inline-flex items-center px-4 py-2 text-sm font-semibold {$activeClass}\">{$i}</a>";
        }

        // Next button
        if ($currentPage < $totalPages) {
            $nextUrl = self::buildPaginationUrl($baseUrl, $currentPage + 1, $params);
            $html .= "<a href=\"{$nextUrl}\" class=\"relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50\">";
            $html .= '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
            $html .= '</a>';
        }

        $html .= '</nav></div></div>';
        $html .= '</nav>';

        return $html;
    }

    /**
     * Construir URL de paginación
     *
     * @param string $baseUrl
     * @param int $page
     * @param array $params
     * @return string
     */
    private static function buildPaginationUrl(string $baseUrl, int $page, array $params = []): string {
        $params['page'] = $page;
        $query = http_build_query($params);
        return $baseUrl . '?' . $query;
    }
}
