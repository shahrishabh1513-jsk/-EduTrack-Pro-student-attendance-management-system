<?php
// faculty/attendance/mark-attendance.php
require_once '../../includes/config.php';
redirectIfNotFaculty();

$user_id = $_SESSION['user_id'];
$faculty_id = getFacultyIdByUserId($user_id);

// Get faculty subjects
$subjects_query = "SELECT s.*, c.course_name, c.semester 
                   FROM subjects s
                   JOIN courses c ON s.course_id = c.id
                   WHERE s.faculty_id = :faculty_id";
$subj_stmt = $db->prepare($subjects_query);
$subj_stmt->bindParam(':faculty_id', $faculty_id);
$subj_stmt->execute();
$subjects = $subj_stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_subject = isset($_GET['subject_id']) ? $_GET['subject_id'] : '';
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$students = [];

if ($selected_subject) {
    // Get students for this subject
    $students_query = "SELECT s.*, u.full_name 
                       FROM students s
                       JOIN users u ON s.user_id = u.id
                       WHERE s.course_id = (SELECT course_id FROM subjects WHERE id = :subject_id)
                       AND s.semester = (SELECT semester FROM subjects WHERE id = :subject_id)";
    $stud_stmt = $db->prepare($students_query);
    $stud_stmt->bindParam(':subject_id', $selected_subject);
    $stud_stmt->execute();
    $students = $stud_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if attendance already marked
    foreach($students as &$student) {
        $check_query = "SELECT status, remarks FROM attendance 
                        WHERE student_id = :student_id 
                        AND subject_id = :subject_id 
                        AND date = :date";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':student_id', $student['id']);
        $check_stmt->bindParam(':subject_id', $selected_subject);
        $check_stmt->bindParam(':date', $selected_date);
        $check_stmt->execute();
        $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            $student['attendance_status'] = $existing['status'];
            $student['attendance_remarks'] = $existing['remarks'];
        } else {
            $student['attendance_status'] = '';
            $student['attendance_remarks'] = '';
        }
    }
}

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_attendance'])) {
    $subject_id = $_POST['subject_id'];
    $date = $_POST['date'];
    
    foreach($_POST['attendance'] as $student_id => $status) {
        $remarks = $_POST['remarks'][$student_id] ?? '';
        
        // Check if record exists
        $check_query = "SELECT id FROM attendance 
                        WHERE student_id = :student_id 
                        AND subject_id = :subject_id 
                        AND date = :date";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':student_id', $student_id);
        $check_stmt->bindParam(':subject_id', $subject_id);
        $check_stmt->bindParam(':date', $date);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing
            $update_query = "UPDATE attendance SET status = :status, remarks = :remarks, marked_by = :marked_by 
                            WHERE student_id = :student_id AND subject_id = :subject_id AND date = :date";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':status', $status);
            $update_stmt->bindParam(':remarks', $remarks);
            $update_stmt->bindParam(':marked_by', $faculty_id);
            $update_stmt->bindParam(':student_id', $student_id);
            $update_stmt->bindParam(':subject_id', $subject_id);
            $update_stmt->bindParam(':date', $date);
            $update_stmt->execute();
        } else {
            // Insert new
            $insert_query = "INSERT INTO attendance (student_id, subject_id, date, status, remarks, marked_by) 
                            VALUES (:student_id, :subject_id, :date, :status, :remarks, :marked_by)";
            $insert_stmt = $db->prepare($insert_query);
            $insert_stmt->bindParam(':student_id', $student_id);
            $insert_stmt->bindParam(':subject_id', $subject_id);
            $insert_stmt->bindParam(':date', $date);
            $insert_stmt->bindParam(':status', $status);
            $insert_stmt->bindParam(':remarks', $remarks);
            $insert_stmt->bindParam(':marked_by', $faculty_id);
            $insert_stmt->execute();
        }
    }
    
    $success = "Attendance saved successfully!";
    logActivity($user_id, 'mark_attendance', "Marked attendance for subject ID: $subject_id on $date");
    
    // Refresh page
    header("Location: mark-attendance.php?subject_id=$subject_id&date=$date&success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .attendance-table th {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }
        .btn-present {
            background-color: #28a745;
            color: white;
        }
        .btn-absent {
            background-color: #dc3545;
            color: white;
        }
        .btn-late {
            background-color: #ffc107;
            color: white;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../includes/faculty-nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/faculty-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Mark Attendance</h1>
                </div>
                
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Attendance saved successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Subject Selection -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-book"></i> Select Subject & Date</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="row">
                            <div class="col-md-5">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" class="form-select" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach($subjects as $subject): ?>
                                    <option value="<?php echo $subject['id']; ?>" <?php echo $selected_subject == $subject['id'] ? 'selected' : ''; ?>>
                                        <?php echo $subject['subject_name']; ?> - <?php echo $subject['course_name']; ?> (Sem <?php echo $subject['semester']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" value="<?php echo $selected_date; ?>" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Load Students</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if($selected_subject && count($students) > 0): ?>
                <!-- Attendance Form -->
                <form method="POST" action="">
                    <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                    <input type="hidden" name="date" value="<?php echo $selected_date; ?>">
                    
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> Student List</h5>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                                    <i class="fas fa-check"></i> Mark All Present
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                                    <i class="fas fa-times"></i> Mark All Absent
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" onclick="markAll('late')">
                                    <i class="fas fa-clock"></i> Mark All Late
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered attendance-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Attendance</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count = 1; foreach($students as $student): ?>
                                        <tr>
                                            <td><?php echo $count++; ?></td>
                                            <td><?php echo $student['student_id']; ?></td>
                                            <td><?php echo $student['full_name']; ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="present" id="present_<?php echo $student['id']; ?>"
                                                           <?php echo $student['attendance_status'] == 'present' ? 'checked' : ''; ?>>
                                                    <label for="present_<?php echo $student['id']; ?>" class="btn btn-sm btn-present">P</label>
                                                    
                                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="absent" id="absent_<?php echo $student['id']; ?>"
                                                           <?php echo $student['attendance_status'] == 'absent' ? 'checked' : ''; ?>>
                                                    <label for="absent_<?php echo $student['id']; ?>" class="btn btn-sm btn-absent">A</label>
                                                    
                                                    <input type="radio" name="attendance[<?php echo $student['id']; ?>]" 
                                                           value="late" id="late_<?php echo $student['id']; ?>"
                                                           <?php echo $student['attendance_status'] == 'late' ? 'checked' : ''; ?>>
                                                    <label for="late_<?php echo $student['id']; ?>" class="btn btn-sm btn-late">L</label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="remarks[<?php echo $student['id']; ?>]" 
                                                       class="form-control form-control-sm" 
                                                       value="<?php echo $student['attendance_remarks'] ?? ''; ?>"
                                                       placeholder="Optional remarks">
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" name="save_attendance" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Attendance
                            </button>
                        </div>
                    </div>
                </form>
                <?php elseif($selected_subject && count($students) == 0): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No students found for this subject.
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function markAll(status) {
            const radios = document.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                if (radio.value === status) {
                    radio.checked = true;
                }
            });
        }
    </script>
</body>
</html>