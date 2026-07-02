<?php
require_once __DIR__ . '/connect.php';
$db = new Database();
$conn = $db->connect();

// Base path for financial console (for links)
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if (!defined('FINANCIAL_BASE')) {
    define('FINANCIAL_BASE', $base_url);
}

function financial_project_rows($db) {
    static $projects = null;
    if ($projects !== null) {
        return $projects;
    }

    $rows = $db->read(
        "SELECT id, name, display_name, code, primary_color, secondary_color, image_path, seal
         FROM languages
         WHERE is_active = 1 AND code IS NOT NULL AND code <> ''
         ORDER BY sort_order, id"
    );
    if ($rows === false) {
        $rows = [];
    }

    $projects = array_map(function ($row) {
        $label = trim($row['display_name'] ?? '') ?: (trim($row['name'] ?? '') ?: $row['code']);
        return [
            'id' => (int)($row['id'] ?? 0),
            'project_name' => $label,
            'keyword' => $row['code'],
            'code' => $row['code'],
            'name' => $row['name'] ?? $label,
            'display_name' => $row['display_name'] ?? $label,
            'primary_color' => $row['primary_color'] ?? null,
            'secondary_color' => $row['secondary_color'] ?? null,
            'image_path' => $row['image_path'] ?? null,
            'seal' => $row['seal'] ?? null,
        ];
    }, $rows);

    return $projects;
}

function financial_project_label($projects, $value) {
    $value = trim((string)$value);
    if ($value === '') {
        return '-';
    }
    if (strtolower($value) === 'all') {
        return 'All active projects';
    }

    $labels = [];
    foreach ($projects as $project) {
        $labels[$project['keyword']] = $project['project_name'];
    }

    if (strpos($value, ',') !== false) {
        $parts = array_filter(array_map('trim', explode(',', $value)));
        $resolved = array_map(function ($part) use ($labels) {
            return $labels[$part] ?? $part;
        }, $parts);
        return implode(', ', $resolved);
    }

    return $labels[$value] ?? $value;
}

function financial_project_by_key($projects, $value) {
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    foreach ($projects as $project) {
        if (($project['keyword'] ?? '') === $value || ($project['code'] ?? '') === $value) {
            return $project;
        }
    }
    return null;
}

function financial_media_url($path) {
    $path = trim((string)$path);
    if ($path === '') {
        return '';
    }
    if (preg_match('/^(https?:)?\/\//i', $path) || stripos($path, 'data:') === 0) {
        return $path;
    }
    if (strpos($path, '/') === 0) {
        return $path;
    }
    $base = defined('FINANCIAL_BASE') ? FINANCIAL_BASE : '';
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function financial_project_seal_src($project) {
    if (!$project || empty($project['seal'])) {
        return '';
    }
    return financial_media_url($project['seal']);
}

function financial_project_icon_html($project, $class = 'project-seal', $fallback_icon = 'chart', $size = 17) {
    $label = $project ? trim($project['project_name'] ?? $project['display_name'] ?? $project['name'] ?? $project['keyword'] ?? 'Project') : 'Project';
    $src = financial_project_seal_src($project);
    if ($src !== '') {
        return '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($label) . ' icon" class="' . htmlspecialchars($class) . '" loading="lazy" onerror="this.style.display=\'none\';">';
    }
    return function_exists('console_icon') ? console_icon($fallback_icon, $size) : '';
}
