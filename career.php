<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get career ID from URL
$career_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch career details
$stmt = $db->prepare("SELECT * FROM careers WHERE id = :id");
$stmt->execute([':id' => $career_id]);
$career = $stmt->fetch();

if (!$career) {
    header("Location: explore.php");
    exit();
}

// Fetch related careers
$stmt = $db->prepare("
    SELECT * FROM careers 
    WHERE industry = :industry 
    AND id != :id 
    LIMIT 3
");
$stmt->execute([
    ':industry' => $career['industry'],
    ':id' => $career_id
]);
$related_careers = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($career['title']); ?> - Career Explorer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
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
            padding-top: 80px;
        }

        /* Navigation styles (same as explore.php) */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(26, 26, 26, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin: 0;
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: var(--text-light);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        /* Career Details Styles */
        .career-header {
            background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
            padding: 4rem 2rem;
            text-align: center;
        }

        .career-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .career-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            color: var(--text-muted);
        }

        .career-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .career-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .career-main {
            background: var(--card-dark);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .career-main h2 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .career-main p {
            margin-bottom: 1.5rem;
            color: var(--text-muted);
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .skill-tag {
            background: rgba(74, 144, 226, 0.2);
            color: var(--primary-color);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-size: 0.9rem;
        }

        .career-sidebar {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .salary-card {
            background: var(--card-dark);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .salary-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .salary-range {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .related-careers {
            background: var(--card-dark);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .related-careers h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .related-career {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .related-career:last-child {
            border-bottom: none;
        }

        .related-career img {
            width: 60px;
            height: 60px;
            border-radius: var(--border-radius);
            object-fit: cover;
        }

        .related-career-info h4 {
            color: var(--text-light);
            margin-bottom: 0.25rem;
        }

        .related-career-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .back-button:hover {
            color: var(--secondary-color);
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .career-content {
                grid-template-columns: 1fr;
            }

            .career-header h1 {
                font-size: 2rem;
            }

            .career-meta {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="logo">
                <i class="fas fa-briefcase"></i>
                <h1>Career Explorer</h1>
            </div>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="explore.php">Explore Careers</a></li>
                <li><a href="quiz.php">Career Quiz</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
    </nav>

    <div class="career-header">
        <a href="explore.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Careers
        </a>
        <h1><?php echo htmlspecialchars($career['title']); ?></h1>
        <div class="career-meta">
            <span><i class="fas fa-industry"></i> <?php echo htmlspecialchars($career['industry']); ?></span>
            <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($career['education_level']); ?></span>
            <span><i class="fas fa-dollar-sign"></i> $<?php echo number_format($career['salary_min']); ?> - $<?php echo number_format($career['salary_max']); ?></span>
        </div>
    </div>

    <div class="career-content">
        <div class="career-main">
            <h2>Career Overview</h2>
            <p><?php echo nl2br(htmlspecialchars($career['description'])); ?></p>

            <h2>Required Skills</h2>
            <div class="skills-list">
                <?php
                $skills = explode(',', $career['skills_required']);
                foreach ($skills as $skill):
                ?>
                    <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                <?php endforeach; ?>
            </div>

            <h2>Job Outlook</h2>
            <p><?php echo nl2br(htmlspecialchars($career['job_outlook'])); ?></p>
        </div>

        <div class="career-sidebar">
            <div class="salary-card">
                <h3>Salary Information</h3>
                <div class="salary-range">
                    $<?php echo number_format($career['salary_min']); ?> - $<?php echo number_format($career['salary_max']); ?>
                </div>
                <p>Average salary range for this position</p>
            </div>

            <?php if (!empty($related_careers)): ?>
                <div class="related-careers">
                    <h3>Related Careers</h3>
                    <?php foreach ($related_careers as $related): ?>
                        <a href="career.php?id=<?php echo $related['id']; ?>" class="related-career">
                            <img src="<?php echo htmlspecialchars($related['image_url']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                            <div class="related-career-info">
                                <h4><?php echo htmlspecialchars($related['title']); ?></h4>
                                <p><?php echo htmlspecialchars($related['education_level']); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html> 