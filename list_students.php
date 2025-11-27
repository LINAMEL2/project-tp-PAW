<?php
require 'db_connect.php';

try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT * FROM students ORDER BY id DESC");
    $students = $stmt->fetchAll();
} catch (Exception $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>

<link rel="stylesheet" href="stylee.css">

<div class="container">
    <h2>Students List</h2>

    <?php if (empty($students)): ?>
        <p>No students found. <a class="btn-link" href="add_student.php">Add a student</a></p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Matricule</th>
                    <th>Group</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= $s['id'] ?></td>
                    <td><?= htmlspecialchars($s['fullname']) ?></td>
                    <td><?= htmlspecialchars($s['matricule']) ?></td>
                    <td><?= htmlspecialchars($s['group_id']) ?></td>
                    <td>
                        <a class="btn-table" href="update_student.php?id=<?= $s['id'] ?>">Edit</a>
                        <a class="btn-table" href="delete_student.php?id=<?= $s['id'] ?>" onclick="return confirm('Delete this student?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div style="text-align:center; margin-top:20px;">
        <a class="btn-link" href="add_student.php">Add New Student</a>
    </div>
</div>
