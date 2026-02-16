<?php
require_once 'admin/config.php';
$db = get_db();

$type = 'rental';
$new_subject = '【WEST FIELD】貸切予約を承りました (Updated)';
$new_content = 'Test content update ' . date('Y-m-d H:i:s');

echo "Attempting to update rental template...\n";

try {
    $stmt = $db->prepare("UPDATE booking_templates SET subject = ?, content = ?, updated_at = datetime('now') WHERE type = ?");
    $result = $stmt->execute([$new_subject, $new_content, $type]);

    if ($result) {
        $count = $stmt->rowCount();
        echo "Update executed. Rows affected: $count\n";

        // Verify update
        $stmt = $db->prepare("SELECT subject, content FROM booking_templates WHERE type = ?");
        $stmt->execute([$type]);
        $row = $stmt->fetch();

        echo "Current Subject: " . $row['subject'] . "\n";
        if ($row['subject'] === $new_subject) {
            echo "SUCCESS: Template updated successfully.\n";
        } else {
            echo "FAILURE: Template was not updated.\n";
        }
    } else {
        echo "FAILURE: Execute returned false.\n";
    }
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
