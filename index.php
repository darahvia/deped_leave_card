
<!DOCTYPE html>
<html>
<head>
    <title>Application for Leave</title>
    <link rel="stylesheet" type="text/css" href="styles.css"> 
    <style>
        /* Simple tab styles */
        .tab-container { margin: 30px auto; width: 90%; }
        .tab-buttons { display: flex; border-bottom: 1px solid #ccc; }
        .tab-buttons button {
            background: #f1f1f1;
            border: none;
            outline: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 16px;
        }
        .tab-buttons button.active { background: #fff; border-bottom: 2px solid #007bff; }
        .tab-content { display: none; padding: 20px 0; }
        .tab-content.active { display: block; }
    </style>
    <script>
        //  tab switching
        function showTab(tabIndex) {
            var tabs = document.querySelectorAll('.tab-content');
            var buttons = document.querySelectorAll('.tab-buttons button');
            tabs.forEach((tab, i) => {
                tab.classList.toggle('active', i === tabIndex);
                buttons[i].classList.toggle('active', i === tabIndex);
            });
        }
        window.onload = function() { showTab(0); };
    </script>
</head>
<body>

<form method="POST">
  <?php if (isset($_POST['find_mode'])): ?>
    <label for="name">Name:</label>
    <input type="text" name="name" required><br>
    <button type="submit" name="find_employee">Find Employee</button>

  <?php else: ?>
    <label for="name">Name (Last, First, Middle):</label>
    <input type="text" name="name" required><br>

    <label for="division">Division:</label>
    <input type="text" name="division" required><br>

    <label for="designation">Designation:</label>
    <input type="text" name="designation" required><br>

    <label for="salary">Salary:</label>
    <input type="number" step="0.01" name="salary" required><br>

    <button type="submit" name="add_employee">Add Employee</button>
  <?php endif; ?>
</form>

<!-- Switch Buttons -->
<form method="POST" style="margin-top:10px;">
  <button type="submit" name="find_mode">Switch to Find Employee</button>
  <button type="submit" name="add_mode">Switch to Add Employee</button>
</form>

<div class="tab-container">
    <div class="tab-buttons">
        <button type="button" onclick="showTab(0)">Application for Leave</button>
        <button type="button" onclick="showTab(1)">Leave Cards</button>
    </div>
    <div class="tab-content" id="tab-application">
        <div class="container">
            <div class="left-side">
                <form method="post" action="">
                    <?php
                    // Fetch distinct names and divisions for datalists
                    $name_result = $conn->query("SELECT DISTINCT name FROM leave_applications ORDER BY name ASC");
                    $division_result = $conn->query("SELECT DISTINCT division FROM leave_applications ORDER BY division ASC");

                    // Handle AJAX request for employee_id lookup
                    if (isset($_GET['get_employee_id']) && isset($_GET['name'])) {
                        $name = $conn->real_escape_string($_GET['name']);
                        $emp_res = $conn->query("SELECT employee_id FROM leave_applications WHERE name='$name' AND employee_id IS NOT NULL ORDER BY id DESC LIMIT 1");
                        if ($emp_res && $emp_res->num_rows > 0) {
                            $row = $emp_res->fetch_assoc();
                            echo intval($row['employee_id']);
                        } else {
                            echo 0;
                        }
                        exit;
                    }
                    ?>

                    <script>

                    document.getElementById('name').addEventListener('change', function() {
                        var name = this.value;
                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', '?get_employee_id=1&name=' + encodeURIComponent(name), true);
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                document.getElementById('employee_id').value = xhr.responseText.trim();
                            }
                        };
                        xhr.send();
                    });
                    </script>

                    <div class="section">
                        <div class="section-title">Details of Applications</div>
                        <div class="row">
                            <label for="leave_type">Type of Leave:</label>
                            <select id="leave_type" name="leave_type">
                                <option value="">Select</option>
                                <option value="vacation">Vacation Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="maternity">Maternity Leave</option>
                                <option value="paternity">Paternity Leave</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                        <div class="row">
                            <label for="leave_details">Details of Leave:</label>
                            <select id="leave_details" name="leave_details">
                                <option value="">Select</option>
                                <option value="within_ph">Within the Philippines</option>
                                <option value="abroad">Abroad</option>
                                <option value="emergency">Emergency</option>
                                <option value="special">Special</option>
                            </select>
                        </div>
                        <div class="row">
                            <label for="working_days">No. of Working Days Applied For:</label>
                            <input type="date" id="inclusive_date_start" name="inclusive_date_start">
                        </div>
                        <div class="row">
                            <label for="inclusive_date_start">Start Date:</label>
                            <input type="date" id="inclusive_date_start" name="inclusive_date_start"">
                        </div>
                        <div class="row">
                            <label for="inclusive_date">End Date:</label>
                            <input type="date" id="inclusive_date_end" name="inclusive_date_end">
                        </div>
                        <div class="row">
                            <label for="date_filed">Date Filed:</label>
                            <input type="date" id="date_filed" name="date_filed" placeholder="2024-07-05">
                        </div>
                        <div class="row">
                            <label for="date_incurred">Date Incurred:</label>
                            <input type="date" id="date_incurred" name="date_incurred" placeholder="2024-07-05">
                        </div>
                        <div class="row">
                            <label for="commutation">Commutation:</label>
                            <input type="text" id="commutation" name="commutation">
                        </div>
                    </div>

                    <button type="submit" name="submit_leave">Submit</button>
                </form>