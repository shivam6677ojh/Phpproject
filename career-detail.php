<?php
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: explore.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$career_id = $_GET['id'];

// Get career details
$query = "SELECT c.*, GROUP_CONCAT(s.name) as skills 
          FROM careers c 
          LEFT JOIN career_skills cs ON c.id = cs.career_id 
          LEFT JOIN skills s ON cs.skill_id = s.id 
          WHERE c.id = :id
          GROUP BY c.id";

$stmt = $db->prepare($query);
$stmt->bindParam(':id', $career_id);
$stmt->execute();

$career = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$career) {
    header('Location: explore.php');
    exit();
}

// Get related careers
$related_query = "SELECT c.* 
                 FROM careers c 
                 WHERE c.industry = :industry 
                 AND c.id != :id 
                 LIMIT 3";

$related_stmt = $db->prepare($related_query);
$related_stmt->bindParam(':industry', $career['industry']);
$related_stmt->bindParam(':id', $career_id);
$related_stmt->execute();

$related_careers = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($career['title']); ?> - Career Explorer</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <h1>Career Explorer</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="explore.php">Explore Careers</a></li>
                <li><a href="quiz.php">Career Quiz</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Career Detail Section -->
    <section class="career-detail">
        <div class="container">
            <div class="career-header">
                <h1><?php echo htmlspecialchars($career['title']); ?></h1>
                <p class="industry"><?php echo htmlspecialchars($career['industry']); ?></p>
            </div>

            <div class="career-content">
                <div class="career-info">
                    <div class="info-card">
                        <h3>Overview</h3>
                        <p><?php echo htmlspecialchars($career['description']); ?></p>
                    </div>

                    <div class="info-card">
                        <h3>Required Education</h3>
                        <p><?php echo htmlspecialchars($career['education_level']); ?></p>
                    </div>

                    <div class="info-card">
                        <h3>Salary Range</h3>
                        <div class="salary-chart">
                            <canvas id="salaryChart"></canvas>
                        </div>
                        <p class="salary-info">
                            $<?php echo number_format($career['salary_min']); ?> - 
                            $<?php echo number_format($career['salary_max']); ?> per year
                        </p>
                    </div>

                    <div class="info-card">
                        <h3>Job Growth</h3>
                        <div class="growth-chart">
                            <canvas id="growthChart"></canvas>
                        </div>
                        <p class="growth-info">
                            Projected growth: <?php echo $career['growth_rate']; ?>% (Much faster than average)
                        </p>
                    </div>

                    <div class="info-card">
                        <h3>Required Skills</h3>
                        <div class="skills">
                            <?php 
                            $skills = explode(',', $career['skills']);
                            foreach ($skills as $skill): 
                            ?>
                                <span class="skill-badge"><?php echo htmlspecialchars($skill); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <?php if (!empty($related_careers)): ?>
                <div class="related-careers">
                    <h2>Related Careers</h2>
                    <div class="related-grid">
                        <?php foreach ($related_careers as $related): ?>
                            <div class="related-card">
                                <h3><?php echo htmlspecialchars($related['title']); ?></h3>
                                <p class="industry"><?php echo htmlspecialchars($related['industry']); ?></p>
                                <p class="salary">$<?php echo number_format($related['salary_min']); ?> - 
                                   $<?php echo number_format($related['salary_max']); ?></p>
                                <a href="career-detail.php?id=<?php echo $related['id']; ?>" class="view-more">
                                    View Details
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Career Explorer</h3>
                    <p>Helping you find your dream career since 2024</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="explore.php">Explore Careers</a></li>
                        <li><a href="quiz.php">Career Quiz</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Career Explorer. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Salary Chart
        const salaryCtx = document.getElementById('salaryChart').getContext('2d');
        new Chart(salaryCtx, {
            type: 'bar',
            data: {
                labels: ['Entry Level', 'Mid Level', 'Senior Level'],
                datasets: [{
                    label: 'Salary Range',
                    data: [
                        <?php echo $career['salary_min']; ?>,
                        <?php echo ($career['salary_min'] + $career['salary_max']) / 2; ?>,
                        <?php echo $career['salary_max']; ?>
                    ],
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.2)',
                        'rgba(37, 99, 235, 0.4)',
                        'rgba(37, 99, 235, 0.6)'
                    ],
                    borderColor: [
                        'rgba(37, 99, 235, 1)',
                        'rgba(37, 99, 235, 1)',
                        'rgba(37, 99, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'doughnut',
            data: {
                labels: ['Projected Growth', 'Industry Average'],
                datasets: [{
                    data: [<?php echo $career['growth_rate']; ?>, 7],
                    backgroundColor: [
                        'rgba(46, 125, 50, 0.6)',
                        'rgba(158, 158, 158, 0.6)'
                    ],
                    borderColor: [
                        'rgba(46, 125, 50, 1)',
                        'rgba(158, 158, 158, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                cutout: '70%'
            }
        });
    </script>
</body>
</html> 