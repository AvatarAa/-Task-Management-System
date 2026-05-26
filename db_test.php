<?php
require 'db.php';
echo "DB connected. PDO driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
