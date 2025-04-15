<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
            case 'edit':
                $title = $_POST['title'];
                $description = $_POST['description'];
                $industry = $_POST['industry'];
                $education_level = $_POST['education_level'];
                $salary_min = (int)$_POST['salary_min'];
                $salary_max = (int)$_POST['salary_max'];
                $skills_required = $_POST['skills_required'];
                $job_outlook = $_POST['job_outlook'];
                $image_url = $_POST['image_url'];

                if ($_POST['action'] === 'add') {
                    $stmt = $db->prepare("
                        INSERT INTO careers (
                            title, description, industry, education_level,
                            salary_min, salary_max, skills_required, job_outlook, image_url
                        ) VALUES (
                            :title, :description, :industry, :education_level,
                            :salary_min, :salary_max, :skills_required, :job_outlook, :image_url
                        )
                    ");
                } else {
                    $id = (int)$_POST['id'];
                    $stmt = $db->prepare("
                        UPDATE careers SET
                            title = :title,
                            description = :description,
                            industry = :industry,
                            education_level = :education_level,
                            salary_min = :salary_min,
                            salary_max = :salary_max,
                            skills_required = :skills_required,
                            job_outlook = :job_outlook,
                            image_url = :image_url
                        WHERE id = :id
                    ");
                    $stmt->bindParam(':id', $id);
                }

                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':industry', $industry);
                $stmt->bindParam(':education_level', $education_level);
                $stmt->bindParam(':salary_min', $salary_min);
                $stmt->bindParam(':salary_max', $salary_max);
                $stmt->bindParam(':skills_required', $skills_required);
                $stmt->bindParam(':job_outlook', $job_outlook);
                $stmt->bindParam(':image_url', $image_url);

                $stmt->execute();
                header("Location: careers.php?success=" . ($_POST['action'] === 'add' ? 'added' : 'updated'));
                exit();
                break;

            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM careers WHERE id = :id");
                $stmt->execute([':id' => $id]);
                header("Location: careers.php?success=deleted");
                exit();
                break;
        }
    }
}

// Get all careers
$careers = $db->query("SELECT * FROM careers ORDER BY created_at DESC")->fetchAll();

// Get unique industries and education levels
$industries = $db->query("SELECT DISTINCT industry FROM careers ORDER BY industry")->fetchAll(PDO::FETCH_COLUMN);
$education_levels = $db->query("SELECT DISTINCT education_level FROM careers ORDER BY education_level")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Careers - Career Explorer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #357abd;
            --background-dark: #1a1a1a;
            --card-dark: #2d2d2d;
            --text-light: #ffffff;
            --text-muted: #b0b0b0;
            --border-radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background-dark);
            color: var(--text-light);
            line-height: 1.6;
        }

        .admin-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }

        /* Sidebar Styles (same as index.php) */
        .sidebar {
            background: var(--card-dark);
            padding: 2rem;
            position: fixed;
            width: 250px;
            height: 100vh;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header {
            margin-bottom: 2rem;
        }

        .sidebar-header h1 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-light);
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(74, 144, 226, 0.2);
            color: var(--primary-color);
        }

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
        }

        /* Main Content Styles */
        .main-content {
            padding: 2rem;
            margin-left: 250px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-header h2 {
            color: var(--text-light);
            font-size: 1.8rem;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .action-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Careers Table */
        .careers-table {
            width: 100%;
            background: var(--card-dark);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .careers-table th,
        .careers-table td {
            padding: 1rem;
            text-align: left;
        }

        .careers-table th {
            background: rgba(0, 0, 0, 0.2);
            color: var(--text-light);
            font-weight: 600;
        }

        .careers-table tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .careers-table tr:last-child {
            border-bottom: none;
        }

        .careers-table td {
            color: var(--text-muted);
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .table-button {
            padding: 0.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
        }

        .edit-button {
            background: rgba(74, 144, 226, 0.2);
            color: var(--primary-color);
        }

        .delete-button {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }

        .table-button:hover {
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: var(--card-dark);
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: var(--border-radius);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .modal-header h3 {
            color: var(--text-light);
            font-size: 1.5rem;
        }

        .close-button {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-button:hover {
            color: var(--text-light);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--border-radius);
            color: var(--text-light);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-textarea {
            min-height: 150px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }

        .cancel-button {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .save-button {
            background: var(--primary-color);
            color: white;
        }

        .cancel-button,
        .save-button {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .cancel-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .save-button:hover {
            background: var(--secondary-color);
        }

        /* Success Message */
        .success-message {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .admin-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .main-content {
                margin-left: 0;
            }

            .careers-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1>Career Explorer</h1>
                <p>Admin Dashboard</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="careers.php" class="active"><i class="fas fa-briefcase"></i> Careers</a></li>
                <li><a href="messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="page-header">
                <h2>Manage Careers</h2>
                <button class="action-button" onclick="openModal('add')">
                    <i class="fas fa-plus"></i>
                    Add New Career
                </button>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    Career <?php echo htmlspecialchars($_GET['success']); ?> successfully!
                </div>
            <?php endif; ?>

            <div class="careers-table-container">
                <table class="careers-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Industry</th>
                            <th>Education Level</th>
                            <th>Salary Range</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($careers as $career): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($career['title']); ?></td>
                                <td><?php echo htmlspecialchars($career['industry']); ?></td>
                                <td><?php echo htmlspecialchars($career['education_level']); ?></td>
                                <td>$<?php echo number_format($career['salary_min']); ?> - $<?php echo number_format($career['salary_max']); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button class="table-button edit-button" onclick="openModal('edit', <?php echo $career['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="table-button delete-button" onclick="deleteCareer(<?php echo $career['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add/Edit Career Modal -->
    <div id="careerModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Career</h3>
                <button class="close-button" onclick="closeModal()">&times;</button>
            </div>
            <form id="careerForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="careerId">

                <div class="form-group">
                    <label for="title">Career Title</label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control form-textarea" required></textarea>
                </div>

                <div class="form-group">
                    <label for="industry">Industry</label>
                    <select id="industry" name="industry" class="form-control" required>
                        <option value="">Select Industry</option>
                        <?php foreach ($industries as $industry): ?>
                            <option value="<?php echo htmlspecialchars($industry); ?>">
                                <?php echo htmlspecialchars($industry); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="education_level">Education Level</label>
                    <select id="education_level" name="education_level" class="form-control" required>
                        <option value="">Select Education Level</option>
                        <?php foreach ($education_levels as $level): ?>
                            <option value="<?php echo htmlspecialchars($level); ?>">
                                <?php echo htmlspecialchars($level); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="salary_min">Minimum Salary</label>
                    <input type="number" id="salary_min" name="salary_min" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="salary_max">Maximum Salary</label>
                    <input type="number" id="salary_max" name="salary_max" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="skills_required">Required Skills (comma-separated)</label>
                    <input type="text" id="skills_required" name="skills_required" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="job_outlook">Job Outlook</label>
                    <textarea id="job_outlook" name="job_outlook" class="form-control form-textarea" required></textarea>
                </div>

                <div class="form-group">
                    <label for="image_url">Image URL</label>
                    <input type="url" id="image_url" name="image_url" class="form-control" required>
                </div>

                <div class="form-actions">
                    <button type="button" class="cancel-button" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="save-button">Save Career</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(action, id = null) {
            const modal = document.getElementById('careerModal');
            const form = document.getElementById('careerForm');
            const title = document.getElementById('modalTitle');
            const actionInput = document.getElementById('formAction');
            const idInput = document.getElementById('careerId');

            if (action === 'add') {
                title.textContent = 'Add New Career';
                actionInput.value = 'add';
                form.reset();
            } else if (action === 'edit' && id) {
                title.textContent = 'Edit Career';
                actionInput.value = 'edit';
                idInput.value = id;
                // Here you would typically fetch the career data and populate the form
                // For now, we'll just show the modal
            }

            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('careerModal').style.display = 'none';
        }

        function deleteCareer(id) {
            if (confirm('Are you sure you want to delete this career?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('careerModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html> 