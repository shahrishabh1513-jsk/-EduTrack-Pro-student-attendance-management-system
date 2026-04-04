<?php
// student/requests/leave-apply.php
require_once '../../includes/config.php';
redirectIfNotStudent();

$user_id = $_SESSION['user_id'];

// Handle leave application
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_type = sanitizeInput($_POST['leave_type']);
    $from_date = sanitizeInput($_POST['from_date']);
    $to_date = sanitizeInput($_POST['to_date']);
    $reason = sanitizeInput($_POST['reason']);
    
    $query = "INSERT INTO leave_applications (user_id, role, leave_type, from_date, to_date, reason, status) 
              VALUES (:user_id, 'student', :leave_type, :from_date, :to_date, :reason, 'pending')";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':leave_type', $leave_type);
    $stmt->bindParam(':from_date', $from_date);
    $stmt->bindParam(':to_date', $to_date);
    $stmt->bindParam(':reason', $reason);
    
    if ($stmt->execute()) {
        $success = "Leave application submitted successfully!";
        logActivity($user_id, 'apply_leave', "Applied for leave from $from_date to $to_date");
    } else {
        $error = "Failed to submit leave application!";
    }
}

// Get leave history
$history_query = "SELECT * FROM leave_applications WHERE user_id = :user_id ORDER BY created_at DESC";
$hist_stmt = $db->prepare($history_query);
$hist_stmt->bindParam(':user_id', $user_id);
$hist_stmt->execute();
$leave_history = $hist_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/student-nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Apply for Leave</h1>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-pen-alt"></i> New Leave Application</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Leave Type</label>
                                        <select name="leave_type" class="form-select" required>
                                            <option value="casual">Casual Leave</option>
                                            <option value="sick">Sick Leave</option>
                                            <option value="emergency">Emergency Leave</option>
                                            <option value="study">Study Leave</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">From Date</label>
                                            <input type="date" name="from_date" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">To Date</label>
                                            <input type="date" name="to_date" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Reason</label>
                                        <textarea name="reason" class="form-control" rows="4" required placeholder="Please provide detailed reason for leave..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit Application</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history"></i> Leave History</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($leave_history as $leave): ?>
                                            <tr>
                                                <td><?php echo ucfirst($leave['leave_type']); ?></td>
                                                <td><?php echo date('d M', strtotime($leave['from_date'])); ?> - <?php echo date('d M Y', strtotime($leave['to_date'])); ?></td>
                                                <td>
                                                    <?php if($leave['status'] == 'pending'): ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php elseif($leave['status'] == 'approved'): ?>
                                                        <span class="badge bg-success">Approved</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>