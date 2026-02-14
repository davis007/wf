<?php
/**
 * CSRFトークン取得API
 */

// エラー表示を無効にする
ini_set('display_errors', 0);
error_reporting(E_ALL);

// 設定ファイル読み込み
require_once 'admin/config.php';

// config.phpでエラー表示が有効になっている可能性があるので、再度無効化
ini_set('display_errors', 0);

// レスポンス用ヘッダー
header('Content-Type: application/json; charset=utf-8');

// Originヘッダーがある場合のみ設定
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
}

// セキュリティヘッダー
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// トークン生成
$token = generate_csrf_token();

// レスポンス
echo json_encode([
    'success' => true,
    'token' => $token,
    'timestamp' => time()
]);

// セッションIDの変更（セキュリティ強化）
session_regenerate_id(true);
?>
