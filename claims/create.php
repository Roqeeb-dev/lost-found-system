<?php
session_start();
require_once '../config/db.php';

// ── Must be logged in ──────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// ── Only accept POST requests ──────────────────────────────────────────────
// If someone tries to visit this URL directly in the browser, send them away
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../items/list.php");
    exit();
}

// ── Collect and sanitize inputs ────────────────────────────────────────────
$item_id = isset($_POST['item_id']) ? (int) $_POST['item_id'] : 0;
$answer  = trim($_POST['answer'] ?? '');
$user_id = $_SESSION['user_id'];

// ── Basic validation ───────────────────────────────────────────────────────
if ($item_id === 0 || $answer === '') {
    $_SESSION['flash'] = 'Please fill in all fields before submitting.';
    header("Location: ../items/view.php?id=$item_id");
    exit();
}

// ── Fetch the item being claimed ───────────────────────────────────────────
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();

// Item doesn't exist
if (!$item) {
    header("Location: ../items/list.php");
    exit();
}

// ── Run through all the same checks as view.php ───────────────────────────

// Can't claim your own item
if ($item['user_id'] == $user_id) {
    $_SESSION['flash_error'] = 'You cannot claim your own item.';
    header("Location: ../items/view.php?id=$item_id");
    exit();
}

// Only found items can be claimed
if ($item['type'] !== 'found') {
    $_SESSION['flash_error'] = 'Only found items can be claimed.';
    header("Location: ../items/view.php?id=$item_id");
    exit();
}

// Item must still be available
if ($item['status'] !== 'available') {
    $_SESSION['flash_error'] = 'This item has already been claimed.';
    header("Location: ../items/view.php?id=$item_id");
    exit();
}

// ── Check if user already submitted a claim for this item ─────────────────
$existing = $conn->prepare("
    SELECT id FROM claims
    WHERE item_id = ? AND user_id = ?
");
$existing->bind_param("ii", $item_id, $user_id);
$existing->execute();

if ($existing->get_result()->fetch_assoc()) {
    $_SESSION['flash_error'] = 'You have already submitted a claim for this item.';
    header("Location: ../items/view.php?id=$item_id");
    exit();
}

// ── All checks passed — insert the claim ──────────────────────────────────
$insert = $conn->prepare("
    INSERT INTO claims (item_id, user_id, answer_given, status)
    VALUES (?, ?, ?, 'pending')
");
$insert->bind_param("iis", $item_id, $user_id, $answer);
$insert->execute();

// ── Update item status to 'claimed' ───────────────────────────────────────
// This prevents other users from also submitting claims
$update = $conn->prepare("
    UPDATE items SET status = 'claimed' WHERE id = ?
");
$update->bind_param("i", $item_id);
$update->execute();

// ── Set success flash message and redirect back to item ───────────────────
$_SESSION['flash'] = 'Your claim has been submitted! Admin will review it shortly.';
header("Location: ../items/view.php?id=$item_id");
exit();
?>