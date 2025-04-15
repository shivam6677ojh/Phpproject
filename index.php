<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Career Explorer - Find Your Dream Career</title>
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
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            position: relative;
            overflow: hidden;
            margin-top: 80px;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover;
            opacity: 0.1;
            z-index: 0;
        }

        .hero-content {
            text-align: center;
            max-width: 800px;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--primary-color), #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 1s ease;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--text-muted);
            animation: fadeInUp 1s ease 0.2s;
            animation-fill-mode: both;
        }

        .search-container {
            max-width: 600px;
            margin: 0 auto;
            animation: fadeInUp 1s ease 0.4s;
            animation-fill-mode: both;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            background: var(--card-dark);
            padding: 1rem;
            border-radius: var(--border-radius);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
        }

        .search-input {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: var(--border-radius);
            background: #3d3d3d;
            color: var(--text-light);
            font-size: 1rem;
            transition: var(--transition);
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
        }

        .search-button {
            padding: 1rem 2rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .features {
            padding: 5rem 2rem;
            background: var(--background-dark);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: var(--card-dark);
            padding: 2rem;
            border-radius: var(--border-radius);
            text-align: center;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .feature-card p {
            color: var(--text-muted);
        }

        .popular-careers {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%);
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .section-header p {
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        .careers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
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

        .career-content p {
            color: var(--text-muted);
            margin-bottom: 1rem;
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

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scroll-indicator {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
        }

        .scroll-indicator i {
            font-size: 2rem;
            color: var(--text-light);
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-20px);
            }
            60% {
                transform: translateY(-10px);
            }
        }

        .cta-section {
            padding: 5rem 2rem;
            text-align: center;
            background: var(--card-dark);
        }

        .cta-content {
            max-width: 600px;
            margin: 0 auto;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--text-light);
        }

        .cta-content p {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: var(--transition);
        }

        .cta-button:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .cta-button i {
            transition: var(--transition);
        }

        .cta-button:hover i {
            transform: translateX(5px);
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

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .search-form {
                flex-direction: column;
            }

            .search-button {
                width: 100%;
                justify-content: center;
            }

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
                <li><a href="index.php" class="active">Home</a></li>
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

    <section class="hero">
        <div class="hero-content">
            <h1>Discover Your Dream Career</h1>
            <p>Explore thousands of career opportunities and find the perfect path for your future</p>
            <div class="search-container">
                <form class="search-form" action="search.php" method="GET">
                    <input type="text" class="search-input" name="query" placeholder="Search careers, industries, or skills...">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                        Search
                    </button>
                </form>
            </div>
        </div>
        <div class="scroll-indicator">
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <section class="features">
        <div class="features-grid">
            <div class="feature-card" data-aos="fade-up">
                <i class="fas fa-chart-line feature-icon"></i>
                <h3>Career Growth</h3>
                <p>Discover careers with the highest growth potential and best opportunities for advancement.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-graduation-cap feature-icon"></i>
                <h3>Education Paths</h3>
                <p>Find the right educational requirements and training needed for your dream career.</p>
            </div>
            <div class="feature-card" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-dollar-sign feature-icon"></i>
                <h3>Salary Insights</h3>
                <p>Get detailed information about salary ranges and compensation packages.</p>
            </div>
        </div>
    </section>

    <section class="popular-careers">
        <div class="section-header" data-aos="fade-up">
            <h2>Popular Career Paths</h2>
            <p>Explore some of the most in-demand careers in today's job market</p>
        </div>
        <div class="careers-grid">
            <div class="career-card" data-aos="fade-up">
                <div class="career-image" style="background-image: url('https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80')"></div>
                <div class="career-content">
                    <h3>Software Development</h3>
                    <p>Create innovative software solutions and applications for various industries.</p>
                    <a href="career.php?id=1" class="career-link">
                        Learn More
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="career-card" data-aos="fade-up" data-aos-delay="100">
                <div class="career-image" style="background-image: url('https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80')"></div>
                <div class="career-content">
                    <h3>Data Science</h3>
                    <p>Analyze complex data sets to drive business decisions and innovation.</p>
                    <a href="career.php?id=2" class="career-link">
                        Learn More
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="career-card" data-aos="fade-up" data-aos-delay="200">
                <div class="career-image" style="background-image: url('https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80')"></div>
                <div class="career-content">
                    <h3>Digital Marketing</h3>
                    <p>Develop and implement online marketing strategies for businesses.</p>
                    <a href="career.php?id=3" class="career-link">
                        Learn More
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="cta-content" data-aos="fade-up">
            <h2>Ready to Start Your Career Journey?</h2>
            <p>Join thousands of professionals who have found their dream careers through our platform.</p>
            <a href="register.php" class="cta-button">
                Get Started
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </section>

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

        // Smooth scroll for the scroll indicator
        document.querySelector('.scroll-indicator').addEventListener('click', () => {
            window.scrollTo({
                top: document.querySelector('.features').offsetTop,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html> 