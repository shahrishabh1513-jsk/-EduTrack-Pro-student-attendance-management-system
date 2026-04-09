<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('student')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);

// Get student id
$student_query = "SELECT id FROM students WHERE user_id = {$_SESSION['user_id']}";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);
$student_id = $student['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $from_date = sanitize($_POST['from_date']);
    $to_date = sanitize($_POST['to_date']);
    $reason = sanitize($_POST['reason']);
    $leave_type = sanitize($_POST['leave_type']);
    $contact = sanitize($_POST['emergency_contact']);
    
    $insert_query = "INSERT INTO leave_applications (student_id, from_date, to_date, reason, leave_type, remarks) 
                     VALUES ($student_id, '$from_date', '$to_date', '$reason', '$leave_type', '$contact')";
    
    if (mysqli_query($conn, $insert_query)) {
        $success = "Leave application submitted successfully!";
    } else {
        $error = "Error submitting application";
    }
}

// Fetch leave history
$history_query = "SELECT * FROM leave_applications WHERE student_id = $student_id ORDER BY applied_at DESC";
$history_result = mysqli_query($conn, $history_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Leave - EduTrack Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: 280px; background: white; position: fixed; height: 100vh; overflow-y: auto; box-shadow: 2px 0 10px rgba(0,0,0,0.05); transition: all 0.3s; z-index: 100; }
        .sidebar.collapsed { width: 80px; }
        .sidebar.collapsed .sidebar-nav a span, .sidebar.collapsed .sidebar-header h3 { display: none; }
        .sidebar-header { padding: 25px 20px; border-bottom: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; }
        .sidebar-header .logo { display: flex; align-items: center; gap: 10px; }
        .sidebar-header i { font-size: 2rem; color: #4361ee; }
        .sidebar-header h3 { font-size: 1.2rem; color: #1e293b; }
        .toggle-sidebar { background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #6c757d; }
        .sidebar-nav { padding: 20px 0; }
        .sidebar-nav a { display: flex; align-items: center; gap: 15px; padding: 12px 20px; color: #6c757d; text-decoration: none; transition: all 0.3s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(67,97,238,0.05); color: #4361ee; border-left: 4px solid #4361ee; }
        .main-content { flex: 1; margin-left: 280px; padding: 20px 30px; transition: all 0.3s; }
        .main-content.expanded { margin-left: 80px; }
        .top-header { background: white; padding: 15px 25px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .user-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-profile img { width: 40px; height: 40px; border-radius: 50%; }
        .form-container { background: white; border-radius: 20px; padding: 30px; max-width: 600px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-submit { background: linear-gradient(135deg, #4361ee, #3f37c9); color: white; padding: 14px 30px; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; width: 100%; }
        .leave-history { background: white; border-radius: 20px; padding: 30px; }
        .leave-item { border: 1px solid #e9ecef; border-radius: 15px; padding: 20px; margin-bottom: 15px; }
        .status-pending { color: #ffc107; font-weight: 600; }
        .status-approved { color: #4cc9f0; font-weight: 600; }
        .status-rejected { color: #f72585; font-weight: 600; }
        .alert-success { background: rgba(76,201,240,0.1); color: #4cc9f0; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
        .alert-error { background: rgba(247,37,133,0.1); color: #f72585; padding: 12px; border-radius: 10px; margin-bottom: 20px; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo"><i class="fas fa-graduation-cap"></i><h3>EduTrack Pro</h3></div>
                <button class="toggle-sidebar" id="toggleSidebar"><i class="fas fa-chevron-left"></i></button>
            </div>
            <nav class="sidebar-nav">
                <a href="../dashboard.php"><i class="fas fa-home"></i><span> Dashboard</span></a>
                <a href="../profile.php"><i class="fas fa-user"></i><span> Profile</span></a>
                <a href="../attendance/view-attendance.php"><i class="fas fa-calendar-check"></i><span> Attendance</span></a>
                <a href="leave-apply.php" class="active"><i class="fas fa-calendar-plus"></i><span> Apply Leave</span></a>
                <a href="leave-status.php"><i class="fas fa-calendar-check"></i><span> Leave Status</span></a>
                <a href="grievance.php"><i class="fas fa-exclamation-circle"></i><span> Grievance</span></a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Apply for Leave</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p><?php echo htmlspecialchars($user['id_number']); ?></p></div>
                </div>
            </header>

            <div class="form-container">
                <h3 style="margin-bottom: 20px;">New Leave Application</h3>
                
                <?php if(isset($success)): ?>
                    <div class="alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Leave Type</label>
                        <select name="leave_type" required>
                            <option value="medical">Medical Leave</option>
                            <option value="emergency">Emergency</option>
                            <option value="personal">Personal Leave</option>
                            <option value="vacation">Vacation</option>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>From Date</label>
                            <input type="date" name="from_date" required>
                        </div>
                        <div class="form-group">
                            <label>To Date</label>
                            <input type="date" name="to_date" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Reason for Leave</label>
                        <textarea name="reason" rows="4" required placeholder="Please provide detailed reason..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Emergency Contact</label>
                        <input type="text" name="emergency_contact" placeholder="+91 98765 43210" required>
                    </div>
                    
                    <button type="submit" class="btn-submit">Submit Application</button>
                </form>
            </div>
            
            <div class="leave-history">
                <h3 style="margin-bottom: 20px;">My Leave History</h3>
                <?php if(mysqli_num_rows($history_result) > 0): ?>
                    <?php while($leave = mysqli_fetch_assoc($history_result)): ?>
                        <div class="leave-item">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong><?php echo ucfirst($leave['leave_type']); ?> Leave</strong>
                                <span class="status-<?php echo $leave['status']; ?>"><?php echo strtoupper($leave['status']); ?></span>
                            </div>
                            <p><?php echo date('d M Y', strtotime($leave['from_date'])); ?> - <?php echo date('d M Y', strtotime($leave['to_date'])); ?></p>
                            <p style="color: #6c757d;"><?php echo htmlspecialchars($leave['reason']); ?></p>
                            <p style="font-size: 0.8rem; margin-top: 10px;">Applied: <?php echo date('d M Y', strtotime($leave['applied_at'])); ?></p>
                            <?php if($leave['remarks']): ?>
                                <p style="font-size: 0.8rem;">Remarks: <?php echo htmlspecialchars($leave['remarks']); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No leave applications found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
    </script>
</body>
</html>