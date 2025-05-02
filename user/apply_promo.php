<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promoCode = $_POST['promo_code'] ?? '';
    $discount = $_POST['discount'] ?? 0;
    
    $_SESSION['applied_promo'] = [
        'code' => $promoCode,
        'amount' => (float)$discount
    ];
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}