<?php
// 管理パネル設定ファイル
session_start();

// エラーレポート設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// データベース設定
define('DB_PATH', __DIR__ . '/database.sqlite');

// 管理者メールアドレス（.envから読み込む）
$admin_email = 'admin@example.com'; // デフォルト値

// SMTP設定（.envから読み込む）
$smtp_host = 'localhost';
$smtp_port = 25;
$smtp_username = '';
$smtp_password = '';
$smtp_from_email = 'noreply@westfield.example.com';
$smtp_from_name = 'WEST FIELD';

// .envファイルがあれば読み込む
$env_file = __DIR__ . '/../.env';
if (file_exists($env_file)) {
    $env_content = file_get_contents($env_file);
    $lines = explode("\n", $env_content);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, 'ADMIN_EMAIL=') === 0) {
            $admin_email = trim(substr($line, 12));
        } elseif (strpos($line, 'SMTP_HOST=') === 0) {
            $smtp_host = trim(substr($line, 10));
        } elseif (strpos($line, 'SMTP_PORT=') === 0) {
            $smtp_port = intval(trim(substr($line, 10)));
        } elseif (strpos($line, 'SMTP_USERNAME=') === 0) {
            $smtp_username = trim(substr($line, 14));
        } elseif (strpos($line, 'SMTP_PASSWORD=') === 0) {
            $smtp_password = trim(substr($line, 14));
        } elseif (strpos($line, 'SMTP_FROM_EMAIL=') === 0) {
            $smtp_from_email = trim(substr($line, 16));
        } elseif (strpos($line, 'SMTP_FROM_NAME=') === 0) {
            $smtp_from_name = trim(substr($line, 15));
        }
    }
}

// セッション有効期限（15分）
define('TOKEN_EXPIRY', 15 * 60); // 15分
define('TOKEN_LENGTH', 4); // 4桁トークン

// CSRFトークン生成
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRFトークン検証
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// データベース接続
function get_db() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('データベース接続エラー: ' . $e->getMessage());
        }
    }
    return $db;
}

// データベース初期化
function init_database() {
    $db = get_db();

    // 管理者テーブル
    $db->exec("CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // ログイントークンテーブル
    $db->exec("CREATE TABLE IF NOT EXISTS login_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT NOT NULL,
        token TEXT NOT NULL,
        attempts INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL
    )");

    // 送信先テーブル
    $db->exec("CREATE TABLE IF NOT EXISTS recipients (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        subscribed INTEGER DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 署名テーブル
    $db->exec("CREATE TABLE IF NOT EXISTS signature (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 配信ログテーブル
    $db->exec("CREATE TABLE IF NOT EXISTS delivery_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        recipient_id INTEGER NOT NULL,
        subject TEXT NOT NULL,
        status TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (recipient_id) REFERENCES recipients(id)
    )");

    // デフォルト署名を挿入
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM signature");
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result['count'] == 0) {
        $stmt = $db->prepare("INSERT INTO signature (content) VALUES (?)");
        $stmt->execute(["WEST FIELD サバイバルゲームフィールド\n〒586-0052 大阪府河内長野市河合寺４２６\nTEL: 090-9715-1979"]);
    }
}

// 初期化実行
init_database();
?>