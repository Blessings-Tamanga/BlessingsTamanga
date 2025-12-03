<?php
include_once 'config/database.php';
include_once 'models/PersonalInfo.php';
include_once 'models/Education.php';
include_once 'models/Skill.php';
include_once 'models/Hobby.php';
include_once 'models/Dashboard.php';
include_once 'models/Message.php';
include_once 'helpers/EmailService.php';
// Add these includes at the top
include_once 'models/Project.php';
include_once 'models/Business.php';



$database = new Database();
$db = $database->getConnection();

// Track visitor
$dashboard = new Dashboard($db);
$session_id = session_id();
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$dashboard->trackVisitor($session_id, $ip_address, $user_agent);



// Handle contact form submission
$contact_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $messageModel = new Message($db);
    $emailService = new EmailService([
        'from_email' => 'blessings@yourdomain.com',
        'from_name' => 'Blessings E. Tamanga'
    ]);
    
    $message_data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'subject' => $_POST['subject'],
        'message' => $_POST['message'],
        'ip_address' => $ip_address,
        'user_agent' => $user_agent
    ];
    
    if ($messageModel->create($message_data)) {
        // Send auto-reply
        $emailService->sendAutoReply($_POST['email'], $_POST['name']);
        $contact_message = "Thank you for your message! I'll get back to you soon. An auto-reply has been sent to your email.";
    } else {
        $contact_message = "Sorry, there was an error sending your message. Please try again.";
    }
}

// ... rest of your existing index.php code ...


$database = new Database();
$db = $database->getConnection();

// Get data from database
$personalInfo = new PersonalInfo($db);
$info = $personalInfo->getInfo();

$education = new Education($db);
$educations = $education->getAll();

$skills = new Skill($db);
$allSkills = $skills->getAll();

$hobbies = new Hobby($db);
$allHobbies = $hobbies->getAll();


// Initialize models
$projectModel = new Project($db);
$businessModel = new Business($db);

// Get ALL projects data
$allProjects = $projectModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

// Organize projects by status
$featuredProjects = array_filter($allProjects, function($project) {
    return $project['featured'] == 1;
});

$completedProjects = array_filter($allProjects, function($project) {
    return $project['status'] === 'completed';
});

$inProgressProjects = array_filter($allProjects, function($project) {
    return $project['status'] === 'in_progress';
});

$plannedProjects = array_filter($allProjects, function($project) {
    return $project['status'] === 'planned';
});

$onHoldProjects = array_filter($allProjects, function($project) {
    return $project['status'] === 'on_hold';
});

// Get business data
$allBusinesses = $businessModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
$featuredBusinesses = array_filter($allBusinesses, function($business) {
    return $business['featured'] == 1;
});
$activeBusinesses = array_filter($allBusinesses, function($business) {
    return $business['status'] === 'active';
});

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    $statusMap = [
        'completed' => 'completed',
        'in_progress' => 'in-progress',
        'planned' => 'planned',
        'on_hold' => 'on-hold',
        'active' => 'active',
        'inactive' => 'inactive'
    ];
    return $statusMap[$status] ?? 'planned';
}

// Handle contact form submission
$contact_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    // In a real application, you would send an email or save to database
    $contact_message = "Thank you for your message! I'll get back to you soon.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($info['full_name'] ?? 'My Portfolio'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
            /* Projects & Businesses Sections */
    .section-header {
        margin-bottom: 3rem;
    }

    .section-subtitle {
        color: #6c757d;
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Tabs */
    .projects-tabs {
        margin-top: 2rem;
    }

    .tab-nav {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-bottom: 3rem;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 12px 24px;
        border: 2px solid #e9ecef;
        background: white;
        color: #6c757d;
        border-radius: 50px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .tab-btn.active,
    .tab-btn:hover {
        background: #4a6fa5;
        color: white;
        border-color: #4a6fa5;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
        animation: fadeInUp 0.5s ease;
    }

    /* Grid Layout */
    .projects-grid,
    .businesses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    /* Cards */
    .project-card,
    .business-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .project-card:hover,
    .business-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }

    /* Images */
    .project-image,
    .business-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .project-image img,
    .business-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .project-card:hover .project-image img,
    .business-card:hover .business-image img {
        transform: scale(1.05);
    }

    .project-placeholder,
    .business-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #4a6fa5, #166088);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }

    /* Status Badges */
    .project-status,
    .business-status {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .project-status.completed,
    .business-status.active {
        background: #28a745;
    }

    .project-status.in-progress,
    .business-status.planned {
        background: #ffc107;
        color: #000;
    }

    .project-status.planned,
    .business-status.inactive {
        background: #6c757d;
    }

    .project-status.on-hold {
        background: #dc3545;
    }

    .featured-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #ffd700;
        color: #000;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    /* Content */
    .project-content,
    .business-content {
        padding: 1.5rem;
    }

    .project-title,
    .business-name {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .project-category,
    .business-category {
        color: #4a6fa5;
        font-weight: 600;
        margin: 0 0 1rem 0;
        font-size: 0.9rem;
    }

    .project-description,
    .business-description {
        color: #6c757d;
        line-height: 1.6;
        margin: 0 0 1.5rem 0;
    }

    /* Technologies */
    .project-technologies {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .tech-tag {
        background: #e9ecef;
        color: #495057;
        padding: 4px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Links & Buttons */
    .project-links,
    .business-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-link {
        color: #4a6fa5;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: color 0.3s ease;
    }

    .btn-link:hover {
        color: #166088;
    }

    .btn-view-details,
    .btn-learn-more {
        background: #4a6fa5;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: 600;
        transition: background 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-view-details:hover,
    .btn-learn-more:hover {
        background: #166088;
        color: white;
    }

    .btn-visit-website {
        background: transparent;
        color: #4a6fa5;
        border: 2px solid #4a6fa5;
        padding: 8px 18px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-visit-website:hover {
        background: #4a6fa5;
        color: white;
    }

    /* Empty States */
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .empty-state i {
        color: #dee2e6;
    }

    /* Modal Styles */
    .modal-loading {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    .modal-loading i {
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .projects-grid,
        .businesses-grid {
            grid-template-columns: 1fr;
        }
        
        .tab-nav {
            flex-direction: column;
            align-items: center;
        }
        
        .tab-btn {
            width: 200px;
        }
    }

    /* Debug styles */
    .project-card {
        border: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .project-card:hover {
        border-color: #4a6fa5;
    }

    .tab-content {
        min-height: 300px;
    }

    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        opacity: 0.5;
        margin-bottom: 1rem;
    }


    /* Dark Theme Variables - Minimal Additions */
    :root {
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --text-primary: #2c3e50;
    --text-secondary: #6c757d;
    --accent-color: #4a6fa5;
    --card-bg: #ffffff;
    }

    [data-theme="dark"] {
    /* Dark theme colors that match your reference */
    --bg-primary: #1A1A2E;
    --bg-secondary: #0F0F1B;
    --text-primary: #6C63FF;
    --text-secondary: #b0b0b0;
    --accent-color: #6C63FF;
    --card-bg: rgba(255, 255, 255, 0.1);
    }

    /* Apply ONLY to body and main containers */
    body {
    background-color: var(--bg-primary);
    color: var(--text-primary);
    transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Only target specific sections for background changes */
    #projects {
    background: var(--bg-secondary) !important;
    }

    #businesses {
    background: var(--bg-primary) !important;
    }

    #skills{
background: var(--bg-primary) }

#hobbies{
background: var(--bg-primary)
}

    /* Only update cards */
    .project-card,
    .business-card,
    .skill-item,
    .timeline-content,
    .contact-form {
    background: var(--card-bg) !important;
    }

    /* Only update text colors for main content */
    .project-title,
    .business-name,
    .section-title,
    .about-text h3,
    .contact-info h3 {
    color: var(--text-primary) !important;
    }

    .project-description,
    .business-description,
    .section-subtitle,
    .about-text p,
    .contact-info p {
    color: var(--text-secondary) !important;
    }

    /* Update buttons to use accent color */
    .btn,
    .btn-view-details,
    .btn-learn-more {
    background: var(--accent-color) !important;
    }

    .btn:hover,
    .btn-view-details:hover,
    .btn-learn-more:hover {
    background: #4A44B5 !important; /* Darker shade of your accent */
    }

    /* Theme toggler styles - keep your existing ones */
    .theme-toggler {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 15px;
    padding: 10px;
    border-radius: 8px;
    background: var(--bg-secondary);
    }

    .theme-toggle-btn {
    background: var(--accent-color);
    border: none;
    width: 60px;
    height: 30px;
    border-radius: 30px;
    position: relative;
    cursor: pointer;
    transition: background 0.3s ease;
    }

    .theme-toggle-btn::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 24px;
    height: 24px;
    background: white;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    [data-theme="dark"] .theme-toggle-btn::after {
    transform: translateX(30px);
    }

    .theme-label {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary);
    }


    </style>
</head>
<body>
    <?php if ($contact_message): ?>
    <div class="notification">
        <?php echo $contact_message; ?>
    </div>
    <script>
        setTimeout(() => {
            document.querySelector('.notification').style.display = 'none';
        }, 5000);

   
    </script>
    
    <?php endif; ?>

    <!-- Header & Navigation -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo"><?php echo htmlspecialchars($info['full_name'] ?? 'Portfolio'); ?></a>
                <ul class="nav-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#education">Education</a></li>
                    <li><a href="#skills">Skills</a></li>
                    <li><a href="#hobbies">Hobbies</a></li>
                    <li><a href="#projects">projects</a></li>
                    <li><a href="#businesses">Business</a></li>
                    <li><a href="#contact">Contact</a></li>

                </ul>
                
                <div class="menu-toggle">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="container">
            <h1>Hello, I'm <?php echo htmlspecialchars($info['full_name'] ?? 'Welcome'); ?></h1>
            <p><?php echo htmlspecialchars($info['title'] ?? 'Software Engineer & Web Developer'); ?></p>
            <div class="hero-btns">
                <a href="#about" class="btn">Learn More</a>
                <a href="#contact" class="btn btn-outline">Get In Touch</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
<section id="about" class="about">
    <div class="container">
        <h2 class="section-title">About Me</h2>
        <div class="about-content">
            <div class="about-img">
                <?php if (!empty($info['profile_image'])): ?>
                    <!-- Show uploaded profile image -->
                    <img src="<?php echo $info['profile_image']; ?>" 
                         alt="Profile Image of <?php echo htmlspecialchars($info['full_name']); ?>">
                <?php else: ?>
                    <!-- Show default image if no profile image -->
                    <img src="assets/media/DSC_5275 (2).jpg" alt="Profile Image">
                <?php endif; ?>
            </div>
            <div class="about-text">
                <h3>Hi there! I'm <?php echo explode(' ', $info['full_name'] ?? 'User')[0]; ?></h3>
                <p><?php echo nl2br(htmlspecialchars($info['bio'] ?? 'Bio information goes here.')); ?></p>
                <a href="#contact" class="btn">Contact Me</a>
            </div>
        </div>
    </div>
</section>

    <!-- Education Section -->
<section id="education">
    <div class="container">
        <h2 class="section-title">Education & Background</h2>
        <div class="education-timeline">
            <?php foreach($educations as $edu): ?>
            <div class="timeline-item">
                <div class="timeline-content">
                    <!-- Education Image -->
                    <?php if (!empty($edu['image_path'])): ?>
                    <div style="text-align: center; margin-bottom: 15px;">
                        <img src="<?php echo $edu['image_path']; ?>" 
                             alt="<?php echo htmlspecialchars($edu['degree_title']); ?>"
                             style="max-width: 200px; max-height: 150px; border-radius: 8px; border: 2px solid #4a6fa5;">
                    </div>
                    <?php endif; ?>
                    
                    <h3><?php echo htmlspecialchars($edu['degree_title']); ?></h3>
                    <h4>
                        <?php if (!empty($edu['institution_link'])): ?>
                            <a href="<?php echo htmlspecialchars($edu['institution_link']); ?>" target="_blank" style="color: #4a6fa5;">
                                <?php echo htmlspecialchars($edu['institution']); ?>
                            </a>
                        <?php else: ?>
                            <?php echo htmlspecialchars($edu['institution']); ?>
                        <?php endif; ?>
                    </h4>
                    <p><?php echo $edu['year_start']; ?> - <?php echo $edu['year_end'] ?: 'Present'; ?></p>
                    <p><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                    
                    <!-- Certificate Link -->
                    <?php if (!empty($edu['certificate_link'])): ?>
                    <div style="margin-top: 15px;">
                        <a href="<?php echo htmlspecialchars($edu['certificate_link']); ?>" 
                           target="_blank" 
                           class="btn btn-outline-primary" 
                           style="padding: 8px 15px; font-size: 0.9rem;">
                            <i class="fas fa-external-link-alt"></i> View Certificate
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


    <!-- Skills Section -->
    <section id="skills">
        <div class="container">
            <h2 class="section-title">My Skills</h2>
            <div class="skills-grid">
                <?php foreach($allSkills as $skill): ?>
                <div class="skill-item">
                    <div class="circle" data-progress="<?php echo $skill['level']; ?>">
                        <span><?php echo $skill['level']; ?>%</span>
                    </div>
                    <h3><i class="<?php echo $skill['icon'] ?? 'fas fa-code'; ?>"></i> <?php echo htmlspecialchars($skill['skill_name']); ?></h3>
                    <p><?php echo htmlspecialchars($skill['description']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Hobbies Section -->
    <section id="hobbies">
        <div class="container">
            <h2 class="section-title">My Hobbies & Interests</h2>
            <div class="hobbies-container">
                <?php foreach($allHobbies as $hobby): ?>
                <div class="hobby-card" style="background-image: url('<?php echo htmlspecialchars($hobby['image_path'] ?? 'https://images.unsplash.com/photo-1551632811-561732d1e306?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80'); ?>')">
                    <div class="overlay">
                        <h3><?php echo htmlspecialchars($hobby['hobby_name']); ?></h3>
                        <p><?php echo htmlspecialchars($hobby['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

 <!-- Projects Section -->
<section id="projects" style="padding: 100px 0; background: #f8f9fa;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">My Projects</h2>
            <p class="section-subtitle">A showcase of my work and ongoing projects</p>
        </div>

        <!-- Project Status Tabs -->
        <div class="projects-tabs">
            <div class="tab-nav">
                <button class="tab-btn active" data-tab="featured">Featured</button>
                <button class="tab-btn" data-tab="completed">Completed</button>
                <button class="tab-btn" data-tab="in-progress">In Progress</button>
                <button class="tab-btn" data-tab="planned">Planned</button>
                <button class="tab-btn" data-tab="on-hold">On Hold</button>
            </div>

            <!-- Featured Projects -->
            <div class="tab-content active" id="featured-tab">
                <?php if (!empty($featuredProjects)): ?>
                    <div class="projects-grid">
                        <?php foreach($featuredProjects as $project): ?>
                            <?php include 'partials/project-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-star fa-3x mb-3"></i>
                        <h3>No Featured Projects</h3>
                        <p>Featured projects will appear here</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Completed Projects -->
            <div class="tab-content" id="completed-tab">
                <?php if (!empty($completedProjects)): ?>
                    <div class="projects-grid">
                        <?php foreach($completedProjects as $project): ?>
                            <?php include 'partials/project-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <h3>No Completed Projects</h3>
                        <p>Completed projects will appear here</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- In Progress Projects -->
            <div class="tab-content" id="in-progress-tab">
                <?php if (!empty($inProgressProjects)): ?>
                    <div class="projects-grid">
                        <?php foreach($inProgressProjects as $project): ?>
                            <?php include 'partials/project-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-spinner fa-3x mb-3"></i>
                        <h3>No Projects in Progress</h3>
                        <p>Projects currently being worked on will appear here</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Planned Projects -->
            <div class="tab-content" id="planned-tab">
                <?php if (!empty($plannedProjects)): ?>
                    <div class="projects-grid">
                        <?php foreach($plannedProjects as $project): ?>
                            <?php include 'partials/project-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-lightbulb fa-3x mb-3"></i>
                        <h3>No Planned Projects</h3>
                        <p>Upcoming projects will appear here</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- On Hold Projects -->
            <div class="tab-content" id="on-hold-tab">
                <?php if (!empty($onHoldProjects)): ?>
                    <div class="projects-grid">
                        <?php foreach($onHoldProjects as $project): ?>
                            <?php include 'partials/project-card.php'; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-pause-circle fa-3x mb-3"></i>
                        <h3>No Projects on Hold</h3>
                        <p>Projects that are temporarily paused will appear here</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Businesses Section -->
<section id="businesses" style="padding: 100px 0; background: white;">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">My Businesses</h2>
            <p class="section-subtitle">Ventures and entrepreneurial projects I'm involved in</p>
        </div>

        <div class="businesses-grid">
            <?php if (!empty($allBusinesses)): ?>
                <?php foreach($allBusinesses as $business): ?>
                    <div class="business-card" data-business-id="<?php echo $business['id']; ?>">
                        <div class="business-image">
                            <?php if (!empty($business['logo_url'])): ?>
                                <img src="<?php echo $business['logo_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($business['name']); ?>">
                            <?php else: ?>
                                <div class="business-placeholder">
                                    <i class="fas fa-building"></i>
                                </div>
                            <?php endif; ?>
                            <div class="business-status <?php echo getStatusBadgeClass($business['status']); ?>">
                                <?php echo ucfirst($business['status']); ?>
                            </div>
                            <?php if ($business['featured']): ?>
                                <div class="featured-badge">
                                    <i class="fas fa-star"></i> Featured
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="business-content">
                            <h3 class="business-name"><?php echo htmlspecialchars($business['name']); ?></h3>
                            <p class="business-category"><?php echo htmlspecialchars($business['category']); ?></p>
                            <p class="business-description"><?php echo htmlspecialchars($business['short_description']); ?></p>
                            
                            <div class="business-actions">
                                <button class="btn-learn-more" onclick="openBusinessModal(<?php echo $business['id']; ?>)">
                                    Learn More
                                </button>
                                <?php if (!empty($business['website_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($business['website_url']); ?>" 
                                       target="_blank" class="btn-visit-website">
                                        <i class="fas fa-external-link-alt"></i> Visit Website
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-store-alt fa-3x mb-3"></i>
                    <h3>No Businesses Listed</h3>
                    <p>Business ventures will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Business Detail Modal -->
<div class="modal" id="businessModal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title" id="businessModalTitle">Business Details</h3>
            <span class="close" onclick="hideModal('businessModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div id="businessModalContent">
                <div class="modal-loading">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Loading business details...</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="hideModal('businessModal')">Close</button>
            <a href="#" id="businessLearnMoreBtn" class="btn btn-primary" target="_blank" style="display: none;">
                <i class="fas fa-external-link-alt"></i> Learn More
            </a>
        </div>
    </div>
</div>
</section>


    <!-- Contact Section -->
    <section id="contact">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-container">
                <div class="contact-info">
                    <h3>Let's Connect</h3>
                    <p>I'm always open to discussing new opportunities, creative projects, or just having a friendly chat about technology and design.</p>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p><?php echo htmlspecialchars($info['email'] ?? 'email@example.com'); ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Phone</h4>
                            <p><?php echo htmlspecialchars($info['phone'] ?? '+1234567890'); ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Location</h4>
                            <p><?php echo htmlspecialchars($info['city'] ?? 'Your City'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="contact-form">
                    <h3>Send Me a Message</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="Enter subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Your Message</label>
                            <textarea id="message" name="message" class="form-control" placeholder="Enter your message" required></textarea>
                        </div>
                        <button type="submit" name="contact_submit" class="btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Portfolio</h3>
                    <p>A showcase of my skills, education, and interests. Thanks for visiting!</p>
                    <!-- Theme Toggler -->
    <div class="theme-toggler">
        <span class="theme-label">
            <i class="fas fa-sun"></i> 
            Theme 
            <i class="fas fa-moon"></i>
        </span>
        <button class="theme-toggle-btn" id="themeToggle" 
                aria-label="Toggle dark mode">
        </button>
    </div>
                </div>
                
                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#education">Education</a></li>
                        <li><a href="#skills">Skills</a></li>
                        <li><a href="#hobbies">Hobbies</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Connect With Me</h3>
                    <div class="social-links">
                        <a href="https:/wa.me/+265887408082" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://github.com/Blessings-Tamanga" target="_blank"><i class="fab fa-github"></i></a>
                        <a href="https://www.facebook.com/blessings.tamanga.33" target="_blank"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/btamanga?igsh=MTJzdjl1dmJhYTtag==" target="_blank"><i class="fab fa-instagram"></i></a>
                        
                    </div>
                </div>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 <?php echo htmlspecialchars($info['full_name'] ?? 'My Portfolio'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Your existing JavaScript for mobile menu, animations, etc.
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');

        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
            });
        });

        // Animate skill circles when they come into view
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const circles = entry.target.querySelectorAll('.circle');
                    circles.forEach(circle => {
                        const progress = circle.getAttribute('data-progress');
                        circle.style.background = `conic-gradient(#4a6fa5 ${progress * 3.6}deg, #e9ecef 0deg)`;
                    });
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('#skills').forEach(section => {
            observer.observe(section);
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if(targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

             // Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    // Project tabs
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            console.log('Switching to tab:', tabId);
            
            // Remove active class from all buttons and contents
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            const targetTab = document.getElementById(tabId + '-tab');
            if (targetTab) {
                targetTab.classList.add('active');
            } else {
                console.error('Tab content not found:', tabId + '-tab');
            }
        });
    });

    // Debug: Log available tabs
    console.log('Available tabs:');
    tabContents.forEach(tab => {
        console.log('-', tab.id, 'has', tab.querySelectorAll('.project-card').length, 'projects');
    });
});

// Enhanced modal functions
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    } else {
        console.error('Modal not found:', modalId);
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Enhanced project modal
function openProjectModal(projectId) {
    console.log('Opening project modal for ID:', projectId);
    
    document.getElementById('projectModalTitle').textContent = 'Project Details';
    document.getElementById('projectModalContent').innerHTML = `
        <div class="modal-project-details">
            <div class="text-center mb-4">
                <i class="fas fa-code fa-3x text-primary mb-3"></i>
                <h4>Project ID: ${projectId}</h4>
                <p class="text-muted">Project details would be loaded here via AJAX</p>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                This would fetch detailed project information from the server including:
                <ul class="mt-2">
                    <li>Full description</li>
                    <li>Technologies used</li>
                    <li>Project timeline</li>
                    <li>Challenges and solutions</li>
                    <li>Screenshots and demos</li>
                </ul>
            </div>
        </div>
    `;
    showModal('projectModal');
}

// Enhanced business modal
function openBusinessModal(businessId) {
    console.log('Opening business modal for ID:', businessId);
    
    document.getElementById('businessModalTitle').textContent = 'Business Details';
    document.getElementById('businessModalContent').innerHTML = `
        <div class="modal-business-details">
            <div class="text-center mb-4">
                <i class="fas fa-building fa-3x text-primary mb-3"></i>
                <h4>Business ID: ${businessId}</h4>
                <p class="text-muted">Business details would be loaded here via AJAX</p>
            </div>
            <div class="business-info">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    This would fetch detailed business information including:
                    <ul class="mt-2">
                        <li>Full business description</li>
                        <li>Services and offerings</li>
                        <li>Contact information</li>
                        <li>Success stories</li>
                        <li>Testimonials</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    
    // Show learn more button
    const learnMoreBtn = document.getElementById('businessLearnMoreBtn');
    learnMoreBtn.href = '#business-' + businessId;
    learnMoreBtn.style.display = 'inline-block';
    
    showModal('businessModal');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        hideModal(event.target.id);
    }
});


// Enhanced Theme Toggler Functionality
class ThemeManager {
  constructor() {
    this.themeToggle = document.getElementById('themeToggle');
    this.init();
  }

  init() {
    this.loadTheme();
    this.bindEvents();
  }

  loadTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    const theme = savedTheme === 'system' ? (prefersDark ? 'dark' : 'light') : savedTheme;
    
    this.setTheme(theme);
    this.updateTogglePosition(theme);
  }

  setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    const metaThemeColor = document.querySelector("meta[name=theme-color]");
    if (metaThemeColor) {
      metaThemeColor.setAttribute("content", theme === 'dark' ? '#1A1A2E' : '#4a6fa5');
    }
  }

  updateTogglePosition(theme) {
    if (this.themeToggle) {
      this.themeToggle.checked = theme === 'dark';
    }
  }

  bindEvents() {
    if (this.themeToggle) {
      this.themeToggle.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
      });
    }

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme === 'system') {
        this.setTheme(e.matches ? 'dark' : 'light');
      }
    });
  }
}

// Initialize theme manager
document.addEventListener('DOMContentLoaded', () => {
  new ThemeManager();
});
    </script>
</body>
</html>