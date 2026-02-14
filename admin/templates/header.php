<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? '管理パネル'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .mobile-menu {
            display: none;
        }
        .mobile-menu.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- ナビゲーションバー -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 lg:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- ロゴとタイトル -->
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-800">WEST FIELD 管理パネル</h1>
                    </div>

                    <!-- デスクトップメニュー -->
                    <div class="hidden lg:ml-6 lg:flex lg:space-x-8">
                        <a href="dashboard.php" class="<?php echo ($current_page === 'dashboard') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-tachometer-alt mr-2"></i>ダッシュボード
                        </a>
                        <a href="recipients.php" class="<?php echo ($current_page === 'recipients') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-2"></i>送信先管理
                        </a>
                        <a href="send_mail.php" class="<?php echo ($current_page === 'send_mail') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-paper-plane mr-2"></i>メール送信
                        </a>
                        <a href="logs.php" class="<?php echo ($current_page === 'logs') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-history mr-2"></i>配信ログ
                        </a>
                        <a href="signature.php" class="<?php echo ($current_page === 'signature') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-signature mr-2"></i>署名編集
                        </a>
                        <a href="bookings.php" class="<?php echo ($current_page === 'bookings') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-calendar-check mr-2"></i>予約管理
                        </a>
                        <a href="booking_templates.php" class="<?php echo ($current_page === 'booking_templates') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'; ?> inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-mail-bulk mr-2"></i>予約定型メール
                        </a>
                    </div>
                </div>

                <!-- 右側: ユーザー情報とバーガーメニュー -->
                <div class="flex items-center space-x-4">
                    <!-- ユーザーアイコン（ツールチップ付き） -->
                    <div class="relative group">
                        <button class="text-gray-700 hover:text-gray-900 focus:outline-none">
                            <i class="fas fa-user-circle text-xl"></i>
                        </button>
                        <!-- ツールチップ -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                            <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                <div class="font-medium"><?php echo htmlspecialchars($admin_email); ?></div>
                            </div>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>ログアウト
                            </a>
                        </div>
                    </div>

                    <!-- バーガーメニューボタン（モバイル用） -->
                    <div class="lg:hidden">
                        <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- モバイルメニュー -->
        <div id="mobile-menu" class="mobile-menu lg:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="dashboard.php" class="<?php echo ($current_page === 'dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>ダッシュボード
                </a>
                <a href="recipients.php" class="<?php echo ($current_page === 'recipients') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-users mr-2"></i>送信先管理
                </a>
                <a href="send_mail.php" class="<?php echo ($current_page === 'send_mail') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-paper-plane mr-2"></i>メール送信
                </a>
                <a href="logs.php" class="<?php echo ($current_page === 'logs') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-history mr-2"></i>配信ログ
                </a>
                <a href="signature.php" class="<?php echo ($current_page === 'signature') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-signature mr-2"></i>署名編集
                </a>
                <a href="bookings.php" class="<?php echo ($current_page === 'bookings') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-calendar-check mr-2"></i>予約管理
                </a>
                <a href="booking_templates.php" class="<?php echo ($current_page === 'booking_templates') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'; ?> block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-mail-bulk mr-2"></i>予約定型メール
                </a>
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <div class="px-3 py-2 text-sm text-gray-500">
                        <i class="fas fa-user-circle mr-2"></i><?php echo htmlspecialchars($admin_email); ?>
                    </div>
                    <a href="logout.php" class="block px-3 py-2 text-red-600 hover:bg-gray-50 rounded-md">
                        <i class="fas fa-sign-out-alt mr-2"></i>ログアウト
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 lg:px-6 lg:px-8">

<script>
    // モバイルメニューのトグル
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('active');
    });

    // メニュー外をクリックで閉じる
    document.addEventListener('click', function(event) {
        const mobileMenu = document.getElementById('mobile-menu');
        const menuButton = document.getElementById('mobile-menu-button');

        if (!mobileMenu.contains(event.target) && !menuButton.contains(event.target)) {
            mobileMenu.classList.remove('active');
        }
    });

    // メニュー項目クリックで閉じる
    document.querySelectorAll('#mobile-menu a').forEach(function(link) {
        link.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.remove('active');
        });
    });
</script>