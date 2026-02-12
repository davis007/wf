<?php
require_once 'auth_check.php';
check_admin_login();

$db = get_db();
$error = '';
$success = '';
$imported_count = 0;
$skipped_count = 0;

// CSVインポート処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'CSVファイルを選択してください';
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        $filename = $_FILES['csv_file']['name'];

        // ファイル拡張子チェック
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $error = 'CSVファイルのみアップロード可能です';
        } else {
            // CSVファイルを読み込み
            if (($handle = fopen($file, 'r')) !== false) {
                $line_number = 0;

                // トランザクション開始
                $db->beginTransaction();

                try {
                    while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) !== false) {
                        $line_number++;

                        // ヘッダー行をスキップ（オプション）
                        if ($line_number === 1 && (strtolower($data[0]) === 'name' || strtolower($data[0]) === '名前')) {
                            continue;
                        }

                        // データのバリデーション
                        if (count($data) < 2) {
                            $skipped_count++;
                            continue;
                        }

                        $name = trim($data[0] ?? '');
                        $email = trim($data[1] ?? '');

                        // 必須チェック
                        if (empty($name) || empty($email)) {
                            $skipped_count++;
                            continue;
                        }

                        // メールアドレス形式チェック
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $skipped_count++;
                            continue;
                        }

                        // 重複チェック
                        $stmt = $db->prepare("SELECT id FROM recipients WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $skipped_count++;
                            continue;
                        }

                        // データ挿入
                        $stmt = $db->prepare("INSERT INTO recipients (name, email) VALUES (?, ?)");
                        $stmt->execute([$name, $email]);
                        $imported_count++;
                    }

                    fclose($handle);

                    // トランザクションコミット
                    $db->commit();

                    $success = "CSVインポート完了: {$imported_count}件追加, {$skipped_count}件スキップ";

                } catch (Exception $e) {
                    // トランザクションロールバック
                    $db->rollBack();
                    $error = 'インポート中にエラーが発生しました: ' . $e->getMessage();
                }
            } else {
                $error = 'CSVファイルを開けませんでした';
            }
        }
    }
}

// テンプレート変数を配列にまとめる
$template_vars = [
    'error' => $error,
    'success' => $success,
    'imported_count' => $imported_count,
    'skipped_count' => $skipped_count,
    'admin_email' => $_SESSION['admin_email']
];

// HTMLテンプレートをインクルード
include_once 'templates/import_csv_template.php';
?>