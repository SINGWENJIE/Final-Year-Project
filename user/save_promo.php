<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['applied_promo'] = [
        'code' => $_POST['code'],
        'amount' => (float)$_POST['amount']
    ];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}