<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get all questions with their options
$query = "SELECT q.id as question_id, q.question_text, o.id as option_id, o.option_text, o.career_match 
          FROM quiz_questions q 
          LEFT JOIN quiz_options o ON q.id = o.question_id 
          ORDER BY q.id, o.id";
$stmt = $db->prepare($query);
$stmt->execute();
$questions = [];
$current_question = null;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($current_question === null || $current_question['id'] != $row['question_id']) {
        if ($current_question !== null) {
            $questions[] = $current_question;
        }
        $current_question = [
            'id' => $row['question_id'],
            'text' => $row['question_text'],
            'options' => []
        ];
    }
    if ($row['option_id']) {
        $current_question['options'][] = [
            'id' => $row['option_id'],
            'text' => $row['option_text'],
            'career_match' => $row['career_match']
        ];
    }
}
if ($current_question !== null) {
    $questions[] = $current_question;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    $career_scores = [];

    foreach ($answers as $question_id => $option_id) {
        $stmt = $db->prepare("SELECT career_match FROM quiz_options WHERE id = ?");
        $stmt->execute([$option_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $careers = explode(',', $result['career_match']);
            foreach ($careers as $career) {
                $career = trim($career);
                if (!isset($career_scores[$career])) {
                    $career_scores[$career] = 0;
                }
                $career_scores[$career]++;
            }
        }
    }

    // Sort careers by score
    arsort($career_scores);
    
    // Get top 3 careers
    $top_careers = array_slice($career_scores, 0, 3, true);
    
    // Get full career details
    $career_details = [];
    foreach ($top_careers as $career_name => $score) {
        $stmt = $db->prepare("SELECT * FROM careers WHERE title = ?");
        $stmt->execute([$career_name]);
        $career = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($career) {
            $career_details[] = $career;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Quiz - Career Explorer</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .question-card {
            background-color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .question-text {
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }

        .options-list {
            list-style: none;
            padding: 0;
        }

        .option-item {
            margin-bottom: 1rem;
        }

        .option-label {
            display: block;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .option-label:hover {
            background-color: #f8f9fa;
        }

        .option-input {
            margin-right: 1rem;
        }

        .results-container {
            background-color: white;
            padding: 2rem;
            border-radius: var(--border-radius);
            margin-top: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .career-card {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }

        .career-card h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .career-details {
            margin-bottom: 1rem;
        }

        .career-details p {
            margin-bottom: 0.5rem;
        }

        .match-score {
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
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
                <li><a href="quiz.php" class="active">Career Quiz</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <main class="quiz-container">
        <h1>Career Quiz</h1>
        <p>Take this quiz to discover which careers might be the best fit for you based on your preferences and skills.</p>

        <?php if (!isset($_POST['answers'])): ?>
            <form method="POST" action="">
                <?php foreach ($questions as $question): ?>
                    <div class="question-card">
                        <div class="question-text"><?php echo htmlspecialchars($question['text']); ?></div>
                        <ul class="options-list">
                            <?php foreach ($question['options'] as $option): ?>
                                <li class="option-item">
                                    <label class="option-label">
                                        <input type="radio" 
                                               name="answers[<?php echo $question['id']; ?>]" 
                                               value="<?php echo $option['id']; ?>" 
                                               class="option-input" 
                                               required>
                                        <?php echo htmlspecialchars($option['text']); ?>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <button style="background-color: black ; color: #ddd; border-radius: 10px; padding: 10px; cursor: pointer;" type="submit" class="button">Get Results</button>
            </form>
        <?php else: ?>
            <div class="results-container">
                <h2>Your Career Matches</h2>
                <?php if (!empty($career_details)): ?>
                    <?php foreach ($career_details as $career): ?>
                        <div class="career-card">
                            <h3><?php echo htmlspecialchars($career['title']); ?></h3>
                            <div class="career-details">
                                <p><strong>Industry:</strong> <?php echo htmlspecialchars($career['industry']); ?></p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($career['description']); ?></p>
                                <p><strong>Salary Range:</strong> <?php echo htmlspecialchars($career['salary_range']); ?></p>
                                <p><strong>Growth Rate:</strong> <?php echo htmlspecialchars($career['growth_rate']); ?></p>
                                <p><strong>Required Education:</strong> <?php echo htmlspecialchars($career['education_requirements']); ?></p>
                            </div>
                            <a href="career-detail.php?id=<?php echo $career['id']; ?>" class="button">Learn More</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No career matches found. Please try the quiz again.</p>
                <?php endif; ?>
                <a href="quiz.php" class="button">Take Quiz Again</a>
            </div>
        <?php endif; ?>
    </main>

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
        // Quiz form validation and progress tracking
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('careerQuiz');
            const questions = document.querySelectorAll('.quiz-question');
            let currentQuestion = 0;

            // Show first question initially
            questions.forEach((question, index) => {
                question.style.display = index === 0 ? 'block' : 'none';
            });

            // Add navigation between questions
            questions.forEach((question, index) => {
                const options = question.querySelectorAll('input[type="radio"]');
                options.forEach(option => {
                    option.addEventListener('change', () => {
                        setTimeout(() => {
                            if (index < questions.length - 1) {
                                questions[index].style.display = 'none';
                                questions[index + 1].style.display = 'block';
                                currentQuestion = index + 1;
                            }
                        }, 500);
                    });
                });
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                const unanswered = Array.from(questions).some(question => {
                    const options = question.querySelectorAll('input[type="radio"]');
                    return !Array.from(options).some(option => option.checked);
                });

                if (unanswered) {
                    e.preventDefault();
                    alert('Please answer all questions before submitting.');
                }
            });
        });
    </script>
</body>
</html> 