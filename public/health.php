<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'status' => 'ok',
    'service' => 'seller-rating',
    'time' => date(DATE_ATOM),
]);

