<?php
$db = new PDO('sqlite:'.__DIR__.'/songs_db.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create lineups table
$db->exec("CREATE TABLE IF NOT EXISTS lineups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    leader TEXT NOT NULL,
    song TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Create users table
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL
)");

// Insert default admin user if not exists
$stmt = $db->prepare("SELECT * FROM users WHERE username='admin'");
$stmt->execute();
if (!$stmt->fetch()) {
    $db->prepare("INSERT INTO users (username,password) VALUES ('admin','admin')")->execute();
}
?>
