// Faculty Profile JavaScript
let editMode = false;
let originalData = {};

document.addEventListener('DOMContentLoaded', function() {
    const editBtn = document.getElementById('editProfileBtn');
    if(editBtn) {
        editBtn.addEventListener('click', toggleEditMode);
    }
    
    loadProfileData();
});

function toggleEditMode() {
    const personalCard = document.getElementById('personalInfoCard');
    const editBtn = document.getElementById('editProfileBtn');
    
    if(!editMode) {
        // Enable edit mode
        editMode = true;
        personalCard.classList.add('edit-mode');
        editBtn.innerHTML = '<i class="fas fa-times"></i> Cancel Edit';
        
        // Store original values
        originalData = {
            name: document.getElementById('displayName')?.textContent,
            email: document.getElementById('displayEmail')?.textContent,
            phone: document.getElementById('displayPhone')?.textContent,
            address: document.getElementById('displayAddress')?.textContent,
            qualification: document.getElementById('displayQualification')?.textContent
        };
    } else {
        // Cancel edit
        editMode = false;
        personalCard.classList.remove('edit-mode');
        editBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
        
        // Restore original values
        if(originalData.name) document.getElementById('displayName').textContent = originalData.name;
        if(originalData.email) document.getElementById('displayEmail').textContent = originalData.email;
        if(originalData.phone) document.getElementById('displayPhone').textContent = originalData.phone;
        if(originalData.address) document.getElementById('displayAddress').textContent = originalData.address;
        if(originalData.qualification) document.getElementById('displayQualification').textContent = originalData.qualification;
    }
}

function saveProfile() {
    const newData = {
        name: document.getElementById('editName')?.value,
        email: document.getElementById('editEmail')?.value,
        phone: document.getElementById('editPhone')?.value,
        address: document.getElementById('editAddress')?.value,
        qualification: document.getElementById('editQualification')?.value
    };
    
    // Update display
    if(newData.name) document.getElementById('displayName').textContent = newData.name;
    if(newData.email) document.getElementById('displayEmail').textContent = newData.email;
    if(newData.phone) document.getElementById('displayPhone').textContent = newData.phone;
    if(newData.address) document.getElementById('displayAddress').textContent = newData.address;
    if(newData.qualification) document.getElementById('displayQualification').textContent = newData.qualification;
    
    // Update header
    const headerName = document.getElementById('headerName');
    if(headerName) headerName.textContent = newData.name;
    
    // Save to localStorage
    const facultyProfile = {
        name: newData.name,
        email: newData.email,
        phone: newData.phone,
        address: newData.address,
        qualification: newData.qualification
    };
    localStorage.setItem('facultyProfile', JSON.stringify(facultyProfile));
    
    // Exit edit mode
    editMode = false;
    document.getElementById('personalInfoCard')?.classList.remove('edit-mode');
    document.getElementById('editProfileBtn').innerHTML = '<i class="fas fa-edit"></i> Edit Profile';
    
    showNotification('Profile updated successfully!', 'success');
}

function loadProfileData() {
    const savedProfile = localStorage.getItem('facultyProfile');
    if(savedProfile) {
        const profile = JSON.parse(savedProfile);
        if(profile.name) document.getElementById('displayName').textContent = profile.name;
        if(profile.email) document.getElementById('displayEmail').textContent = profile.email;
        if(profile.phone) document.getElementById('displayPhone').textContent = profile.phone;
        if(profile.address) document.getElementById('displayAddress').textContent = profile.address;
        if(profile.qualification) document.getElementById('displayQualification').textContent = profile.qualification;
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `position:fixed;top:20px;right:20px;padding:15px 25px;background:${type === 'success' ? '#4cc9f0' : '#f72585'};color:white;border-radius:10px;z-index:9999;animation:slideIn 0.3s ease;`;
    notification.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}