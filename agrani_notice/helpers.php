<?php
declare(strict_types=1);

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function safe_base(string $s): string {
    $s = trim($s);
    $s = str_replace(['..', '/', '\\', "\0"], '', $s);
    return $s;
}

function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;
    $items = glob($dir . '/*') ?: [];
    foreach ($items as $it) {
        if (is_dir($it)) rrmdir($it);
        else @unlink($it);
    }
    @rmdir($dir);
}

function read_json(string $path): array {
    if (!file_exists($path)) return [];
    $raw = file_get_contents($path);
    $j = json_decode($raw ?: '', true);
    return is_array($j) ? $j : [];
}

function write_json(string $path, array $data): void {
    file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function verify_passwords(?string $input, ?string $batchPassword, string $masterPassword): array {
    $input = (string)($input ?? '');
    if ($input === '') return [false, 'Password required.'];
    if (hash_equals($masterPassword, $input)) return [true, 'master'];
    if ($batchPassword !== null && $batchPassword !== '' && hash_equals($batchPassword, $input)) return [true, 'batch'];
    return [false, 'Wrong password.'];
}

function parse_tab_text(string $text): array {
    $text = trim($text);
    $text = str_replace("\r\n", "\n", $text);
    $lines = array_values(array_filter(explode("\n", $text), fn($l) => trim($l) !== ''));

    if (count($lines) < 2) return [[], []];

    $headers = array_map('trim', explode("\t", array_shift($lines)));
    $rows = [];

    foreach ($lines as $ln) {
        $cols = explode("\t", $ln);
        if (count($cols) < count($headers)) $cols = array_pad($cols, count($headers), '');
        if (count($cols) > count($headers)) $cols = array_slice($cols, 0, count($headers));

        $assoc = [];
        foreach ($headers as $i => $h) $assoc[$h] = trim((string)($cols[$i] ?? ''));
        $rows[] = $assoc;
    }
    return [$headers, $rows];
}
