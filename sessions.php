<?php
require 'db_connect.php';
$conn = getConnection();
$message = '';

// ----- GESTION CREATION -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $course_id = $_POST['course_id'] ?? '';
    $group_id = $_POST['group_id'] ?? '';
    $professor_id = $_POST['professor_id'] ?? '';

    if ($course_id && $group_id && $professor_id) {
        $stmt = $conn->prepare("INSERT INTO attendance_sessions (course_id, group_id, opened_by) VALUES (?, ?, ?)");
        $stmt->execute([$course_id, $group_id, $professor_id]);
        $message = "✅ Session created with ID: " . $conn->lastInsertId();
        $messageType = 'success';
    } else {
        $message = "❌ Please fill all fields to create a session.";
        $messageType = 'error';
    }
}

// ----- GESTION FERMETURE -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close'])) {
    $session_id = $_POST['session_id'] ?? '';
    if ($session_id) {
        $stmt = $conn->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
        $stmt->execute([$session_id]);
        if ($stmt->rowCount() > 0) {
            $message = "✅ Session $session_id has been closed.";
            $messageType = 'success';
        } else {
            $message = "❌ Session $session_id not found or already closed.";
            $messageType = 'error';
        }
    } else {
        $message = "❌ Please enter a session ID to close.";
        $messageType = 'error';
    }
}

// ----- RECUPERATION DES SESSIONS -----
$result = $conn->query("SELECT * FROM attendance_sessions ORDER BY date DESC");
$sessions = $result->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="stylee.css">

<div class="container">
    <h2>Attendance Sessions Dashboard</h2>

    <?php if (!empty($message)): ?>
        <div class="confirmation <?= $messageType ?> show"><?= $message ?></div>
    <?php endif; ?>

    <!-- FORM CREATION -->
    <h3>Create Session</h3>
    <form method="post">
        <label>Course ID:</label>
        <input type="number" name="course_id" required>

        <label>Group ID:</label>
        <input type="number" name="group_id" required>

        <label>Professor ID:</label>
        <input type="number" name="professor_id" required>

        <button class="btn-submit" type="submit" name="create">Create Session</button>
    </form>

    <!-- FORM FERMETURE -->
    <h3>Close Session</h3>
    <form method="post">
        <label>Session ID:</label>
        <input type="number" name="session_id" required>
        <button class="btn-submit" type="submit" name="close">Close Session</button>
    </form>

    <!-- TABLE DES SESSIONS -->
    <h3>All Sessions</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Course ID</th>
            <th>Group ID</th>
            <th>Opened By</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
        <?php foreach($sessions as $s): ?>
        <tr class="<?= $s['status'] === 'closed' ? 'closed' : '' ?>">
            <td><?= $s['id'] ?></td>
            <td><?= $s['course_id'] ?></td>
            <td><?= $s['group_id'] ?></td>
            <td><?= $s['opened_by'] ?></td>
            <td><?= ucfirst($s['status']) ?></td>
            <td><?= $s['date'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="centered-container">
        <a class="btn-link" href="create_session.php">Back to Create Session</a>
    </div>
</div>

<script>
  // disparition automatique du message après 3s
  const msg = document.querySelector('.confirmation');
  if(msg){
    setTimeout(() => { msg.classList.remove('show'); }, 3000);
  }
</script>
