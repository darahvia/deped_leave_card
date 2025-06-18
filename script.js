function showTab(tabIndex) {
    localStorage.setItem('activeTab', tabIndex); // save tab
    var tabs = document.querySelectorAll('.tab-content');
    var buttons = document.querySelectorAll('.tab-buttons button');
    tabs.forEach((tab, i) => {
        tab.classList.toggle('active', i === tabIndex);
        buttons[i].classList.toggle('active', i === tabIndex);
    });
}

// On page load, restore active tab
window.onload = function() {
    const savedTab = localStorage.getItem('activeTab') || 0;
    showTab(parseInt(savedTab));
};

function showForm(form) {
    document.getElementById('addForm').style.display = (form === 'add') ? 'block' : 'none';
    document.getElementById('findForm').style.display = (form === 'find') ? 'block' : 'none';
}


let current_vl = 0;
let current_sl = 0;

function getCurrentVL() {
    return current_vl;
}

function getCurrentSL() {
    return current_sl;
}

function updateVL(days) {
    // code
}

function updateSL(days) {
   // code
}

function updateLeaveBalance(leave_type, days) {
    switch (leave_type.toLowerCase()) {
        case 'vacation':
            updateVL(days);
            break;
        case 'sick':
            updateSL(days);
            break;
    }
}
