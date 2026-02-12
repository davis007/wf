<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>送信先管理 - WEST FIELD 管理パネル</title>
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
                        <a href="recipients.php" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- 左側: 追加/編集フォーム -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <?php echo $edit_recipient ? '送信先編集' : '送信先追加'; ?>
                        </h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="action" value="<?php echo $edit_recipient ? 'edit' : 'add'; ?>">
                            <?php if ($edit_recipient): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_recipient['id']; ?>">
                            <?php endif; ?>

                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">名前</label>
                                    <input type="text" id="name" name="name"
                                           value="<?php echo $edit_recipient ? htmlspecialchars($edit_recipient['name']) : ''; ?>"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">メールアドレス</label>
                                    <input type="email" id="email" name="email"
                                           value="<?php echo $edit_recipient ? htmlspecialchars($edit_recipient['email']) : ''; ?>"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                </div>

                                <?php if ($edit_recipient): ?>
                                    <div class="flex items-center">
                                        <input type="checkbox" id="subscribed" name="subscribed"
                                               value="1" <?php echo $edit_recipient['subscribed'] ? 'checked' : ''; ?>
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <label for="subscribed" class="ml-2 block text-sm text-gray-900">
                                            メール購読を有効にする
                                        </label>
                                    </div>
                                <?php endif; ?>

                                <div class="flex space-x-3">
                                    <button type="submit"
                                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-save mr-2"></i>
                                        <?php echo $edit_recipient ? '更新' : '追加'; ?>
                                    </button>

                                    <?php if ($edit_recipient): ?>
                                        <a href="recipients.php"
                                           class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md text-center">
                                            <i class="fas fa-times mr-2"></i>キャンセル
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- CSVインポート（簡易版） -->
                <div class="mt-6 bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">CSVインポート</h3>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <p class="text-sm text-gray-500 mb-4">名前,メールアドレスの形式でCSVファイルをインポートできます。</p>
                        <form method="POST" action="import_csv.php" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <div class="space-y-4">
                                <div>
                                    <input type="file" id="csv_file" name="csv_file"
                                           accept=".csv"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <button type="submit"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <i class="fas fa-file-import mr-2"></i>CSVインポート
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 右側: 送信先一覧 -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">送信先一覧</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">全 <?php echo $total_count; ?> 件</p>
                            </div>
                            <div class="flex space-x-2">
                                <!-- 検索フォーム -->
                                <form method="GET" action="" class="flex">
                                    <input type="text" name="search"
                                           value="<?php echo htmlspecialchars($search); ?>"
                                           placeholder="名前またはメールで検索"
                                           class="border border-gray-300 rounded-l-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-r-md">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <?php if (!empty($search)): ?>
                                        <a href="recipients.php"
                                           class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                </form>

                                <!-- CSVエクスポート -->
                                <a href="export_csv.php"
                                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                                    <i class="fas fa-file-export mr-1"></i>CSV出力
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">名前</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">メールアドレス</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">購読</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">登録日</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($recipients)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            送信先がありません
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recipients as $recipient): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($recipient['name']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($recipient['email']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $recipient['subscribed'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <?php echo $recipient['subscribed'] ? '購読中' : '停止中'; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('Y/m/d', strtotime($recipient['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="?edit=<?php echo $recipient['id']; ?>"
                                                       class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="" class="inline" onsubmit="return confirm('本当に削除しますか？');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $recipient['id']; ?>">
                                                        <button type="submit" class="text-red-600 hover:text-red-900">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ページネーション -->
                    <?php if ($total_pages > 1): ?>
                        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 sm:px-6">
                            <div class="flex justify-between">
                                <div>
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            前へ
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-700">
                                    ページ <?php echo $page; ?> / <?php echo $total_pages; ?>
                                </div>
                                <div>
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"
                                           class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            次へ
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>