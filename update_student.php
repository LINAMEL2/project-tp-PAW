<?php
require 'db_connect.php';
$conn = getConnection();

// Vérifier si l’ID est passé en GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid student ID.");
}

// Si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname  = trim($_POST['fullname'] ?? '');
    $matricule = trim($_POST['matricule'] ?? '');
    $group_id  = trim($_POST['group_id'] ?? '');

    if ($fullname === '' || $matricule === '' || $group_id === '') {
        $message = "All fields are required.";
    } else {
        try {
            $stmt = $conn->prepare(
                "UPDATE students 
                 SET fullname = :fullname, matricule = :matricule, group_id = :group_id
                 WHERE id = :id"
            );
            $stmt->execute([
                ':fullname'  => $fullname,
                ':matricule' => $matricule,
                ':group_id'  => $group_id,
                ':id'        => $id
            ]);
            $message = "Student updated successfully!";
        } catch (Exception $e) {
            $message = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Récupérer les infos de l’étudiant
$stmt = $conn->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute([':id' => $id]);
$student = $stmt->fetch();
if (!$student) die("Student not found.");

?>
<link rel="stylesheet" href="stylee.css"> 
<div class="container">
<h2>Update Student</h2>
<?php if (!empty($message)) echo "<p style='color:blue'>$message</p>"; ?>

<form method="post">
    <label>Full Name:</label><br>
    <input type="text" name="fullname" value="<?= htmlspecialchars($student['fullname']) ?>" required><br><br>

    <label>Matricule:</label><br>
    <input type="text" name="matricule" value="<?= htmlspecialchars($student['matricule']) ?>" required><br><br>

    <label>Group ID:</label><br>
    <input type="text" name="group_id" value="<?= htmlspecialchars($student['group_id']) ?>" required><br><br>

    <button type="submit">Update Student</button>
</form>

<br>
<a href="list_students.php">Back to Students List</a>
</div>