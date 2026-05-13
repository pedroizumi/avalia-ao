<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/auth.php';

logout_admin();
redirect_to('/admin/login.php');

