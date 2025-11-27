<?php
require 'db_connect.php';
$conn = getConnection();

$message = "";
$students = [];
$existing_attendance = [];
$session_id = 0;

// Récupérer toutes les sessions ouvertes
$stmt = $conn->query("SELECT * FROM attendance_sessions WHERE status='open' ORDER BY date DESC");
$sessions = $stmt->fetchAll();

// Si session sélectionnée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id'])) {
    $session_id = (int)$_POST['session_id'];

    // Vérifier que la session existe
    $stmt = $conn->prepare("SELECT * FROM attendance_sessions WHERE id = :id AND status='open'");
    $stmt->execute([':id' => $session_id]);
    $session = $stmt->fetch();
    if (!$session) die("No open session found with this ID.");

    // Récupérer les étudiants du groupe
    $stmt = $conn->prepare("SELECT * FROM students WHERE group_id=:group_id ORDER BY fullname");
    $stmt->execute([':group_id' => $session['group_id']]);
    $students = $stmt->fetchAll();

    // Nom du fichier JSON pour cette session
    $filename = "attendance_{$session_id}_" . date('Y-m-d') . ".json";

    // Charger les présences existantes
    if (file_exists($filename)) {
        $existing_attendance = json_decode(file_get_contents($filename), true);
    }

    // Enregistrer les nouvelles présences si le formulaire a été soumis
    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $attendance = [];
        foreach ($students as $s) {
            $status = $_POST['status'][$s['id']] ?? 'absent';
            $attendance[] = [
                'student_id' => $s['id'],
                'fullname'   => $s['fullname'],
                'status'     => $status
            ];
        }
        if (file_put_contents($filename, json_encode($attendance, JSON_PRETTY_PRINT))) {
            $message = "✅ Attendance saved successfully in <b>$filename</b>!";
            $existing_attendance = $attendance;
        } else {
            $message = "❌ Error: could not write the attendance file.";
        }
    }
}
?>

<link rel="stylesheet" href="stylee.css">

<div class="container">
<h2>Take Attendance</h2>

<?php if (!empty($message)): ?>
    <div class="confirmation success show"><?= $message ?></div>
<?php endif; ?>

<!-- Formulaire pour choisir la session -->
<form method="post">
    <label>Select Session:</label>
    <select name="session_id" onchange="this.form.submit()">
        <option value="">-- Choose a session --</option>
        <?php foreach ($sessions as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($s['id']==$session_id)?'selected':'' ?>>
                Session ID <?= $s['id'] ?> | Course <?= $s['course_id'] ?> | Group <?= $s['group_id'] ?> | <?= $s['date'] ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if (!empty($students)): ?>

    <!-- Affichage des présences existantes -->
    <?php if (!empty($existing_attendance)): ?>
        <h3>Existing Attendance</h3>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Status</th>
            </tr>
            <?php foreach($existing_attendance as $att): ?>
                <tr style="background-color: <?= $att['status']=='present' ? '#d4edda' : '#f8d7da' ?>;">
                    <td><?= $att['student_id'] ?></td>
                    <td><?= htmlspecialchars($att['fullname']) ?></td>
                    <td><?= ucfirst($att['status']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br>
    <?php endif; ?>

    <!-- Formulaire pour modifier/enregistrer les présences -->
    <form method="post">
        <input type="hidden" name="session_id" value="<?= $session_id ?>">
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Status</th>
            </tr>
            <?php foreach ($students as $s): 
                $selected_status = 'absent';
                foreach ($existing_attendance as $ea) {
                    if ($ea['student_id'] == $s['id']) {
                        $selected_status = $ea['status'];
                        break;
                    }
                }
                $bgcolor = ($selected_status==='present') ? '#d4edda' : '#f8d7da';
            ?>
            <tr style="background-color: <?= $bgcolor ?>;">
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['fullname']) ?></td>
                <td>
                    <select name="status[<?= $s['id'] ?>]">
                        <option value="present" <?= ($selected_status==='present')?'selected':'' ?>>Present</option>
                        <option value="absent" <?= ($selected_status==='absent')?'selected':'' ?>>Absent</option>
                    </select>
                </td>
            </tr>
            <?php endforeach; ?>
        </table><br>
        <button type="submit" class="btn-submit">Save Attendance</button>
    </form>
<?php endif; ?>

<br>
<a class="btn-link" href="create_session.php">Back to Create Session</a>
</div>

<script>
    const msg = document.querySelector('.confirmation');
    if(msg){
        setTimeout(() => { msg.classList.remove('show'); }, 3000);
    }
</script>
