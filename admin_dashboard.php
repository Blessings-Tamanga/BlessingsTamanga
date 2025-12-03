<?php
session_start();
include_once 'config/database.php';
include_once 'models/PersonalInfo.php';
include_once 'models/Education.php';
include_once 'models/Skill.php';
include_once 'models/Hobby.php';
include_once 'models/Dashboard.php';// ADD THIS LINE:
include_once 'helpers/file_upload.php';
include_once 'models/Message.php';
include_once 'helpers/EmailService.php';
// Include models
include_once 'models/Project.php';
include_once 'models/Business.php';



// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();


// Initialize models
$personalInfo = new PersonalInfo($db);
$education = new Education($db);
$skill = new Skill($db);
$hobby = new Hobby($db);
$dashboard = new Dashboard($db);

// Get data
$adminData = $personalInfo->getInfo();
$educations = $education->getAll();
$skills = $skill->getAll();
$hobbies = $hobby->getAll();
$stats = $dashboard->getStats();


$stats = $dashboard->getStats(); // This should already be there



// Handle form submissions
$message = '';
$message_type = '';


$database = new Database();
$db = $database->getConnection();

// DEBUG: Check uploads directory and PHP settings
error_log("=== ADMIN DASHBOARD DEBUG ===");
error_log("Upload Max Filesize: " . ini_get('upload_max_filesize'));
error_log("Post Max Size: " . ini_get('post_max_size'));
error_log("Uploads directory exists: " . (is_dir('uploads/') ? 'Yes' : 'No'));
error_log("Uploads directory writable: " . (is_writable('uploads/') ? 'Yes' : 'No'));
error_log("Images directory exists: " . (is_dir('uploads/images/') ? 'Yes' : 'No'));
error_log("Images directory writable: " . (is_writable('uploads/images/') ? 'Yes' : 'No'));
error_log("=== END ADMIN DASHBOARD DEBUG ===");


// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit();
}


// Initialize models after database connection
$messageModel = new Message($db);

// Email service configuration
$emailConfig = [
    'from_email' => 'blessingstamanga@gmail.com',
    'from_name' => 'Blessings E. Tamanga'
];
$emailService = new EmailService($emailConfig);

// Handle Personal Info Update
if (isset($_POST['update_personal_info'])) {
    $data = [
        'full_name' => $_POST['full_name'],
        'title' => $_POST['title'],
        'bio' => $_POST['bio'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'city' => $_POST['city'],
        'profile_image' => $adminData['profile_image'] // Keep existing image
    ];
    
    if ($personalInfo->updateInfo($data)) {
        $message = "Personal information updated successfully!";
        $message_type = "success";
        $adminData = $personalInfo->getInfo(); // Refresh data
    } else {
        $message = "Failed to update personal information!";
        $message_type = "error";
    }
}

$database = new Database();
$db = $database->getConnection();

// DEBUG: Check uploads directory
$upload_dir = 'uploads/images/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
if (!is_writable($upload_dir)) {
    error_log("Upload directory not writable: " . $upload_dir);
}

// Handle message reply
// ... existing includes and session start ...

// Handle message reply - UPDATE THIS SECTION
if (isset($_POST['reply_message'])) {
    $message_id = $_POST['message_id'];
    $reply_subject = $_POST['reply_subject'];
    $reply_message = $_POST['reply_message'];
    
    // Get the original message details
    $message_data = $messageModel->getById($message_id);
    
    if ($message_data) {
        // Send the reply email
        if ($emailService->sendCustomReply($message_data['email'], $message_data['name'], $reply_subject, $reply_message)) {
            // Mark as replied in database
            $messageModel->markAsReplied($message_id);
            $message = "✅ Reply sent successfully to " . $message_data['email'] . "!";
            $message_type = "success";
        } else {
            $message = "❌ Failed to send reply email. Please check your server email configuration.";
            $message_type = "error";
        }
    } else {
        $message = "❌ Message not found!";
        $message_type = "error";
    }
}

// ... rest of your code ...


// Handle mark as read
if (isset($_GET['mark_read'])) {
    $messageModel->markAsRead($_GET['mark_read']);
    $message = "Message marked as read!";
    $message_type = "success";
}

// Get message counts
$unread_messages = $messageModel->getUnreadCount();
$total_messages = $messageModel->getTotalCount();
$all_messages = $messageModel->getAll();




$database = new Database();
$db = $database->getConnection();

// Initialize models
$personalInfo = new PersonalInfo($db);
$education = new Education($db);
$skill = new Skill($db);
$hobby = new Hobby($db);
$dashboard = new Dashboard($db);
// ADD THIS LINE:
$fileUpload = new FileUpload();

// Get data
$adminData = $personalInfo->getInfo();

// ADD THIS CODE TO HANDLE PROFILE IMAGE UPLOAD:
if (isset($_POST['upload_profile_image'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $upload_result = $fileUpload->uploadProfileImage($_FILES['profile_image']);
        
        if ($upload_result['success']) {
            // Update database with new image path
            if ($personalInfo->updateProfileImage($upload_result['file_path'])) {
                $message = $upload_result['message'];
                $message_type = "success";
                $adminData = $personalInfo->getInfo(); // Refresh data
            } else {
                $message = "Failed to update profile image in database!";
                $message_type = "error";
            }
        } else {
            $message = $upload_result['message'];
            $message_type = "error";
        }
    } else {
        $message = "Please select a valid image file!";
        $message_type = "error";
    }
}

// ... rest of your existing code for other forms ...



// Handle Add Education with Image
if (isset($_POST['add_education'])) {
    $data = [
        'degree_title' => $_POST['degree_title'],
        'institution' => $_POST['institution'],
        'year_start' => $_POST['year_start'],
        'year_end' => $_POST['year_end'],
        'description' => $_POST['description'],
        'image_path' => '',
        'certificate_link' => $_POST['certificate_link'] ?? '',
        'institution_link' => $_POST['institution_link'] ?? ''
    ];
    
    // Handle image upload if provided
    if (isset($_FILES['education_image']) && $_FILES['education_image']['error'] === 0) {
        $upload_result = $fileUpload->uploadEducationImage($_FILES['education_image']);
        if ($upload_result['success']) {
            $data['image_path'] = $upload_result['file_path'];
        } else {
            $message = $upload_result['message'];
            $message_type = "error";
        }
    }
    
    if ($education->create($data)) {
        $message = "Education added successfully!" . (isset($upload_result) && $upload_result['success'] ? " Image uploaded." : "");
        $message_type = "success";
        $educations = $education->getAll(); // Refresh data
    } else {
        $message = "Failed to add education!";
        $message_type = "error";
    }
}

// Handle Delete Education
if (isset($_GET['delete_education'])) {
    $education_id = $_GET['delete_education'];
    if ($education->delete($education_id)) {
        $message = "Education deleted successfully!";
        $message_type = "success";
        $educations = $education->getAll(); // Refresh data
    } else {
        $message = "Failed to delete education!";
        $message_type = "error";
    }
}



// Handle Add Skill
if (isset($_POST['add_skill'])) {
    $data = [
        'skill_name' => $_POST['skill_name'],
        'category' => $_POST['category'],
        'level' => $_POST['level'],
        'description' => $_POST['description'],
        'icon' => $_POST['icon']
    ];
    
    if ($skill->create($data)) {
        $message = "Skill added successfully!";
        $message_type = "success";
        $skills = $skill->getAll(); // Refresh data
    } else {
        $message = "Failed to add skill!";
        $message_type = "error";
    }
}

// Handle Delete Skill
if (isset($_GET['delete_skill'])) {
    $skill_id = $_GET['delete_skill'];
    if ($skill->delete($skill_id)) {
        $message = "Skill deleted successfully!";
        $message_type = "success";
        $skills = $skill->getAll(); // Refresh data
    } else {
        $message = "Failed to delete skill!";
        $message_type = "error";
    }
}

// Handle Add Hobby
if (isset($_POST['add_hobby'])) {
    $data = [
        'hobby_name' => $_POST['hobby_name'],
        'description' => $_POST['description'],
        'image_path' => $_POST['image_path']
    ];
    
    if ($hobby->create($data)) {
        $message = "Hobby added successfully!";
        $message_type = "success";
        $hobbies = $hobby->getAll(); // Refresh data
    } else {
        $message = "Failed to add hobby!";
        $message_type = "error";
    }
}

// Handle Delete Hobby
if (isset($_GET['delete_hobby'])) {
    $hobby_id = $_GET['delete_hobby'];
    if ($hobby->delete($hobby_id)) {
        $message = "Hobby deleted successfully!";
        $message_type = "success";
        $hobbies = $hobby->getAll(); // Refresh data
    } else {
        $message = "Failed to delete hobby!";
        $message_type = "error";
    }
}


//


// Initialize models
$project = new Project($db);
$business = new Business($db);
$fileUpload = new FileUpload();

// Handle form submissions
$message = '';
$messageType = '';

// Handle Project Creation
if (isset($_POST['add_project'])) {
    $data = [
        'title' => trim($_POST['title']),
        'description' => trim($_POST['description']),
        'short_description' => trim($_POST['short_description']),
        'category' => trim($_POST['category']),
        'status' => $_POST['status'],
        'image_url' => '',
        'project_url' => trim($_POST['project_url'] ?? ''),
        'github_url' => trim($_POST['github_url'] ?? ''),
        'technologies' => json_encode(explode(',', trim($_POST['technologies'] ?? ''))),
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'start_date' => $_POST['start_date'] ?: null,
        'end_date' => $_POST['end_date'] ?: null
    ];

    // Handle image upload
    if (isset($_FILES['project_image']) && $_FILES['project_image']['error'] === 0) {
        $uploadResult = $fileUpload->uploadProjectImage($_FILES['project_image']);
        if ($uploadResult['success']) {
            $data['image_url'] = $uploadResult['file_path'];
        } else {
            $message = $uploadResult['message'];
            $messageType = 'error';
        }
    }

    if ($project->create($data)) {
        $message = 'Project added successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to add project.';
        $messageType = 'error';
    }
}

// Handle Business Creation
if (isset($_POST['add_business'])) {
    $data = [
        'name' => trim($_POST['name']),
        'description' => trim($_POST['description']),
        'short_description' => trim($_POST['short_description']),
        'category' => trim($_POST['category']),
        'logo_url' => '',
        'website_url' => trim($_POST['website_url'] ?? ''),
        'contact_email' => trim($_POST['contact_email'] ?? ''),
        'status' => $_POST['status'],
        'learn_more_url' => trim($_POST['learn_more_url'] ?? ''),
        'featured' => isset($_POST['featured']) ? 1 : 0
    ];

    // Handle logo upload
    if (isset($_FILES['business_logo']) && $_FILES['business_logo']['error'] === 0) {
        $uploadResult = $fileUpload->uploadBusinessLogo($_FILES['business_logo']);
        if ($uploadResult['success']) {
            $data['logo_url'] = $uploadResult['file_path'];
        } else {
            $message = $uploadResult['message'];
            $messageType = 'error';
        }
    }

    if ($business->create($data)) {
        $message = 'Business added successfully!';
        $messageType = 'success';
    } else {
        $message = 'Failed to add business.';
        $messageType = 'error';
    }
}

// Handle deletions
if (isset($_GET['delete_project'])) {
    if ($project->delete($_GET['delete_project'])) {
        $message = 'Project deleted successfully!';
        $messageType = 'success';
    }
}

if (isset($_GET['delete_business'])) {
    if ($business->delete($_GET['delete_business'])) {
        $message = 'Business deleted successfully!';
        $messageType = 'success';
    }
}

// Get data for display
$projects = $project->getAll()->fetchAll(PDO::FETCH_ASSOC);
$businesses = $business->getAll()->fetchAll(PDO::FETCH_ASSOC);
$projectStats = $project->getCountByStatus();
$businessStats = $business->getCountByStatus();




// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styledashboard.css">
    <script src="assets/interactivity/js.js"></script>
    <style>
        
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
    <?php if ($message): ?>
    <div class="notification <?php echo $message_type; ?>">
        <i class="fas fa-<?php echo $message_type === 'success' ? 'check' : 'exclamation-triangle'; ?>"></i>
        <?php echo $message; ?>
    </div>
    <script>
        // Auto-hide notification after 5 seconds
        setTimeout(() => {
            document.querySelector('.notification').style.display = 'none';
        }, 5000);
    </script>
    <?php endif; ?>

    <div class="container-fluid">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <span class="sidebar-brand">Portfolio Admin</span>
            </div>
            <div class="sidebar-menu">
                <a href="#dashboard" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#personal-info" class="menu-item">
                    <i class="fas fa-user"></i>
                    <span>Personal Info</span>
                </a>
                <a href="#education" class="menu-item">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Education</span>
                </a>
                <a href="#skills" class="menu-item">
                    <i class="fas fa-code"></i>
                    <span>Skills</span>
                </a>
                <a href="#hobbies" class="menu-item">
                    <i class="fas fa-heart"></i>
                    <span>Hobbies</span>
                </a>

                <a href="#projects" class="menu-item">
                    <i class="fas fa-code-branch"></i>
                    <span>Projects</span>
                </a>

                <a href="#businesses" class="menu-item">
                   <i class="fas fa-building"></i>
                    <span>Businesses</span>
                </a>

                <a href="#settings" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>

                 <a href="#settings" class="menu-item">
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

       </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <div class="header-title">
                    <h1>Admin Dashboard</h1>
                    <p>Manage your portfolio content and settings</p>
                </div>
                <div class="header-actions">
                    <div class="user-info">
                        <div class="user-avatar">BE</div>
                        <div>
                            <div><?php echo htmlspecialchars($adminData['full_name'] ?? 'Admin User'); ?></div>
                            <div class="text-muted">Administrator</div>
                        </div>
                    </div>
                    <a href="?logout=true" class="btn btn-outline-primary" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

           <!-- Dashboard Section -->
<section id="dashboard">
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Profile Views</div>
                <div class="card-icon">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
            <div class="card-content">
                <h3><?php echo $stats['profile_views'] ?? '0'; ?></h3>
                <p class="text-success">+12.4% from last month</p>
            </div>
        </div>
        
        <div class="card card-success">
            <div class="card-header">
                <div class="card-title">Completed Projects</div>
                <div class="card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="card-content">
                <h3><?php echo $stats['completed_projects'] ?? '0'; ?></h3>
                <p>+3 this month</p>
            </div>
        </div>
        
        <!-- ONLINE USERS CARD -->
        <div class="card card-info">
            <div class="card-header">
                <div class="card-title">Online Users</div>
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="card-content">
                <h3><?php echo $stats['online_users'] ?? '0'; ?></h3>
                <p>Currently active</p>
            </div>
        </div>
        
        <!-- MESSAGES CARD - CLICKABLE -->
        <div class="card card-warning" style="cursor: pointer;" onclick="showModal('messagesModal')">
            <div class="card-header">
                <div class="card-title">Messages</div>
                <div class="card-icon">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="card-content">
                <h3><?php echo $total_messages; ?></h3>
                <p><?php echo $unread_messages; ?> unread</p>
                <?php if ($unread_messages > 0): ?>
                <div class="notification-badge" style="position: absolute; top: 10px; right: 10px; background: red; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                    <?php echo $unread_messages; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>



    <!-- Messages Modal -->
<div class="modal" id="messagesModal" style="display: none;">
    <div class="modal-content" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">Messages (<?php echo $total_messages; ?>)</h3>
            <span class="close" onclick="hideModal('messagesModal')">&times;</span>
        </div>
        <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
            <?php if (empty($all_messages)): ?>
                <p class="text-muted text-center">No messages yet.</p>
            <?php else: ?>
                <div class="messages-list">
                    <?php foreach($all_messages as $msg): ?>
                    <!-- In the messages modal, update the message item display -->
<div class="message-item" style="border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: <?php echo $msg['is_read'] ? '#fff' : '#f8f9fa'; ?>;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
        <div style="flex: 1;">
            <h4 style="margin: 0; color: #333; font-size: 1.1rem;">
                <?php echo htmlspecialchars($msg['subject']); ?>
            </h4>
            <p style="margin: 5px 0; color: #666; font-size: 0.9rem;">
                <i class="fas fa-user"></i> 
                <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
            </p>
            <p style="margin: 5px 0; color: #4a6fa5; font-size: 0.85rem;">
                <i class="fas fa-envelope"></i> 
                <?php echo htmlspecialchars($msg['email']); ?>
            </p>
            <small style="color: #888;">
                <i class="fas fa-clock"></i>
                <?php echo date('M j, Y g:i A', strtotime($msg['created_at'])); ?>
                <?php if (!$msg['is_read']): ?>
                    <span class="status-badge status-active" style="margin-left: 10px;">NEW</span>
                <?php endif; ?>
                <?php if ($msg['replied']): ?>
                    <span class="status-badge status-success" style="margin-left: 10px; background: #28a745;">REPLIED</span>
                <?php endif; ?>
            </small>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php if (!$msg['is_read']): ?>
                <a href="?mark_read=<?php echo $msg['message_id']; ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-check"></i> Read
                </a>
            <?php endif; ?>
            <button class="btn btn-sm btn-success" 
                    onclick="openReplyModal(
                        <?php echo $msg['message_id']; ?>, 
                        '<?php echo addslashes($msg['name']); ?>', 
                        '<?php echo addslashes($msg['email']); ?>', 
                        '<?php echo addslashes($msg['subject']); ?>'
                    )">
                <i class="fas fa-reply"></i> Reply
            </button>
        </div>
    </div>
    <div style="background: white; padding: 12px; border-radius: 5px; border-left: 3px solid #4a6fa5; margin-top: 10px;">
        <p style="margin: 0; white-space: pre-wrap; line-height: 1.5;"><?php echo htmlspecialchars($msg['message']); ?></p>
    </div>
</div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reply Message Modal -->
<div class="modal" id="replyModal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">✉️ Send Email Reply</h3>
            <span class="close">&times;</span>
        </div>
        <form method="POST" id="replyForm">
            <div class="modal-body">
                <input type="hidden" name="message_id" id="reply_message_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">To Email</label>
                        <input type="email" class="form-control" id="reply_to_email" readonly 
                               style="background: #f8f9fa; font-weight: bold;">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Recipient Name</label>
                        <input type="text" class="form-control" id="reply_to_name" readonly 
                               style="background: #f8f9fa;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" class="form-control" name="reply_subject" id="reply_subject" required
                           placeholder="Enter email subject...">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Your Message</label>
                    <textarea class="form-control" name="reply_message" id="reply_message" 
                              rows="8" required placeholder="Type your reply message here..."></textarea>
                    <small class="text-muted">This message will be sent directly to the recipient's email address.</small>
                </div>
                
                <div class="form-group" style="background: #e8f4fd; padding: 15px; border-radius: 5px; border-left: 4px solid #4a6fa5;">
                    <h4 style="margin: 0 0 10px 0; color: #2c5aa0;">📧 Email Preview</h4>
                    <p style="margin: 5px 0;"><strong>From:</strong> <?php echo $emailConfig['from_email'] ?? 'blessings.tamanga@example.com'; ?></p>
                    <p style="margin: 5px 0;"><strong>To:</strong> <span id="preview_to_email"></span></p>
                    <p style="margin: 5px 0;"><strong>Subject:</strong> <span id="preview_subject"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="reply_message" class="btn btn-success" style="padding: 10px 20px;">
                    <i class="fas fa-paper-plane"></i> Send Email Reply
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="hideModal('replyModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
</div>
</section>


    
        <!-- Personal Info Section -->
<section id="personal-info" style="display: none;">
    <div class="card">
        <div class="section-header">
            <h3 class="section-title">Personal Information</h3>
        </div>
        
        <!-- SIMPLE WORKING VERSION -->
<div class="form-group">
    <label class="form-label">Profile Image</label>
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
        <div>
            <?php if (!empty($adminData['profile_image']) && file_exists($adminData['profile_image'])): ?>
                <img src="<?php echo $adminData['profile_image'] . '?t=' . time(); ?>" 
                     alt="Current Profile" 
                     style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #4a6fa5;">
            <?php else: ?>
                <div style="width: 150px; height: 150px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; border: 3px solid #4a6fa5;">
                    <i class="fas fa-user" style="font-size: 3rem; color: #666;"></i>
                    <span style="font-size: 0.8rem; text-align: center;">No Image</span>
                </div>
            <?php endif; ?>
        </div>
<div style="flex: 1;">
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_image" accept="image/*" required>
        <button type="submit" name="upload_profile_image" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Image
        </button>
    </form>
</div>    </div>
</div>

        <!-- PERSONAL INFORMATION FORM -->
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($adminData['full_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Job Title</label>
                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($adminData['title'] ?? ''); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea class="form-control" name="bio" rows="4" required><?php echo htmlspecialchars($adminData['bio'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($adminData['email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($adminData['phone'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($adminData['city'] ?? ''); ?>">
            </div>
            
            <button type="submit" name="update_personal_info" class="btn btn-success">
                <i class="fas fa-save"></i> Save Personal Information
            </button>
        </form>
    </div>
</section>

            <!-- Education Section --><!-- Education Section -->
<section id="education" style="display: none;">
    <div class="card">
        <div class="section-header">
            <h3 class="section-title">Education & Background</h3>
            <button type="button" class="btn btn-primary" onclick="showModal('addEducationModal')">
                <i class="fas fa-plus"></i> Add Education
            </button>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Degree</th>
                        <th>Institution</th>
                        <th>Years</th>
                        <th>Links</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($educations as $edu): ?>
                    <tr>
                        <td>
                            <?php if (!empty($edu['image_path'])): ?>
                                <img src="<?php echo $edu['image_path']; ?>" 
                                     alt="<?php echo htmlspecialchars($edu['degree_title']); ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #666;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($edu['degree_title']); ?></strong>
                            <?php if (!empty($edu['description'])): ?>
                                <br><small class="text-muted"><?php echo substr(htmlspecialchars($edu['description']), 0, 50); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($edu['institution']); ?></td>
                        <td><?php echo $edu['year_start']; ?> - <?php echo $edu['year_end'] ?: 'Present'; ?></td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-direction: column;">
                                <?php if (!empty($edu['certificate_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($edu['certificate_link']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary" 
                                       style="padding: 2px 8px; font-size: 0.75rem;">
                                        <i class="fas fa-certificate"></i> Certificate
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($edu['institution_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($edu['institution_link']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-secondary" 
                                       style="padding: 2px 8px; font-size: 0.75rem;">
                                        <i class="fas fa-school"></i> Institution
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="action-buttons">
                            <a href="?delete_education=<?php echo $edu['education_id']; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Are you sure you want to delete this education entry?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>



   <!-- Add Education Modal -->
<div class="modal" id="addEducationModal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">Add Education</h3>
            <span class="close" onclick="hideModal('addEducationModal')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Degree/Certificate Title *</label>
                    <input type="text" class="form-control" name="degree_title" placeholder="Enter degree or certificate name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Institution *</label>
                    <input type="text" class="form-control" name="institution" placeholder="Enter institution name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Year *</label>
                        <input type="number" class="form-control" name="year_start" placeholder="Enter start year" min="1900" max="2030" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Year</label>
                        <input type="number" class="form-control" name="year_end" placeholder="Enter end year (leave empty if present)" min="1900" max="2030">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Enter description of your education"></textarea>
                </div>
                
                <!-- NEW: Education Image Upload -->
                <div class="form-group">
                    <label class="form-label">Education Image</label>
                    <div class="file-upload-area" style="border: 2px dashed #ddd; padding: 20px; text-align: center; border-radius: 8px; background: #f9f9f9;">
                        <i class="fas fa-image" style="font-size: 2rem; color: #666; margin-bottom: 10px;"></i>
                        <p style="margin: 10px 0; color: #666;">Click to upload or drag and drop</p>
                        <input type="file" name="education_image" accept="image/*" 
                               style="display: none;" id="educationImageInput">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('educationImageInput').click()">
                            <i class="fas fa-upload"></i> Choose Image
                        </button>
                        <div id="educationImagePreview" style="margin-top: 15px; display: none;">
                            <img id="educationPreview" style="max-width: 200px; max-height: 150px; border-radius: 5px;">
                        </div>
                    </div>
                    <small class="text-muted">Optional: Upload an image of your certificate, institution, or related visual</small>
                </div>
                
                <!-- NEW: Links Section -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Certificate Link</label>
                        <input type="url" class="form-control" name="certificate_link" placeholder="https://example.com/certificate">
                        <small class="text-muted">Link to view/download certificate</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Institution Link</label>
                        <input type="url" class="form-control" name="institution_link" placeholder="https://example.com/institution">
                        <small class="text-muted">Link to institution website</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_education" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Education
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="hideModal('addEducationModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>



            <!-- Skills Section -->
            <section id="skills" style="display: none;">
                <div class="card">
                    <div class="section-header">
                        <h3 class="section-title">Skills Management</h3>
                        <button type="button" class="btn btn-primary" onclick="showModal('addSkillModal')">
                            <i class="fas fa-plus"></i> Add Skill
                        </button>
                    </div>
                    
                    <div class="skills-grid">
                        <?php foreach($skills as $skill_item): ?>
                        <div class="skill-item">
                            <div class="circle" style="background: conic-gradient(var(--primary) <?php echo $skill_item['level'] * 3.6; ?>deg, var(--gray-200) 0deg);">
                                <span><?php echo $skill_item['level']; ?>%</span>
                            </div>
                            <h3><i class="<?php echo $skill_item['icon'] ?? 'fas fa-code'; ?>"></i> <?php echo htmlspecialchars($skill_item['skill_name']); ?></h3>
                            <p><?php echo htmlspecialchars($skill_item['description']); ?></p>
                            <div class="action-buttons mt-3">
                                <a href="?delete_skill=<?php echo $skill_item['skill_id']; ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this skill?')">
                                    Delete
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Hobbies Section -->
            <section id="hobbies" style="display: none;">
                <div class="card">
                    <div class="section-header">
                        <h3 class="section-title">Hobbies & Interests</h3>
                        <button type="button" class="btn btn-primary" onclick="showModal('addHobbyModal')">
                            <i class="fas fa-plus"></i> Add Hobby
                        </button>
                    </div>
                    
                    <div class="hobbies-container">
                        <?php foreach($hobbies as $hobby_item): ?>
                        <div class="hobby-card" style="background-image: url('<?php echo htmlspecialchars($hobby_item['image_path'] ?? 'https://images.unsplash.com/photo-1551632811-561732d1e306?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80'); ?>')">
                            <div class="overlay">
                                <h3><?php echo htmlspecialchars($hobby_item['hobby_name']); ?></h3>
                                <p><?php echo htmlspecialchars($hobby_item['description']); ?></p>
                                <div class="action-buttons mt-3">
                                    <a href="?delete_hobby=<?php echo $hobby_item['hobby_id']; ?>" class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this hobby?')">
                                        Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Projects Section -->
<section id="projects" style="display: none;">
    <div class="card">
        <div class="section-header">
            <div>
                <h3 class="section-title">Projects Management</h3>
                <p class="text-muted">Manage your portfolio projects</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="showModal('addProjectModal')">
                <i class="fas fa-plus"></i> Add Project
            </button>
        </div>

        <!-- Project Statistics -->
        <div class="dashboard-cards" style="margin-bottom: 2rem;">
            <?php
            $statusColors = [
                'completed' => 'success',
                'in_progress' => 'warning', 
                'planned' => 'info',
                'on_hold' => 'secondary'
            ];
            
            foreach ($projectStats as $stat): 
                $count = $stat['count'];
                $status = $stat['status'];
                $color = $statusColors[$status] ?? 'secondary';
            ?>
            <div class="card card-<?php echo $color; ?>">
                <div class="card-header">
                    <div class="card-title"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></div>
                    <div class="card-icon">
                        <i class="fas fa-<?php echo $status === 'completed' ? 'check-circle' : 'tasks'; ?>"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3><?php echo $count; ?></h3>
                    <p>Projects</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Projects Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Project</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Technologies</th>
                        <th>Dates</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projects)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No projects added yet</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $proj): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if (!empty($proj['image_url'])): ?>
                                    <img src="<?php echo $proj['image_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($proj['title']); ?>"
                                         class="rounded"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-code text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($proj['title']); ?></strong>
                                        <?php if ($proj['short_description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($proj['short_description']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($proj['category']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $proj['status'] === 'completed' ? 'active' : ($proj['status'] === 'in_progress' ? 'warning' : 'pending'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $proj['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $techs = json_decode($proj['technologies'] ?? '[]', true);
                                if (is_array($techs) && !empty($techs)):
                                    foreach (array_slice($techs, 0, 2) as $tech):
                                ?>
                                    <span class="tech-tag"><?php echo htmlspecialchars(trim($tech)); ?></span>
                                <?php 
                                    endforeach;
                                    if (count($techs) > 2):
                                ?>
                                    <span class="tech-tag">+<?php echo count($techs) - 2; ?></span>
                                <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small>
                                    <?php if ($proj['start_date']): ?>
                                        <?php echo date('M Y', strtotime($proj['start_date'])); ?>
                                    <?php else: ?>
                                        TBD
                                    <?php endif; ?>
                                    -
                                    <?php if ($proj['end_date']): ?>
                                        <?php echo date('M Y', strtotime($proj['end_date'])); ?>
                                    <?php else: ?>
                                        Present
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($proj['featured']): ?>
                                    <i class="fas fa-star text-warning" title="Featured"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?delete_project=<?php echo $proj['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Delete this project?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Add Project Modal -->
<div class="modal" id="addProjectModal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">Add New Project</h3>
            <span class="close" onclick="hideModal('addProjectModal')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project Title *</label>
                        <input type="text" class="form-control" name="title" required 
                               placeholder="e.g., E-commerce Website">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <input type="text" class="form-control" name="category" required
                               placeholder="e.g., Web Development, Mobile App">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Short Description *</label>
                    <textarea class="form-control" name="short_description" rows="2" required
                              placeholder="Brief description for project cards..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Description</label>
                    <textarea class="form-control" name="description" rows="4"
                              placeholder="Detailed project description..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="planned">Planned</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Technologies</label>
                        <input type="text" class="form-control" name="technologies"
                               placeholder="PHP, JavaScript, React, MySQL (comma separated)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" name="start_date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Project URL</label>
                        <input type="url" class="form-control" name="project_url"
                               placeholder="https://yourproject.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">GitHub URL</label>
                        <input type="url" class="form-control" name="github_url"
                               placeholder="https://github.com/username/repo">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Project Image</label>
                    <input type="file" class="form-control" name="project_image" accept="image/*">
                    <small class="text-muted">Optional: JPG, PNG, GIF, WEBP (Max 2MB)</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="featured" id="projectFeatured">
                        <label class="form-check-label" for="projectFeatured">
                            Feature this project
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_project" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Project
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="hideModal('addProjectModal')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
</section>


<!-- Businesses Section -->
<section id="businesses" style="display: none;">
    <div class="card">
        <div class="section-header">
            <div>
                <h3 class="section-title">Businesses Management</h3>
                <p class="text-muted">Manage your business ventures</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="showModal('addBusinessModal')">
                <i class="fas fa-plus"></i> Add Business
            </button>
        </div>

        <!-- Business Statistics -->
        <div class="dashboard-cards" style="margin-bottom: 2rem;">
            <?php
            $bizStatusColors = [
                'active' => 'success',
                'planned' => 'warning',
                'inactive' => 'secondary'
            ];
            
            foreach ($businessStats as $stat): 
                $count = $stat['count'];
                $status = $stat['status'];
                $color = $bizStatusColors[$status] ?? 'secondary';
            ?>
            <div class="card card-<?php echo $color; ?>">
                <div class="card-header">
                    <div class="card-title"><?php echo ucfirst($status); ?></div>
                    <div class="card-icon">
                        <i class="fas fa-<?php echo $status === 'active' ? 'rocket' : 'clock'; ?>"></i>
                    </div>
                </div>
                <div class="card-content">
                    <h3><?php echo $count; ?></h3>
                    <p>Businesses</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Businesses Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Contact</th>
                        <th>Website</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($businesses)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-store-alt fa-2x mb-2"></i>
                            <p>No businesses added yet</p>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($businesses as $biz): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if (!empty($biz['logo_url'])): ?>
                                    <img src="<?php echo $biz['logo_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($biz['name']); ?>"
                                         class="rounded"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-building text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?php echo htmlspecialchars($biz['name']); ?></strong>
                                        <?php if ($biz['short_description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($biz['short_description']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($biz['category']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $biz['status'] === 'active' ? 'active' : ($biz['status'] === 'planned' ? 'warning' : 'pending'); ?>">
                                    <?php echo ucfirst($biz['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($biz['contact_email']): ?>
                                    <small><?php echo htmlspecialchars($biz['contact_email']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($biz['website_url']): ?>
                                    <a href="<?php echo htmlspecialchars($biz['website_url']); ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($biz['featured']): ?>
                                    <i class="fas fa-star text-warning" title="Featured"></i>
                                <?php else: ?>
                                    <i class="far fa-star text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?delete_business=<?php echo $biz['id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Delete this business?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Business Modal -->
<div class="modal" id="addBusinessModal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title">Add New Business</h3>
            <span class="close" onclick="hideModal('addBusinessModal')">&times;</span>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Business Name *</label>
                        <input type="text" class="form-control" name="name" required
                               placeholder="e.g., Tech Solutions Inc.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <input type="text" class="form-control" name="category" required
                               placeholder="e.g., Software Development, Consulting">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Short Description *</label>
                    <textarea class="form-control" name="short_description" rows="2" required
                              placeholder="Brief description for business cards..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Full Description</label>
                    <textarea class="form-control" name="description" rows="4"
                              placeholder="Detailed business description..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status *</label>
                        <select class="form-control" name="status" required>
                            <option value="active">Active</option>
                            <option value="planned">Planned</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-control" name="contact_email"
                               placeholder="contact@business.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Website URL</label>
                        <input type="url" class="form-control" name="website_url"
                               placeholder="https://yourbusiness.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Learn More URL</label>
                        <input type="url" class="form-control" name="learn_more_url"
                               placeholder="https://yourbusiness.com/about">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Business Logo</label>
                    <input type="file" class="form-control" name="business_logo" accept="image/*">
                    <small class="text-muted">Optional: JPG, PNG, GIF, WEBP (Max 2MB)</small>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="featured" id="businessFeatured">
                        <label class="form-check-label" for="businessFeatured">
                            Feature this business
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="add_business" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Business
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="hideModal('addBusinessModal')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>
</section>

            <!-- Settings Section -->
            <section id="settings" style="display: none;">
                <div class="card">
                    <div class="section-header">
                        <h3 class="section-title">Settings</h3>
                    </div>
                    <p>Settings section coming soon...</p>
                </div>
            </section>
        </div>
    </div>

    <!-- Add Skill Modal -->
    <div class="modal" id="addSkillModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Skill</h3>
                <span class="close" onclick="hideModal('addSkillModal')">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Skill Name</label>
                        <input type="text" class="form-control" name="skill_name" placeholder="Enter skill name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select class="form-control" name="category" required>
                            <option value="Technical">Technical</option>
                            <option value="Creative">Creative</option>
                            <option value="Personal">Personal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Proficiency Level</label>
                        <input type="range" class="form-control" name="level" min="0" max="100" value="80">
                        <div class="d-flex justify-content-between mt-1">
                            <span>0%</span>
                            <span>50%</span>
                            <span>100%</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Icon Class</label>
                        <input type="text" class="form-control" name="icon" placeholder="fas fa-code" value="fas fa-code">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter skill description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_skill" class="btn btn-success">Save Skill</button>
                    <button type="button" class="btn btn-outline-danger" onclick="hideModal('addSkillModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Hobby Modal -->
    <div class="modal" id="addHobbyModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Hobby</h3>
                <span class="close" onclick="hideModal('addHobbyModal')">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Hobby Name</label>
                        <input type="text" class="form-control" name="hobby_name" placeholder="Enter hobby name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" placeholder="Enter hobby description"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Image URL</label>
                        <input type="text" class="form-control" name="image_path" placeholder="Enter image URL">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_hobby" class="btn btn-success">Save Hobby</button>
                    <button type="button" class="btn btn-outline-danger" onclick="hideModal('addHobbyModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets\interactivity\admin.js">
    </script>
</body>
</html>