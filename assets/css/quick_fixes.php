<?php
// quick_fixes.php - Run this once to fix common issues
include 'config.php';

echo "<h3>Applying Quick Fixes...</h3>";

// Fix 1: Check if sessions work
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "✅ Sessions started correctly<br>";
}

// Fix 2: Verify database connection
try {
    $pdo->query("SELECT 1");
    echo "✅ Database connection working<br>";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
}

// Fix 3: Check if admin user exists
$admin = $pdo->query("SELECT * FROM teachers WHERE teacher_id = 'ADMIN001'")->fetch();
if ($admin) {
    echo "✅ Admin user exists<br>";
} else {
    echo "❌ Admin user not found - please import database.sql again<br>";
}

echo "<h3>Quick fixes applied! Check if issues are resolved.</h3>";
?>