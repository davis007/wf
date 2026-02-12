<?php
require_once 'config.php';

$db = get_db();

// テーブル作成
$sql = "
-- 管理者認証トークン
CREATE TABLE IF NOT EXISTS admin_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    attempts INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL
);

-- 送信先
CREATE TABLE IF NOT EXISTS recipients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    subscribed INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 配信ログ
CREATE TABLE IF NOT EXISTS delivery_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    recipient_id INTEGER NOT NULL,
    subject TEXT NOT NULL,
    status TEXT NOT NULL,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipient_id) REFERENCES recipients (id)
);

-- 署名
CREATE TABLE IF NOT EXISTS signature (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- テストデータ挿入
INSERT OR IGNORE INTO recipients (name, email, subscribed) VALUES
('テストユーザー1', 'test1@example.com', 1),
('テストユーザー2', 'test2@example.com', 1),
('テストユーザー3', 'test3@example.com', 0);

INSERT OR IGNORE INTO signature (content) VALUES
('WEST FIELD サバイバルゲームフィールド
〒586-0052 大阪府河内長野市河合寺４２６
TEL: 090-9715-1979
営業時間: 10:00-16:00 (フィールド開閉 9:00-17:00)');
";

try {
    $db->exec($sql);
    echo "データベースの初期化が完了しました。<br>";
    echo "以下のテストデータが挿入されました:<br>";
    echo "- テストユーザー3件<br>";
    echo "- デフォルト署名<br><br>";

    // テーブル一覧を表示
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll();
    echo "作成されたテーブル:<br>";
    foreach ($tables as $table) {
        echo "- " . $table['name'] . "<br>";
    }

    echo "<br><a href='login.php'>ログインページへ</a>";

} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
?>