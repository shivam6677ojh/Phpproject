<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";

try {
    // Connect to MySQL without selecting a database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Drop existing database if it exists
    $pdo->exec("DROP DATABASE IF EXISTS career_explorer");

    // Create new database
    $pdo->exec("CREATE DATABASE career_explorer");
    $pdo->exec("USE career_explorer");

    // Create careers table
    $pdo->exec("CREATE TABLE careers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        industry VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        salary_range VARCHAR(100) NOT NULL,
        growth_rate VARCHAR(50) NOT NULL,
        required_skills TEXT NOT NULL,
        education_requirements TEXT NOT NULL,
        job_outlook TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Create contact_messages table
    $pdo->exec("CREATE TABLE contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create admin_users table
    $pdo->exec("CREATE TABLE admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create quiz_questions table
    $pdo->exec("CREATE TABLE quiz_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_text TEXT NOT NULL,
        question_type VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create quiz_options table
    $pdo->exec("CREATE TABLE quiz_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        option_text TEXT NOT NULL,
        career_match TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
    )");

    // Create skills table
    $pdo->exec("CREATE TABLE skills (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE
    )");

    // Create career_skills table
    $pdo->exec("CREATE TABLE career_skills (
        career_id INT NOT NULL,
        skill_id INT NOT NULL,
        PRIMARY KEY (career_id, skill_id),
        FOREIGN KEY (career_id) REFERENCES careers(id) ON DELETE CASCADE,
        FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE
    )");

    // Insert default admin user (password: admin123)
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
    $stmt->execute(['admin', $password_hash]);

    // Insert sample careers data
    $careers = [
        ['Software Developer', 'Technology', 'Design and develop software applications and systems', '60,000 - 120,000', '22%', 'Programming, Problem Solving, Communication, Data Analysis, Teamwork, Critical Thinking, Project Management, Technical Writing', 'Bachelor\'s Degree', 'Positive'],
        ['Data Scientist', 'Technology', 'Analyze complex data sets to help organizations make better decisions', '80,000 - 150,000', '31%', 'Programming, Problem Solving, Communication, Data Analysis, Teamwork, Critical Thinking, Project Management, Technical Writing', 'Master\'s Degree', 'Positive'],
        ['Registered Nurse', 'Healthcare', 'Provide patient care and support in various healthcare settings', '50,000 - 90,000', '15%', 'Communication, Teamwork, Critical Thinking, Project Management, Technical Writing', 'Bachelor\'s Degree', 'Positive']
    ];

    $stmt = $pdo->prepare("INSERT INTO careers (title, industry, description, salary_range, growth_rate, required_skills, education_requirements, job_outlook) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($careers as $career) {
        $stmt->execute($career);
    }

    // Insert sample quiz questions
    $questions = [
        ['What type of work environment do you prefer?', 'multiple_choice'],
        ['Which of these activities interests you the most?', 'multiple_choice'],
        ['How do you prefer to solve problems?', 'multiple_choice'],
        ['What is your preferred way of learning?', 'multiple_choice'],
        ['Which of these skills do you enjoy using the most?', 'multiple_choice']
    ];

    $stmt = $pdo->prepare("INSERT INTO quiz_questions (question_text, question_type) VALUES (?, ?)");
    foreach ($questions as $question) {
        $stmt->execute($question);
    }

    // Insert sample quiz options
    $options = [
        [1, 'Working independently in a quiet environment', 'Software Developer, Data Scientist'],
        [1, 'Working in a team with regular interaction', 'Registered Nurse, Project Manager'],
        [1, 'Working in a fast-paced, dynamic environment', 'Entrepreneur, Sales Manager'],
        [2, 'Analyzing data and finding patterns', 'Data Scientist, Business Analyst'],
        [2, 'Creating and building things', 'Software Developer, Engineer'],
        [2, 'Helping and caring for others', 'Registered Nurse, Social Worker'],
        [3, 'Using logical and analytical thinking', 'Software Developer, Data Scientist'],
        [3, 'Using creativity and innovation', 'Graphic Designer, Marketing Manager'],
        [3, 'Using empathy and understanding', 'Registered Nurse, Counselor'],
        [4, 'Learning through hands-on experience', 'Software Developer, Engineer'],
        [4, 'Learning through research and study', 'Data Scientist, Researcher'],
        [4, 'Learning through interaction with others', 'Registered Nurse, Teacher'],
        [5, 'Technical and analytical skills', 'Software Developer, Data Scientist'],
        [5, 'Communication and interpersonal skills', 'Registered Nurse, Sales Manager'],
        [5, 'Creative and artistic skills', 'Graphic Designer, Content Creator']
    ];

    $stmt = $pdo->prepare("INSERT INTO quiz_options (question_id, option_text, career_match) VALUES (?, ?, ?)");
    foreach ($options as $option) {
        $stmt->execute($option);
    }

    // Insert sample skills
    $skills = ['Programming', 'Problem Solving', 'Communication', 'Data Analysis', 'Teamwork', 'Critical Thinking', 'Project Management', 'Technical Writing'];
    $stmt = $pdo->prepare("INSERT INTO skills (name) VALUES (?)");
    foreach ($skills as $skill) {
        $stmt->execute([$skill]);
    }

    echo "Database and all tables created successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 