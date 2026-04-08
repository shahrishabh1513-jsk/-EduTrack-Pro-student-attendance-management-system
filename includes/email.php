<?php
/**
 * EduTrack Pro - Email Functions
 * Handles email sending using PHPMailer
 */

require_once BASE_PATH . 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using PHPMailer
 */
function sendMail($to, $subject, $body, $is_html = true, $attachments = []) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SITE_EMAIL, SITE_NAME);
        $mail->addAddress($to);
        
        // Attachments
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }
        }
        
        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if (!$is_html) {
            $mail->AltBody = $body;
        }
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Send welcome email to new student
 */
function sendStudentWelcomeEmail($student) {
    $subject = "Welcome to " . SITE_NAME . " - Student Portal Access";
    
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 10px 20px; background: #4361ee; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Welcome to " . SITE_NAME . "</h2>
                </div>
                <div class='content'>
                    <h3>Dear " . htmlspecialchars($student['full_name']) . ",</h3>
                    <p>Your student account has been created successfully. You can now access the student portal to manage your academic journey.</p>
                    <p><strong>Your Login Credentials:</strong></p>
                    <ul>
                        <li><strong>Username:</strong> " . $student['username'] . "</li>
                        <li><strong>Roll Number:</strong> " . $student['roll_number'] . "</li>
                        <li><strong>Course:</strong> " . $student['course'] . "</li>
                        <li><strong>Semester:</strong> " . $student['semester'] . "</li>
                    </ul>
                    <p>Click the button below to login:</p>
                    <p style='text-align: center;'>
                        <a href='" . SITE_URL . "login.php' class='button'>Login to Portal</a>
                    </p>
                    <p>If you have any questions, please contact the administration.</p>
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
    
    return sendMail($student['email'], $subject, $body);
}

/**
 * Send welcome email to new faculty
 */
function sendFacultyWelcomeEmail($faculty) {
    $subject = "Welcome to " . SITE_NAME . " - Faculty Portal Access";
    
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Welcome to " . SITE_NAME . "</h2>
                </div>
                <div class='content'>
                    <h3>Dear " . htmlspecialchars($faculty['full_name']) . ",</h3>
                    <p>Your faculty account has been created successfully. You can now access the faculty portal to manage your classes and students.</p>
                    <p><strong>Your Login Credentials:</strong></p>
                    <ul>
                        <li><strong>Username:</strong> " . $faculty['username'] . "</li>
                        <li><strong>Employee ID:</strong> " . $faculty['employee_id'] . "</li>
                        <li><strong>Department:</strong> " . $faculty['department'] . "</li>
                    </ul>
                    <p>Click the button below to login:</p>
                    <p style='text-align: center;'>
                        <a href='" . SITE_URL . "login.php' style='display: inline-block; padding: 10px 20px; background: #4361ee; color: white; text-decoration: none; border-radius: 5px;'>Login to Portal</a>
                    </p>
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
    
    return sendMail($faculty['email'], $subject, $body);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $token) {
    $reset_link = SITE_URL . "reset-password.php?token=" . $token;
    
    $subject = "Password Reset Request - " . SITE_NAME;
    
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 10px 20px; background: #4361ee; color: white; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>You requested to reset your password. Click the button below to reset it:</p>
                    <p style='text-align: center;'>
                        <a href='$reset_link' class='button'>Reset Password</a>
                    </p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you did not request this, please ignore this email.</p>
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
    
    return sendMail($email, $subject, $body);
}

/**
 * Send leave approval notification
 */
function sendLeaveApprovalEmail($student_email, $student_name, $leave) {
    $subject = "Leave Application " . ucfirst($leave['status']) . " - " . SITE_NAME;
    
    $status_color = $leave['status'] == 'approved' ? '#4cc9f0' : '#f72585';
    $status_text = strtoupper($leave['status']);
    
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .status { color: $status_color; font-weight: bold; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Leave Application Update</h2>
                </div>
                <div class='content'>
                    <h3>Dear " . htmlspecialchars($student_name) . ",</h3>
                    <p>Your leave application has been <span class='status'>$status_text</span>.</p>
                    <p><strong>Leave Details:</strong></p>
                    <ul>
                        <li><strong>Type:</strong> " . ucfirst($leave['type']) . "</li>
                        <li><strong>From:</strong> " . date('d/m/Y', strtotime($leave['from_date'])) . "</li>
                        <li><strong>To:</strong> " . date('d/m/Y', strtotime($leave['to_date'])) . "</li>
                        <li><strong>Days:</strong> " . $leave['days'] . "</li>
                    </ul>
                    " . (!empty($leave['remarks']) ? "<p><strong>Remarks:</strong> " . htmlspecialchars($leave['remarks']) . "</p>" : "") . "
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
    
    return sendMail($student_email, $subject, $body);
}

/**
 * Send exam form verification email
 */
function sendExamFormVerificationEmail($student_email, $student_name, $form) {
    $subject = "Exam Form Verification - " . SITE_NAME;
    
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Exam Form Verification</h2>
                </div>
                <div class='content'>
                    <h3>Dear " . htmlspecialchars($student_name) . ",</h3>
                    <p>Your exam form has been verified successfully.</p>
                    <p><strong>Exam Details:</strong></p>
                    <ul>
                        <li><strong>Semester:</strong> " . $form['semester'] . "</li>
                        <li><strong>Academic Year:</strong> " . $form['academic_year'] . "</li>
                        <li><strong>Total Fee Paid:</strong> ₹" . number_format($form['total_fee'], 2) . "</li>
                    </ul>
                    <p>You can now download your hall ticket from the student portal.</p>
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
    
    return sendMail($student_email, $subject, $body);
}

/**
 * Send result declaration email
 */
function sendResultEmail($student_email, $student_name, $semester, $sgpa) {
    $subject = "Results Declared - Semester " . $semester . " - " . SITE_NAME;
    
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .sgpa { font-size: 24px; color: #4361ee; font-weight: bold; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Results Declared</h2>
                </div>
                <div class='content'>
                    <h3>Dear " . htmlspecialchars($student_name) . ",</h3>
                    <p>Your results for Semester " . $semester . " have been declared.</p>
                    <p><strong>Your SGPA:</strong> <span class='sgpa'>" . $sgpa . "</span></p>
                    <p>Login to the student portal to view your detailed results.</p>
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
    
    return sendMail($student_email, $subject, $body);
}

/**
 * Send grievance resolution email
 */
function sendGrievanceResolutionEmail($student_email, $student_name, $grievance) {
    $subject = "Grievance Update - " . SITE_NAME;
    
    $body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4361ee; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f8f9fa; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Grievance Update</h2>
                </div>
                <div class='content'>
                    <h3>Dear " . htmlspecialchars($student_name) . ",</h3>
                    <p>Your grievance has been resolved.</p>
                    <p><strong>Grievance Details:</strong></p>
                    <ul>
                        <li><strong>Title:</strong> " . htmlspecialchars($grievance['title']) . "</li>
                        <li><strong>Category:</strong> " . ucfirst($grievance['category']) . "</li>
                    </ul>
                    <p><strong>Resolution:</strong> " . htmlspecialchars($grievance['resolution']) . "</p>
                    <p>Best regards,<br>" . SITE_NAME . " Team</p>
                </div>
                <div class='footer'>
                    &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
    ";
    
    return sendMail($student_email, $subject, $body);
}

/**
 * Send bulk email to students
 */
function sendBulkEmailToStudents($subject, $message, $course = null, $semester = null) {
    global $conn;
    
    $where = "1=1";
    if ($course) {
        $where .= " AND s.course = '$course'";
    }
    if ($semester) {
        $where .= " AND s.semester = $semester";
    }
    
    $query = "SELECT u.email, u.full_name 
              FROM students s 
              JOIN users u ON s.user_id = u.id 
              WHERE $where AND u.status = 'active'";
    
    $result = mysqli_query($conn, $query);
    $success_count = 0;
    
    while ($student = mysqli_fetch_assoc($result)) {
        $personalized_message = str_replace('{name}', $student['full_name'], $message);
        if (sendMail($student['email'], $subject, $personalized_message)) {
            $success_count++;
        }
    }
    
    return $success_count;
}

/**
 * Send bulk email to faculty
 */
function sendBulkEmailToFaculty($subject, $message, $department = null) {
    global $conn;
    
    $where = "1=1";
    if ($department) {
        $where .= " AND f.department = '$department'";
    }
    
    $query = "SELECT u.email, u.full_name 
              FROM faculty f 
              JOIN users u ON f.user_id = u.id 
              WHERE $where AND u.status = 'active'";
    
    $result = mysqli_query($conn, $query);
    $success_count = 0;
    
    while ($faculty = mysqli_fetch_assoc($result)) {
        $personalized_message = str_replace('{name}', $faculty['full_name'], $message);
        if (sendMail($faculty['email'], $subject, $personalized_message)) {
            $success_count++;
        }
    }
    
    return $success_count;
}
?>