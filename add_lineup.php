<?php
session_start();
include 'db.php';

if (!isset($_SESSION['leader'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leader = $_POST['leader'];
    $songs = $_POST['songs']; // this is an array of songs

    $conflicts = [];

    foreach ($songs as $song) {
        $song = trim($song);
        if (empty($song)) continue;

        // Check 2-month rule
        $stmtCheck = $db->prepare("SELECT leader, song, created_at FROM lineups WHERE created_at >= datetime('now', '-2 months') AND song=?");
        $stmtCheck->execute([$song]);
        $recent = $stmtCheck->fetchAll();

        if (!empty($recent)) {
            foreach ($recent as $r) {
                $conflicts[] = $r;
            }
        }
    }

    if (!empty($conflicts)) {
        $error = "Warning! The following song(s) were sung within the last 2 months:<br>";
        foreach ($conflicts as $c) {
            $error .= htmlspecialchars($c['song']) . " by " . htmlspecialchars($c['leader']) . " on " . $c['created_at'] . "<br>";
        }
    } else {
        // Insert songs
        $stmt = $db->prepare("INSERT INTO lineups (leader, song) VALUES (?, ?)");
        foreach ($songs as $song) {
            $song = trim($song);
            if ($song != '') $stmt->execute([$leader, $song]);
        }
        $success = "Lineup saved successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Add New Lineup</title></head>
<body>
<h2>Add New Lineup</h2>
<?php 
if ($error) echo "<p style='color:red;'>$error</p>"; 
if ($success) echo "<p style='color:green;'>$success</p>"; 
?>

<form method="post">
    Leader Name: <input type="text" name="leader" required><br><br>

    <div id="songContainer">
        Song Name: <input type="text" name="songs[]" required>
    </div>

    <button type="button" onclick="addSong()">+ Add Song</button><br><br>
    <button type="submit">Save Lineup</button>
</form>

<script>
function addSong() {
    const container = document.getElementById('songContainer');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'songs[]';
    input.required = true;
    container.appendChild(document.createElement('br'));
    container.appendChild(input);
}
</script>
</body>
</html>
