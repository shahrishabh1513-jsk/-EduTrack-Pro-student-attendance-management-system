<?php
require_once '../../includes/config.php';

if (!isLoggedIn() || !hasRole('admin')) {
    redirect('login.php');
}

$user = getUserData($conn, $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Allocation - EduTrack Pro</title>
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
        .filter-bar { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; }
        .filter-select, .search-box { padding: 10px 15px; border: 2px solid #e9ecef; border-radius: 10px; font-family: 'Poppins', sans-serif; }
        .btn-allocate { background: #4361ee; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; }
        .seat-layout { background: white; border-radius: 20px; padding: 25px; overflow-x: auto; }
        .room-card { background: #f8f9fa; border-radius: 15px; padding: 20px; margin-bottom: 20px; }
        .room-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 15px; color: #4361ee; }
        .seat-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; }
        .seat { background: white; border: 1px solid #e9ecef; border-radius: 8px; padding: 8px; text-align: center; font-size: 0.75rem; cursor: pointer; transition: all 0.3s; }
        .seat.allocated { background: rgba(76,201,240,0.1); border-color: #4cc9f0; color: #4cc9f0; }
        .seat:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .student-list { max-height: 400px; overflow-y: auto; }
        .student-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #e9ecef; cursor: pointer; }
        .student-item:hover { background: #f8f9fa; }
        .student-item.selected { background: rgba(67,97,238,0.1); }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; border-radius: 20px; padding: 30px; max-width: 400px; width: 90%; }
        @media (max-width: 768px) { .sidebar { transform: translateX(-100%); } .sidebar.active { transform: translateX(0); } .main-content { margin-left: 0; } .seat-grid { grid-template-columns: repeat(3, 1fr); } }
        .mobile-menu-btn { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        @media (max-width: 768px) { .mobile-menu-btn { display: block; } }
        .notification { position: fixed; top: 20px; right: 20px; padding: 15px 25px; border-radius: 10px; z-index: 9999; animation: slideIn 0.3s ease; color: white; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
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
                <a href="exam-schedule.php"><i class="fas fa-calendar-alt"></i><span> Exam Schedule</span></a>
                <a href="exam-forms-verification.php"><i class="fas fa-file-alt"></i><span> Exam Forms</span></a>
                <a href="hall-ticket-generate.php"><i class="fas fa-ticket-alt"></i><span> Hall Tickets</span></a>
                <a href="seat-allocation.php" class="active"><i class="fas fa-chair"></i><span> Seat Allocation</span></a>
                <a href="results-management.php"><i class="fas fa-chart-line"></i><span> Results</span></a>
                <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i><span> Logout</span></a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="top-header">
                <button class="mobile-menu-btn" id="mobileMenuBtn"><i class="fas fa-bars"></i></button>
                <div><h2>Seat Allocation Management</h2></div>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['full_name']); ?>&background=4361ee&color=fff" alt="Profile">
                    <div><h4><?php echo htmlspecialchars($user['full_name']); ?></h4><p>Administrator</p></div>
                </div>
            </header>

            <div class="filter-bar">
                <select class="filter-select" id="examSelect"><option value="CS301">Data Structures (CS301)</option><option value="CS302">Database Management (CS302)</option><option value="CS303">Web Development (CS303)</option></select>
                <select class="filter-select" id="roomSelect"><option value="Room 101">Room 101 (Capacity: 60)</option><option value="Room 102">Room 102 (Capacity: 60)</option><option value="Room 203">Room 203 (Capacity: 50)</option><option value="Lab 3">Lab 3 (Capacity: 45)</option></select>
                <button class="btn-allocate" onclick="autoAllocate()"><i class="fas fa-magic"></i> Auto Allocate</button>
                <button class="btn-allocate" onclick="saveAllocation()" style="background:#28a745;"><i class="fas fa-save"></i> Save Allocation</button>
            </div>

            <div class="seat-layout">
                <h3 style="margin-bottom:20px;"><i class="fas fa-chair"></i> Seat Layout - <span id="roomName">Room 101</span></h3>
                <div id="seatGrid" class="seat-grid"></div>
            </div>

            <div class="seat-layout">
                <h3 style="margin-bottom:20px;"><i class="fas fa-users"></i> Students Waiting for Allocation</h3>
                <div id="studentList" class="student-list"></div>
            </div>
        </main>
    </div>

    <div id="seatModal" class="modal">
        <div class="modal-content">
            <h3>Assign Student to Seat</h3>
            <p id="seatInfo"></p>
            <select id="studentSelect" class="filter-select" style="width:100%; margin:15px 0;"></select>
            <button class="btn-allocate" onclick="assignStudent()">Assign</button>
            <button class="btn-allocate" onclick="closeModal()" style="background:#6c757d; margin-top:10px;">Cancel</button>
        </div>
    </div>

    <script>
        let currentRoom = 'Room 101';
        let seats = {};
        let students = [
            { id: 1, roll: 'IT181', name: 'Rishabh Shah', allocated: false, seat: null },
            { id: 2, roll: 'IT095', name: 'Jenish khunt', allocated: false, seat: null },
            { id: 3, roll: 'IT124', name: 'Vasu Motisarya', allocated: false, seat: null },
            { id: 4, roll: 'IT131', name: 'Hetvi Savani', allocated: false, seat: null }
        ];
        
        function initSeats(room) {
            const capacities = { 'Room 101': 60, 'Room 102': 60, 'Room 203': 50, 'Lab 3': 45 };
            const capacity = capacities[room] || 60;
            seats = {};
            for(let i = 1; i <= capacity; i++) seats[i] = null;
        }
        
        function loadSeatLayout() {
            const room = document.getElementById('roomSelect').value;
            currentRoom = room;
            document.getElementById('roomName').textContent = room;
            initSeats(room);
            
            const grid = document.getElementById('seatGrid');
            const rows = Math.ceil(Object.keys(seats).length / 6);
            let html = '';
            for(let i = 0; i < rows; i++) {
                for(let j = 1; j <= 6; j++) {
                    const seatNum = i * 6 + j;
                    if(seatNum <= Object.keys(seats).length) {
                        const student = students.find(s => s.seat === `${room}-${seatNum}`);
                        html += `<div class="seat ${student ? 'allocated' : ''}" onclick="selectSeat(${seatNum})">Seat ${seatNum}<br>${student ? student.name.substring(0, 10) : 'Empty'}</div>`;
                    }
                }
            }
            grid.innerHTML = html;
        }
        
        function loadStudentList() {
            const unallocated = students.filter(s => !s.allocated);
            const list = document.getElementById('studentList');
            if(unallocated.length === 0) {
                list.innerHTML = '<div style="text-align:center;padding:20px;">All students allocated</div>';
                return;
            }
            list.innerHTML = unallocated.map(s => `<div class="student-item" onclick="selectStudent(${s.id})"><span><strong>${s.roll}</strong> - ${s.name}</span><i class="fas fa-arrow-right"></i></div>`).join('');
        }
        
        let selectedSeatNum = null;
        function selectSeat(seatNum) { selectedSeatNum = seatNum; loadStudentsForModal(); document.getElementById('seatModal').style.display = 'flex'; document.getElementById('seatInfo').innerHTML = `Seat Number: ${seatNum}`; }
        
        function loadStudentsForModal() {
            const unallocated = students.filter(s => !s.allocated);
            const select = document.getElementById('studentSelect');
            select.innerHTML = '<option value="">Select Student</option>' + unallocated.map(s => `<option value="${s.id}">${s.roll} - ${s.name}</option>`).join('');
        }
        
        function assignStudent() {
            const studentId = document.getElementById('studentSelect').value;
            if(!studentId) { showNotification('Please select a student', 'error'); return; }
            const student = students.find(s => s.id == studentId);
            if(student && selectedSeatNum) {
                student.allocated = true;
                student.seat = `${currentRoom}-${selectedSeatNum}`;
                seats[selectedSeatNum] = student;
                closeModal();
                loadSeatLayout();
                loadStudentList();
                showNotification(`${student.name} allocated to Seat ${selectedSeatNum}`, 'success');
            }
        }
        
        function autoAllocate() {
            const unallocated = students.filter(s => !s.allocated);
            let seatNum = 1;
            for(let student of unallocated) {
                while(seats[seatNum] !== null) seatNum++;
                student.allocated = true;
                student.seat = `${currentRoom}-${seatNum}`;
                seats[seatNum] = student;
                seatNum++;
            }
            loadSeatLayout();
            loadStudentList();
            showNotification('Auto allocation completed!', 'success');
        }
        
        function saveAllocation() { showNotification('Seat allocation saved successfully!', 'success'); }
        function selectStudent(id) { const student = students.find(s => s.id === id); if(student && !student.allocated) { alert(`Allocate seat for ${student.name}`); } }
        function closeModal() { document.getElementById('seatModal').style.display = 'none'; selectedSeatNum = null; }
        function showNotification(message, type) { const n = document.createElement('div'); n.className = 'notification'; n.style.background = type === 'success' ? '#4cc9f0' : '#f72585'; n.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`; document.body.appendChild(n); setTimeout(() => n.remove(), 3000); }
        
        document.getElementById('roomSelect').addEventListener('change', loadSeatLayout);
        
        const sidebar = document.getElementById('sidebar'), mainContent = document.getElementById('mainContent'), toggleBtn = document.getElementById('toggleSidebar'), mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if(toggleBtn) toggleBtn.addEventListener('click', function() { sidebar.classList.toggle('collapsed'); mainContent.classList.toggle('expanded'); });
        if(mobileMenuBtn) mobileMenuBtn.addEventListener('click', function() { sidebar.classList.toggle('active'); });
        function handleResponsive() { if(window.innerWidth <= 768) { sidebar.classList.add('collapsed'); mainContent.classList.add('expanded'); } else { sidebar.classList.remove('collapsed', 'active'); mainContent.classList.remove('expanded'); } }
        window.addEventListener('resize', handleResponsive); handleResponsive();
        
        initSeats('Room 101');
        loadSeatLayout();
        loadStudentList();
        window.onclick = function(event) { if(event.target == document.getElementById('seatModal')) closeModal(); }
    </script>
</body>
</html>