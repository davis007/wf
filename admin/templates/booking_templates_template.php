<?php include_once 'header.php'; ?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-800">予約定型メール管理</h2>
    <p class="text-gray-600">予約完了時に送信される自動返信メールの内容を編集できます。</p>
</div>

<?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($error); ?></p>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
        <p><?php echo htmlspecialchars($success); ?></p>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 gap-8">
    <?php foreach ($templates as $template): ?>
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">
                    <?php echo $template['type'] === 'normal' ? '通常予約（定例会）テンプレート' : '夜戦予約テンプレート'; ?>
                </h3>
                <p class="text-sm text-gray-500">最終更新: <?php echo $template['updated_at']; ?></p>
            </div>
            <div class="p-6">
                <form action="booking_templates.php" method="POST">
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($template['type']); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">件名</label>
                        <input type="text" name="subject" value="<?php echo htmlspecialchars($template['subject']); ?>" class="w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">本文</label>
                        <textarea name="content" rows="10" class="w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"><?php echo htmlspecialchars($template['content']); ?></textarea>
                        <p class="mt-2 text-xs text-gray-500">
                            以下のタグが使用可能です：<br>
                            <code class="bg-gray-100 px-1">{name}</code> (予約者名),
                            <code class="bg-gray-100 px-1">{event_date}</code> (参加日),
                            <code class="bg-gray-100 px-1">{team_name}</code> (チーム名),
                            <code class="bg-gray-100 px-1">{people}</code> (人数),
                            <code class="bg-gray-100 px-1">{participants}</code> (代表者),
                            <code class="bg-gray-100 px-1">{pickup_status}</code> (送迎有無),
                            <code class="bg-gray-100 px-1">{rental_gun}</code> (レンタルガン),
                            <code class="bg-gray-100 px-1">{rental_goggle}</code> (レンタルゴーグル)
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                            <i class="fas fa-save mr-2"></i>保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include_once 'footer.php'; ?>
