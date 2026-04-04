<?php
// student/profile.php
require_once '../includes/config.php';
redirectIfNotStudent();

$user_id = $_SESSION['user_id'];

// Get student details
$query = "SELECT s.*, c.course_name, d.dept_name 
          FROM students s 
          LEFT JOIN courses c ON s.course_id = c.id 
          LEFT JOIN departments d ON c.department_id = d.id 
          WHERE s.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $pincode = sanitizeInput($_POST['pincode']);
    
    $update_query = "UPDATE students SET phone = :phone, address = :address, 
                      city = :city, state = :state, pincode = :pincode 
                      WHERE user_id = :user_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':address', $address);
    $update_stmt->bindParam(':city', $city);
    $update_stmt->bindParam(':state', $state);
    $update_stmt->bindParam(':pincode', $pincode);
    $update_stmt->bindParam(':user_id', $user_id);
    
    if ($update_stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh data
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $error = "Failed to update profile!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
        }
        .info-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: 600;
            color: #667eea;
        }
    </style>
</head>
<body>
    <?php include 'includes/student-nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/student-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="../assets/images/avatars/default-student.png" alt="Profile" class="profile-pic">
                        </div>
                        <div class="col-md-10">
                            <h2><?php echo $student['full_name']; ?></h2>
                            <p class="mb-0">Student ID: <?php echo $student['student_id']; ?> | Enrollment: <?php echo $student['enrollment_no']; ?></p>
                            <p><?php echo $student['course_name']; ?> - Semester <?php echo $student['semester']; ?></p>
                        </div>
                    </div>
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
                        <div class="card info-card">
                            <div class="card-header bg-white">
                                <h5><i class="fas fa-user-circle"></i> Personal Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Full Name:</td>
                                        <td><?php echo $student['full_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Father's Name:</td>
                                        <td><?php echo $student['father_name'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Mother's Name:</td>
                                        <td><?php echo $student['mother_name'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Date of Birth:</td>
                                        <td><?php echo $student['date_of_birth'] ? date('d M Y', strtotime($student['date_of_birth'])) : 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Gender:</td>
                                        <td><?php echo ucfirst($student['gender'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Blood Group:</td>
                                        <td><?php echo $student['blood_group'] ?? 'N/A'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card info-card">
                            <div class="card-header bg-white">
                                <h5><i class="fas fa-graduation-cap"></i> Academic Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Student ID:</td>
                                        <td><?php echo $student['student_id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Enrollment No:</td>
                                        <td><?php echo $student['enrollment_no']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Course:</td>
                                        <td><?php echo $student['course_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Department:</td>
                                        <td><?php echo $student['dept_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Current Semester:</td>
                                        <td><?php echo $student['semester']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Batch Year:</td>
                                        <td><?php echo $student['batch_year']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Joining Date:</td>
                                        <td><?php echo $student['joining_date'] ? date('d M Y', strtotime($student['joining_date'])) : 'N/A'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card info-card">
                            <div class="card-header bg-white">
                                <h5><i class="fas fa-address-card"></i> Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" value="<?php echo $student['phone']; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Alternate Phone</label>
                                        <input type="tel" class="form-control" name="alternate_phone" value="<?php echo $student['alternate_phone']; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="<?php echo $student['email']; ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="2"><?php echo $student['address']; ?></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">City</label>
                                            <input type="text" class="form-control" name="city" value="<?php echo $student['city']; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">State</label>
                                            <input type="text" class="form-control" name="state" value="<?php echo $student['state']; ?>">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Pincode</label>
                                        <input type="text" class="form-control" name="pincode" value="<?php echo $student['pincode']; ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card info-card">
                            <div class="card-header bg-white">
                                <h5><i class="fas fa-users"></i> Guardian Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="info-label">Guardian Name:</td>
                                        <td><?php echo $student['guardian_name'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Guardian Phone:</td>
                                        <td><?php echo $student['guardian_phone'] ?? 'N/A'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="info-label">Relation:</td>
                                        <td><?php echo $student['guardian_relation'] ?? 'N/A'; ?></td>
                                    </tr>
                                </table>
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