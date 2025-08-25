<?php
session_start();
include 'db.php';

if (!isset($_SESSION['leader'])) {
    header("Location: login.php");
    exit;
}

$leader = $_SESSION['leader'];

// Handle adding new lineup
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_lineup'])) {
    $leader_name = trim($_POST['leader_name']);
    $songs = $_POST['songs'] ?? [];

    foreach ($songs as $song) {
        $song = trim($song);
        if ($song == "") continue;

        // Check if song was sung under 2 months
        $stmt = $db->prepare("SELECT * FROM lineups WHERE song=? AND created_at >= date('now','-2 months')");
        $stmt->execute([$song]);
        if ($stmt->fetch()) {
            $warnings[] = $song;
        } else {
            $stmtInsert = $db->prepare("INSERT INTO lineups (leader, song) VALUES (?, ?)");
            $stmtInsert->execute([$leader_name, $song]);
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->prepare("DELETE FROM lineups WHERE id=?")->execute([$id]);
}

// Handle edit
if (isset($_POST['edit_lineup'])) {
    $id = intval($_POST['edit_id']);
    $song = trim($_POST['edit_song']);
    $leader_name = trim($_POST['edit_leader']);
    $db->prepare("UPDATE lineups SET song=?, leader=? WHERE id=?")->execute([$song, $leader_name, $id]);
}

// Fetch all lineups
$lineups = $db->query("SELECT * FROM lineups ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Determine conflict indices
$conflictIndices = [];
foreach ($lineups as $index => $lineup) {
    $stmt = $db->prepare("SELECT * FROM lineups WHERE song=? AND created_at >= date('now','-2 months')");
    $stmt->execute([$lineup['song']]);
    $recent = $stmt->fetch();
    if ($recent && $recent['id'] != $lineup['id']) {
        $conflictIndices[] = $index;
        $warnings[] = $lineup['song'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Worship Lineup Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .conflict-highlight { border: 2px solid red; background-color: #ffe6e6; }
    </style>
</head>
<body class="p-4">
    <h2>Welcome, <?= htmlspecialchars($leader) ?></h2>

    <!-- Add Lineup Form -->
    <form method="post" class="mb-4">
        <div class="mb-2">
            <label>Song Leader:</label>
            <input type="text" name="leader_name" class="form-control" value="<?= htmlspecialchars($leader) ?>" required>
        </div>

        <div id="songList" class="mb-2">
            <label>Enter Song Name:</label>
            <input type="text" name="songs[]" class="form-control mb-1" required>
        </div>

        <button type="button" id="addSong" class="btn btn-secondary mb-2">+ Add Song</button>
        <button type="submit" name="submit_lineup" class="btn btn-primary">Save Lineup</button>
    </form>

    <!-- Warning Section -->
    <?php if (!empty($warnings)): ?>
        <div class="alert alert-danger">
            <strong>Warning!</strong> These songs were sung under the last 2 months:
            <ul>
                <?php foreach (array_unique($warnings) as $w): ?>
                    <li><?= htmlspecialchars($w) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Song History -->
    <h4>📖 Song History</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Leader</th>
                <th>Song</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lineups as $index => $l): ?>
                <tr>
                    <td><?= $l['created_at'] ?></td>
                    <td><?= htmlspecialchars($l['leader']) ?></td>
                    <td><?= htmlspecialchars($l['song']) ?></td>
                    <td>
                        <a href="?delete=<?= $l['id'] ?>" class="btn btn-danger btn-sm">Delete</a>
                        <button class="btn btn-warning btn-sm edit-btn" data-id="<?= $l['id'] ?>" data-leader="<?= htmlspecialchars($l['leader']) ?>" data-song="<?= htmlspecialchars($l['song']) ?>">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <form method="post">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="mb-2">
                        <label>Song Leader:</label>
                        <input type="text" name="edit_leader" id="edit_leader" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Song Name:</label>
                        <input type="text" name="edit_song" id="edit_song" class="form-control" required>
                    </div>
                    <button type="submit" name="edit_lineup" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add song input
    document.getElementById('addSong').addEventListener('click', function(){
        const div = document.createElement('div');
        div.innerHTML = '<input type="text" name="songs[]" class="form-control mb-1" required>';
        document.getElementById('songList').appendChild(div);
    });

    // Edit button modal
    const editBtns = document.querySelectorAll('.edit-btn');
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_id').value = btn.dataset.id;
            document.getElementById('edit_leader').value = btn.dataset.leader;
            document.getElementById('edit_song').value = btn.dataset.song;
            editModal.show();
        });
    });

    // Highlight conflict songs
    function highlightConflicts(conflictIndices) {
        const formSongs = document.querySelectorAll('#songList input[name="songs[]"]');
        conflictIndices.forEach(i => {
            if(formSongs[i]) formSongs[i].classList.add('conflict-highlight');
        });
    }

    highlightConflicts(<?= json_encode($conflictIndices) ?>);
</script>
</body>
</html>
