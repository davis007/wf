<?php
// ページタイトルと現在のページを設定
$page_title = '配信ログ - WEST FIELD 管理パネル';
$current_page = 'logs';
?>

<?php include_once 'header.php'; ?>

        <!-- 統計カード -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <i class="fas fa-paper-plane text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">総送信件数</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $stats['total']; ?> 件</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">成功</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $stats['success']; ?> 件</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                            <i class="fas fa-times-circle text-white text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">失敗</dt>
                                <dd class="text-lg font-medium text-gray-900"><?php echo $stats['failed']; ?> 件</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 検索フォーム -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">ログ検索</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                <form method="GET" action="" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- キーワード検索 -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">キーワード</label>
                            <input type="text" id="search" name="search"
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="名前、メール、件名">
                        </div>

                        <!-- ステータス -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">ステータス</label>
                            <select id="status" name="status"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">すべて</option>
                                <option value="success" <?php echo $status === 'success' ? 'selected' : ''; ?>>成功</option>
                                <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>失敗</option>
                            </select>
                        </div>

                        <!-- 日付範囲（開始） -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700">開始日</label>
                            <input type="date" id="date_from" name="date_from"
                                   value="<?php echo htmlspecialchars($date_from); ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- 日付範囲（終了） -->
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700">終了日</label>
                            <input type="date" id="date_to" name="date_to"
                                   value="<?php echo htmlspecialchars($date_to); ?>"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i>検索
                        </button>
                        <a href="logs.php"
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-md">
                            <i class="fas fa-times mr-2"></i>リセット
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- ログ一覧 -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">配信ログ一覧</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">全 <?php echo $total_count; ?> 件</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        ページ <?php echo $page; ?> / <?php echo $total_pages; ?>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">送信日時</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">送信先</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">件名</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    ログがありません
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo date('Y/m/d H:i:s', strtotime($log['sent_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($log['name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($log['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($log['subject']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php echo $log['status'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $log['status'] === 'success' ? '成功' : '失敗'; ?>
                                        </span>
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
                                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>"
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
                                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?><?php echo !empty($status) ? '&status=' . urlencode($status) : ''; ?><?php echo !empty($date_from) ? '&date_from=' . urlencode($date_from) : ''; ?><?php echo !empty($date_to) ? '&date_to=' . urlencode($date_to) : ''; ?>"
                                   class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    次へ
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

<?php include_once 'footer.php'; ?>