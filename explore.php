<?php
require_once 'config/database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$industry = isset($_GET['industry']) ? $_GET['industry'] : '';
$education = isset($_GET['education']) ? $_GET['education'] : '';
$salary_range = isset($_GET['salary_range']) ? $_GET['salary_range'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6;

// Build the query
$query = "SELECT * FROM careers WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($industry)) {
    $query .= " AND industry = :industry";
    $params[':industry'] = $industry;
}

if (!empty($education)) {
    $query .= " AND education_level = :education";
    $params[':education'] = $education;
}

if (!empty($salary_range)) {
    switch ($salary_range) {
        case 'low':
            $query .= " AND salary_min <= 50000";
            break;
        case 'medium':
            $query .= " AND salary_min > 50000 AND salary_min <= 80000";
            break;
        case 'high':
            $query .= " AND salary_min > 80000";
            break;
    }
}

// Get total count for pagination
$count_query = str_replace("SELECT *", "SELECT COUNT(*)", $query);
$stmt = $db->prepare($count_query);
$stmt->execute($params);
$total_careers = $stmt->fetchColumn();

// Calculate pagination
$total_pages = ceil($total_careers / $per_page);
$offset = ($page - 1) * $per_page;

// Add pagination to query
$query .= " LIMIT :offset, :per_page";

// Get careers
$stmt = $db->prepare($query);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$careers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get unique values for filters
$industries = $db->query("SELECT DISTINCT industry FROM careers ORDER BY industry")->fetchAll(PDO::FETCH_COLUMN);
$education_levels = $db->query("SELECT DISTINCT education_level FROM careers ORDER BY education_level")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Careers - Career Explorer</title>
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

        /* Header Styles */
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
            position: relative;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-color);
            transition: var(--transition);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a.active {
            color: var(--primary-color);
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 2px;
            background: var(--text-light);
            transition: var(--transition);
        }

        /* Explorer Page Styles */
        .explorer-header {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
        }

        .explorer-header h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .explorer-header p {
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .filters {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--card-dark);
            border-radius: var(--border-radius);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .filter-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-item {
            position: relative;
        }

        .filter-item select {
            width: 100%;
            padding: 0.75rem;
            background: #3d3d3d;
            border: none;
            border-radius: var(--border-radius);
            color: var(--text-light);
            font-size: 1rem;
            cursor: pointer;
            appearance: none;
            padding-right: 2.5rem;
        }

        .filter-item::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: #3d3d3d;
            border: none;
            border-radius: var(--border-radius);
            color: var(--text-light);
            font-size: 1rem;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .careers-grid {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .career-card {
            background: var(--card-dark);
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .career-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .career-image {
            height: 200px;
            background-size: cover;
            background-position: center;
        }

        .career-content {
            padding: 1.5rem;
        }

        .career-content h3 {
            margin-bottom: 0.5rem;
            color: var(--text-light);
        }

        .career-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .career-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .career-content p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .career-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .career-link:hover {
            color: var(--secondary-color);
            transform: translateX(5px);
        }

        .career-link i {
            transition: var(--transition);
        }

        .career-link:hover i {
            transform: translateX(5px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            background: var(--card-dark);
            color: var(--text-light);
            text-decoration: none;
            border-radius: var(--border-radius);
            transition: var(--transition);
        }

        .pagination a:hover,
        .pagination a.active {
            background: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--background-dark);
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            .nav-links.active {
                display: flex;
            }

            .hamburger {
                display: flex;
            }

            .hamburger.active span:nth-child(1) {
                transform: rotate(45deg) translate(5px, 5px);
            }

            .hamburger.active span:nth-child(2) {
                opacity: 0;
            }

            .hamburger.active span:nth-child(3) {
                transform: rotate(-45deg) translate(5px, -5px);
            }

            .explorer-header h1 {
                font-size: 2.5rem;
            }

            .filter-group {
                grid-template-columns: 1fr;
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
                <li><a href="explore.php" class="active">Explore Careers</a></li>
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

    <header class="explorer-header">
        <h1>Explore Career Paths</h1>
        <p>Discover and compare different career options based on your interests and skills</p>
    </header>

    <form method="GET" class="filters">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" name="search" placeholder="Search careers..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="filter-group">
            <div class="filter-item">
                <select name="industry">
                    <option value="">Industry</option>
                    <?php foreach ($industries as $ind): ?>
                        <option value="<?php echo htmlspecialchars($ind); ?>" <?php echo $industry === $ind ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ind); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <select name="education">
                    <option value="">Education Level</option>
                    <?php foreach ($education_levels as $level): ?>
                        <option value="<?php echo htmlspecialchars($level); ?>" <?php echo $education === $level ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($level); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <select name="salary_range">
                    <option value="">Salary Range</option>
                    <option value="low" <?php echo $salary_range === 'low' ? 'selected' : ''; ?>>$30,000 - $50,000</option>
                    <option value="medium" <?php echo $salary_range === 'medium' ? 'selected' : ''; ?>>$50,000 - $80,000</option>
                    <option value="high" <?php echo $salary_range === 'high' ? 'selected' : ''; ?>>$80,000+</option>
                </select>
            </div>
        </div>
        <button type="submit" class="filter-button">Apply Filters</button>
        <a href="explore.php" class="filter-button reset-button">Reset</a>
    </form>

    <div class="careers-grid">
        <?php if (empty($careers)): ?>
            <div class="no-results">
                <h3>No careers found matching your criteria</h3>
                <p>Try adjusting your filters or <a href="explore.php">reset</a> to see all careers.</p>
            </div>
        <?php else: ?>
            <?php foreach ($careers as $career): ?>
                <div class="career-card" data-aos="fade-up">
                    <div class="career-image" style="background-image: url('<?php echo htmlspecialchars($career['image_url']); ?>')"></div>
                    <div class="career-content">
                        <h3><?php echo htmlspecialchars($career['title']); ?></h3>
                        <div class="career-meta">
                            <span><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($career['education_level']); ?></span>
                            <span><i class="fas fa-dollar-sign"></i> $<?php echo number_format($career['salary_min']); ?>+</span>
                        </div>
                        <p><?php echo htmlspecialchars(substr($career['description'], 0, 150)) . '...'; ?></p>
                        <a href="career.php?id=<?php echo $career['id']; ?>" class="career-link">
                            Learn More
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>&education=<?php echo urlencode($education); ?>&salary_range=<?php echo urlencode($salary_range); ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>&education=<?php echo urlencode($education); ?>&salary_range=<?php echo urlencode($salary_range); ?>"
                   class="<?php echo $i === $page ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&industry=<?php echo urlencode($industry); ?>&education=<?php echo urlencode($education); ?>&salary_range=<?php echo urlencode($salary_range); ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Mobile menu toggle
        const hamburger = document.querySelector('.hamburger');
        const navLinks = document.querySelector('.nav-links');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navLinks.classList.toggle('active');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navLinks.contains(e.target)) {
                hamburger.classList.remove('active');
                navLinks.classList.remove('active');
            }
        });

        // Auto-submit form when filters change
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => {
                select.form.submit();
            });
        });
    </script>
</body>

</html>