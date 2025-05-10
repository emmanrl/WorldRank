
<?php
require_once '../Private/functions.php';
require_once '../Private/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reset_code = $_POST['reset_code'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($reset_code) || empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide both reset code and email']);
        exit;
    }

    try {
        $pdo = db_connect();
        
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND reset_code = ? AND expires_at > NOW() AND used = 0");
        $stmt->execute([$email, $reset_code]);
        
        if ($row = $stmt->fetch()) {
            // Mark the reset code as used
            $updateStmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND reset_code = ?");
            $updateStmt->execute([$email, $reset_code]);
            
            echo json_encode(['status' => 'success', 'message' => 'Reset code verified successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired reset code']);
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while verifying the reset code']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
