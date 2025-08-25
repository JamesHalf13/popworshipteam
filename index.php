<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Prince of Peace Worship Team Song Monitoring</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">

  <!-- Header with Leader Login -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Prince of Peace Worship Team Song Monitoring</h1>
    <a href="login.php" class="btn btn-primary">Leader Login</a>
  </div>

  <!-- Song History Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      📖 Song History
    </div>
    <div class="card-body p-0">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Date</th>
            <th>Leader</th>
            <th>Song</th>
          </tr>
        </thead>
        <tbody>
          <?php
          include 'db.php';
          $stmt = $db->query("SELECT * FROM lineups ORDER BY created_at DESC");
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              echo "<tr>
                      <td>{$row['created_at']}</td>
                      <td>{$row['leader']}</td>
                      <td>{$row['song']}</td>
                    </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
