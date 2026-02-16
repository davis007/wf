<?php
// 管理パネル設定ファイル
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// エラーレポート設定
error_reporting(E_ALL);
ini_set('display_errors', 1);

// データベース設定
define('DB_PATH', __DIR__ . '/database.sqlite');

// SMTP設定（.envから読み込む）
$smtp_host = 'localhost';
$smtp_port = 25;
$smtp_username = '';
$smtp_password = '';
$smtp_from_email = 'info@westfield2023.net';
$smtp_from_name = 'WEST FIELD';

// 管理者メールアドレス（初期値はnull）
$admin_email = null;

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

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // 引用符（' または "）を削除
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            if ($key === 'ADMIN_EMAIL') {
                $admin_email = $value;
            } elseif ($key === 'SMTP_HOST') {
                $smtp_host = $value;
            } elseif ($key === 'SMTP_PORT') {
                $smtp_port = intval($value);
            } elseif ($key === 'SMTP_USERNAME') {
                $smtp_username = $value;
            } elseif ($key === 'SMTP_PASSWORD') {
                $smtp_password = $value;
            } elseif ($key === 'SMTP_FROM_EMAIL') {
                $smtp_from_email = $value;
            } elseif ($key === 'SMTP_FROM_NAME') {
                $smtp_from_name = $value;
            }
        }
    }
}

// .envファイルがない場合やADMIN_EMAILが設定されていない場合はデフォルト値を使用
if ($admin_email === null) {
    $admin_email = 'admin@example.com';
}

// セッション有効期限（15分）
define('TOKEN_EXPIRY', 15 * 60); // 15分
define('TOKEN_LENGTH', 4); // 4桁トークン

// CSRFトークン生成（暗号化トークン方式）
function generate_csrf_token() {
    $secret_key = 'westfield_csrf_secret_' . DB_PATH; // データベースパスを含む秘密鍵
    $timestamp = time();
    $random = bin2hex(random_bytes(16));
    $data = $timestamp . '|' . $random;
    $hash = hash_hmac('sha256', $data, $secret_key);
    return base64_encode($data . '|' . $hash);
}

// CSRFトークン検証（暗号化トークン方式）
function validate_csrf_token($token) {
    $secret_key = 'westfield_csrf_secret_' . DB_PATH;

    try {
        $decoded = base64_decode($token);
        if ($decoded === false) {
            return false;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return false;
        }

        list($timestamp, $random, $hash) = $parts;

        // トークンの有効期限（30分）
        if (time() - (int)$timestamp > 1800) {
            return false;
        }

        $expected_hash = hash_hmac('sha256', $timestamp . '|' . $random, $secret_key);
        return hash_equals($hash, $expected_hash);
    } catch (Exception $e) {
        return false;
    }
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

    // 予約定型メールテーブル
    $db->exec("CREATE TABLE IF NOT EXISTS booking_templates (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        type TEXT UNIQUE NOT NULL,
        subject TEXT NOT NULL,
        content TEXT NOT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 予約定型メールの初期データ
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM booking_templates");
    $stmt->execute();
    if ($stmt->fetch()['count'] == 0) {
        $templates = [
            [
                'type' => 'normal',
                'subject' => '【WEST FIELD】定例会ご予約を承りました',
                'content' => "{name} 様\n\nこの度はWEST FIELDの定例会にご予約いただき、誠にありがとうございます。\n以下の内容でご予約を承りました。\n\n■参加日: {event_date}\n■チーム名: {team_name}\n■ご利用人数: {people}名\n■代表者名: {participants}\n■送迎: {pickup_status}\n■レンタル品（電動ガン）: {rental_gun}\n■レンタル品（ゴーグル）: {rental_goggle}\n\n当日、皆様にお会いできることを楽しみにしております。\nお気をつけてお越しください。\n\n---\nWEST FIELD\nhttps://westfield.example.com"
            ],
            [
                'type' => 'night_battle',
                'subject' => '【WEST FIELD】夜戦ご予約を承りました',
                'content' => "{name} 様\n\nこの度はWEST FIELDの夜戦にご予約いただき、誠にありがとうございます。\n以下の内容でご予約を承りました。\n\n■参加日: {event_date}\n■チーム名: {team_name}\n■ご利用人数: {people}名\n■代表者名: {participants}\n■送迎: {pickup_status}\n■レンタル品（電動ガン）: {rental_gun}\n■レンタル品（ゴーグル）: {rental_goggle}\n\n夜戦は非常に暗くなりますので、ライト等の準備をお願いいたします。\n当日、楽しみにしております。\n\n---\nWEST FIELD\nhttps://westfield.example.com"
            ],
            [
                'type' => 'rental',
                'subject' => '【WEST FIELD】貸切予約を承りました',
                'content' => "{name} 様\n\nこの度はWEST FIELDの貸切をご予約いただき、誠にありがとうございます。\n以下の内容でご予約を承りました。\n\n■参加日: {event_date}\n■チーム名: {team_name}\n■ご利用人数: {people}名\n■代表者名: {participants}\n■送迎: {pickup_status}\n■レンタル品（電動ガン）: {rental_gun}\n■レンタル品（ゴーグル）: {rental_goggle}\n\n貸切当日は、皆様のご来場を心よりお待ちしております。\nご不明な点がございましたら、お気軽にお問い合わせください。\n\n---\nWEST FIELD\nhttps://westfield.example.com"
            ]
        ];
        $stmt = $db->prepare("INSERT INTO booking_templates (type, subject, content) VALUES (?, ?, ?)");
        foreach ($templates as $t) {
            $stmt->execute([$t['type'], $t['subject'], $t['content']]);
        }
    }

    // 予約テーブル
    $db->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        booking_type TEXT NOT NULL,
        name_kanji TEXT NOT NULL,
        name_kana TEXT NOT NULL,
        email TEXT NOT NULL,
        address TEXT,
        tel TEXT,
        event_date TEXT,
        team_name TEXT,
        people INTEGER,
        participants TEXT,
        pickup TEXT,
        pickup_people TEXT,
        rental_gun TEXT,
        rental_goggle TEXT,
        ip_address TEXT,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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