<?php
require_once 'config.php';

// 既にログイン済みならダッシュボードへ
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// メールアドレス入力フォーム処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '有効なメールアドレスを入力してください';
    } elseif ($email !== $admin_email) {
        $error = '管理者メールアドレスが一致しません';
    } else {
        // 過去のトークンを削除
        $db = get_db();
        $stmt = $db->prepare("DELETE FROM login_tokens WHERE email = ? OR expires_at < datetime('now')");
        $stmt->execute([$email]);

        // トークン生成
        $token = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $expires_at = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);

        // トークン保存
        $stmt = $db->prepare("INSERT INTO login_tokens (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires_at]);

        // メール送信
        require_once 'mailer.php';
        $subject = '【WEST FIELD】管理パネルログイン認証コード';
        $body = "管理パネルへのログインが要求されました。\n\n";
        $body .= "認証コード: " . $token . "\n\n";
        $body .= "このコードは15分間有効です。心当たりがない場合は、このメールを破棄してください。";

        if (send_email($email, '管理者', $subject, $body)) {
            $success = "認証コードをメール送信しました。メールを確認して入力してください。";
        } else {
            $error = "メール送信に失敗しました。SMTP設定を確認してください。";
            // 開発環境用に表示（本番では削除推奨）
            $success = "（デバッグ用）メール送信に失敗したため画面表示します: $token";
        }
        $_SESSION['login_email'] = $email;
    }
}

// トークン入力フォーム処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'])) {
    $token = trim($_POST['token']);
    $email = $_SESSION['login_email'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($csrf_token)) {
        $error = 'CSRFトークンが無効です';
    } elseif (empty($email)) {
        $error = 'セッションが切れました。最初からやり直してください';
    } elseif (empty($token) || !preg_match('/^\d{4}$/', $token)) {
        $error = '4桁の数字を入力してください';
    } else {
        $db = get_db();

        // トークン検証
        $stmt = $db->prepare("SELECT * FROM login_tokens WHERE email = ? AND token = ? AND expires_at > datetime('now') ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email, $token]);
        $token_record = $stmt->fetch();

        if (!$token_record) {
            $error = 'トークンが無効または期限切れです';

            // 試行回数を増やす
            $stmt = $db->prepare("UPDATE login_tokens SET attempts = attempts + 1 WHERE email = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email]);

            // 3回失敗で新しいトークンが必要
            $stmt = $db->prepare("SELECT attempts FROM login_tokens WHERE email = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$email]);
            $attempts = $stmt->fetch()['attempts'] ?? 0;

            if ($attempts >= 3) {
                $stmt = $db->prepare("DELETE FROM login_tokens WHERE email = ?");
                $stmt->execute([$email]);
                unset($_SESSION['login_email']);
                $error = '3回失敗しました。新しいトークンを発行してください';
            }
        } else {
            // ログイン成功
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_email'] = $email;

            // 使用済みトークン削除
            $stmt = $db->prepare("DELETE FROM login_tokens WHERE email = ?");
            $stmt->execute([$email]);

            unset($_SESSION['login_email']);
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理パネルログイン - WEST FIELD</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">WEST FIELD 管理パネル</h1>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_SESSION['login_email'])): ?>
                <!-- メールアドレス入力フォーム -->
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            管理者メールアドレス
                        </label>
                        <input type="email" id="email" name="email"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="<?php echo htmlspecialchars($admin_email); ?>"
                               required>
                    </div>

                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-md focus:outline-none focus:shadow-outline transition duration-300">
                        <i class="fas fa-paper-plane mr-2"></i>ログイントークンを送信
                    </button>
                </form>
            <?php else: ?>
                <!-- トークン入力フォーム -->
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="token">
                            4桁認証コード
                        </label>
                        <input type="text" id="token" name="token"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-center text-2xl tracking-widest"
                               placeholder="0000"
                               maxlength="4"
                               pattern="\d{4}"
                               required>
                        <p class="text-gray-500 text-xs mt-1">メールで送信された4桁のコードを入力（15分以内）</p>
                    </div>

                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-md focus:outline-none focus:shadow-outline transition duration-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>ログイン
                    </button>

                    <div class="mt-4 text-center">
                        <a href="login.php" class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-redo mr-1"></i>別のメールアドレスでログイン
                        </a>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>