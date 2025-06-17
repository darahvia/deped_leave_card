
function showTab(tabIndex) {
    var tabs = document.querySelectorAll('.tab-content');
    var buttons = document.querySelectorAll('.tab-buttons button');
    tabs.forEach((tab, i) => {
        tab.classList.toggle('active', i === tabIndex);
        buttons[i].classList.toggle('active', i === tabIndex);
    });
}

function editRow(rowId) {
    var row = document.getElementById('row-' + rowId);
    var cells = row.querySelectorAll('td[data-field]');
    row.classList.add('editing');
    cells.forEach(function(cell) {
        var field = cell.getAttribute('data-field');
        var value = cell.innerText;
        if (field === 'leave_type') {
            var select = document.createElement('select');
            ['vacation','sick','spl','fl','solo parent','others'].forEach(function(opt) {
                var option = document.createElement('option');
                option.value = opt;
                option.text = opt.charAt(0).toUpperCase() + opt.slice(1);
                if (value.toLowerCase() === opt) option.selected = true;
                select.appendChild(option);
            });
            cell.innerHTML = '';
            cell.appendChild(select);
        } else if (field === 'date_filed' || field === 'date_incurred' || field === 'inclusive_date_start' || field === 'inclusive_date_end') {
            var input = document.createElement('input');
            input.type = 'date';
            input.value = value;
            cell.innerHTML = '';
            cell.appendChild(input);
        }
    });
    row.querySelector('.edit-btn').style.display = 'none';
    row.querySelector('.save-btn').style.display = '';
    row.querySelector('.cancel-btn').style.display = '';
}

function cancelEdit(rowId) {
    location.reload(); // simplest way to revert
}

function saveRow(rowId) {
    var row = document.getElementById('row-' + rowId);
    var data = {
        update_leave: 1,
        leave_id: rowId
    };
    row.querySelectorAll('td[data-field]').forEach(function(cell) {
        var field = cell.getAttribute('data-field');
        var value;
        if (cell.children.length && (cell.children[0].tagName === 'INPUT' || cell.children[0].tagName === 'SELECT')) {
            value = cell.children[0].value;
        } else {
            value = cell.innerText;
        }
        data[field] = value;
    });
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.responseText.trim() === 'success') {
            location.reload();
        } else {
            alert('Update failed');
        }
    };
    var params = Object.keys(data).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
    }).join('&');
    xhr.send(params);
}