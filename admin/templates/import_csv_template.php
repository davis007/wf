<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSVインポート - WEST FIELD 管理パネル</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-800">WEST FIELD 管理パネル</h1>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="dashboard.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>ダッシュボード
                        </a>
                        <a href="recipients.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i>送信先管理
                        </a>
                        <a href="send_mail.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>メール送信
                        </a>
                        <a href="logs.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-history mr-2"></i>配信ログ
                        </a>
                        <a href="signature.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-signature mr-2"></i>署名編集
                        </a>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">
                        <i class="fas fa-user-circle mr-1"></i><?php echo htmlspecialchars($admin_email); ?>
                    </span>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-sign-out-alt mr-1"></i>ログアウト
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- メッセージ表示 -->
        <?php if ($error): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">CSVインポート</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <div class="space-y-6">
                    <!-- インポートフォーム -->
                    <div>
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                            <div class="space-y-4">
                                <!-- CSVファイル選択 -->
                                <div>
                                    <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                                        CSVファイルを選択
                                    </label>
                                    <input type="file" id="csv_file" name="csv_file"
                                           accept=".csv"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                           required>
                                    <p class="mt-1 text-sm text-gray-500">
                                        CSVファイル（.csv）のみ対応しています
                                    </p>
                                </div>

                                <!-- インポートボタン -->
                                <div>
                                    <button type="submit"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-file-import mr-2"></i>CSVをインポート
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- CSV形式説明 -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">CSVファイル形式</h4>
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">必須フォーマット（カンマ区切り）:</p>
                                    <div class="bg-white border border-gray-300 rounded p-3 font-mono text-sm">
                                        名前,メールアドレス
                                    </div>
                                </div>

                                <div>
                                    <p class="text-sm text-gray-600 mb-1">例:</p>
                                    <div class="bg-white border border-gray-300 rounded p-3 font-mono text-sm">
                                        山田太郎,taro@example.com<br>
                                        佐藤花子,hanako@example.com<br>
                                        鈴木一郎,ichiro@example.com
                                    </div>
                                </div>

                                <div class="text-sm text-gray-600">
                                    <p class="font-medium mb-1">注意事項:</p>
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>1行目はヘッダー行（「名前,メールアドレス」）でもデータ行でも構いません</li>
                                        <li>メールアドレスは重複チェックされます（既存のメールアドレスはスキップ）</li>
                                        <li>無効なメールアドレス形式の行はスキップされます</li>
                                        <li>名前またはメールアドレスが空の行はスキップされます</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- インポート結果（成功時のみ表示） -->
                    <?php if ($success): ?>
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">インポート結果</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-2">
                                            <i class="fas fa-check text-white"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-green-800">追加件数</p>
                                            <p class="text-lg font-bold text-green-900"><?php echo $imported_count; ?> 件</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-2">
                                            <i class="fas fa-exclamation text-white"></i>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-yellow-800">スキップ件数</p>
                                            <p class="text-lg font-bold text-yellow-900"><?php echo $skipped_count; ?> 件</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- アクションリンク -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex justify-between">
                            <a href="recipients.php"
                               class="inline-flex items-center text-blue-600 hover:text-blue-900">
                                <i class="fas fa-arrow-left mr-2"></i>送信先管理に戻る
                            </a>

                            <a href="export_csv.php"
                               class="inline-flex items-center text-green-600 hover:text-green-900">
                                <i class="fas fa-file-export mr-2"></i>CSVエクスポート
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>