<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('buyer');
redirect('marketplace.php');
