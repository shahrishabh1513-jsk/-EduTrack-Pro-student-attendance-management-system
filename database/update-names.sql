USE edutrack_db;

-- Update Users table
UPDATE users SET full_name = 'Jenish Khunt' WHERE username = 'jenish';
UPDATE users SET full_name = 'Rishabh Shah' WHERE username = 'rishabh';
UPDATE users SET full_name = 'Hetvi Savani' WHERE username = 'hetvi';
UPDATE users SET full_name = 'Vasu Motsary' WHERE username = 'vasu';

-- Update Students table
UPDATE students SET father_name = 'Raj Khunt', mother_name = 'Neha Khunt' WHERE roll_number = 'IT095';
UPDATE students SET father_name = 'Rajesh Shah', mother_name = 'Sunita Shah' WHERE roll_number = 'IT181';
UPDATE students SET father_name = 'Rajesh Savani', mother_name = 'Anita Savani' WHERE roll_number = 'IT131';
UPDATE students SET father_name = 'Rajesh Motsary', mother_name = 'Priya Motsary' WHERE roll_number = 'IT124';

-- Verify updates
SELECT u.username, u.full_name, s.roll_number, s.father_name, s.mother_name 
FROM users u 
JOIN students s ON u.id = s.user_id 
WHERE u.username IN ('jenish', 'rishabh', 'hetvi', 'vasu');