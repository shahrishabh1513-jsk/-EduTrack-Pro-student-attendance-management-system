<?php
// student/resources/notices.php
require_once '../../includes/config.php';
redirectIfNotStudent();

// Get all active notices
$query = "SELECT * FROM notices 
          WHERE is_active = 1 
          AND (expiry_date IS NULL OR expiry_date >= CURDATE())
          ORDER BY created_at DESC";
$notices = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notices - EduTrack Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .notice-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .notice-card:hover {
            transform: translateY(-5px);
        }
        .notice-type {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .type-general { background: #17a2b8; color: white; }
        .type-exam { background: #dc3545; color: white; }
        .type-event { background: #28a745; color: white; }
        .type-holiday { background: #ffc107; color: white; }
        .type-emergency { background: #fd7e14; color: white; }
    </style>
</head>
<body>
    <?php include '../includes/student-nav.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include '../includes/student-sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Notices & Announcements</h1>
                </div>
                
                <div class="row">
                    <?php foreach($notices as $notice): ?>
                    <div class="col-md-6">
                        <div class="card notice-card">
                            <div class="card-body">
                                <span class="notice-type type-<?php echo $notice['notice_type']; ?>">
                                    <?php echo ucfirst($notice['notice_type']); ?>
                                </span>
                                <h5 class="card-title"><?php echo $notice['title']; ?></h5>
                                <p class="card-text"><?php echo nl2br($notice['content']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i> Posted on: <?php echo date('d M Y', strtotime($notice['created_at'])); ?>
                                    </small>
                                    <?php if($notice['attachment']): ?>
                                    <a href="<?php echo '../uploads/notices/' . $notice['attachment']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="fas fa-download"></i> Attachment
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($notices) == 0): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No notices available at the moment.
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>