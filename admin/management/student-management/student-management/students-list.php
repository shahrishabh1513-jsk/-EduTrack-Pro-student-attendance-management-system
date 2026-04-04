<?php
// admin/management/student-management/students-list.php
require_once '../../../includes/config.php';
redirectIfNotAdmin();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Build query
$query = "SELECT s.*, c.course_name, d.dept_name, u.email, u.status 
          FROM students s
          LEFT JOIN courses c ON s.course_id = c.id
          LEFT JOIN departments d ON c.department_id = d.id
          LEFT JOIN users u ON s.user_id = u.id
          WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM students s WHERE 1=1";

if ($search) {
    $query .= " AND (s.student_id LIKE :search OR s.full_name LIKE :search OR s.enrollment_no LIKE :search)";
    $count_query .= " AND (student_id LIKE :search OR full_name LIKE :search OR enrollment_no LIKE :search)";
}

$query .= " ORDER BY s.id DESC LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$count_stmt = $db->prepare($count_query);

if ($search) {
    $search_param = "%$search%";
    $stmt->bindParam(':search', $search_param);
    $count_stmt->bindParam(':search', $search_param);
}
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count_stmt->execute();
$total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../../includes/admin-nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../../includes/admin-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Student Management</h1>
                    <div>
                        <a href="add-student.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Student
                        </a>
                        <a href="student-import.php" class="btn btn-success">
                            <i class="fas fa-upload"></i> Import Students
                        </a>
                    </div>
                </div>
                
                <!-- Search Bar -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row">
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by Student ID, Name or Enrollment No..." 
                                       value="<?php echo $search; ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                            <div class="col-md-2">
                                <a href="students-list.php" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Students Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-users"></i> All Students</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Student ID</th>
                                        <th>Enrollment No</th>
                                        <th>Full Name</th>
                                        <th>Course</th>
                                        <th>Department</th>
                                        <th>Semester</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($students as $student): ?>
                                    <tr>
                                        <td><?php echo $student['id']; ?></td>
                                        <td><?php echo $student['student_id']; ?></td>
                                        <td><?php echo $student['enrollment_no']; ?></td>
                                        <td><?php echo $student['full_name']; ?></td>
                                        <td><?php echo $student['course_name']; ?></td>
                                        <td><?php echo $student['dept_name']; ?></td>
                                        <td><?php echo $student['semester']; ?></td>
                                        <td><?php echo $student['phone']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $student['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($student['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="student-detail.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-student.php?id=<?php echo $student['id']; ?>" 
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteStudent(<?php echo $student['id']; ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo $search; ?>">Previous</a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo $search; ?>">Next</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteStudent(id) {
            if(confirm('Are you sure you want to delete this student?')) {
                window.location.href = 'delete-student.php?id=' + id;
            }
        }
    </script>
</body>
</html>