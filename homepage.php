<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    include 'db_connector.php';
    session_start();

    // State variables for UI
    $show_add_form = false;
    $show_find_form = false;
    $found_employee = null;
    $find_message = '';

    // Handle button clicks to show forms
    if (isset($_POST['show_add'])) {
        $show_add_form = true;
    }
    if (isset($_POST['show_find'])) {
        $show_find_form = true;
    }

    // Handle Add Employee
    if (isset($_POST['add_employee'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $division = $conn->real_escape_string($_POST['division']);
        $designation = $conn->real_escape_string($_POST['designation']);
        $salary = $conn->real_escape_string($_POST['salary']);

        $conn->query("CREATE TABLE IF NOT EXISTS employee_list (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            division VARCHAR(255),
            designation VARCHAR(255),
            salary VARCHAR(100)
        )");

        $conn->query("INSERT INTO employee_list (name, division, designation, salary)
                    VALUES ('$name', '$division', '$designation', '$salary')");
        $find_message = "<div style='color:green;'>✅ Employee Added!</div>";
        $show_add_form = false;
    }

    // Handle Find Employee
    if (isset($_POST['find_employee'])) {
        $name = $conn->real_escape_string($_POST['name']);
        $res = $conn->query("SELECT * FROM employee_list WHERE name='$name' LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $emp = $res->fetch_assoc();
            $_SESSION['found_employee_id'] = $emp['id'];
            $_SESSION['found_employee_name'] = $emp['name'];
            $_SESSION['found_employee_division'] = $emp['division'];
            $_SESSION['found_employee_designation'] = $emp['designation'];
            $_SESSION['found_employee_salary'] = $emp['salary'];
            $found_employee = $emp;
            $show_find_form = false;
        } else {
            $find_message = "<div style='color:red;'>❌ Employee not found.</div>";
            unset($_SESSION['found_employee_id']);
            $show_find_form = true;
        }
    }

    // Handle Leave Application Submission (unchanged)
    if (isset($_POST['submit_leave'])) {
        if (isset($_SESSION['found_employee_id'])) {
            $employee_id = intval($_SESSION['found_employee_id']);
        } else {
            $employee_id = isset($_POST['employee_id']) ? intval($_POST['employee_id']) : 0;
        }

        $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
        // If employee_id is not set, try to fetch it by name
        if (!$employee_id && !empty($name)) {
            $emp_res = $conn->query("SELECT id FROM employee_list WHERE name='$name' ORDER BY id DESC LIMIT 1");
            if ($emp_res && $emp_res->num_rows > 0) {
                $row = $emp_res->fetch_assoc();
                $employee_id = intval($row['id']);
            }
        }

        $leave_type = isset($_POST['leave_type']) ? $conn->real_escape_string($_POST['leave_type']) : '';
        $leave_details = isset($_POST['leave_details']) ? $conn->real_escape_string($_POST['leave_details']) : '';
        $working_days = isset($_POST['working_days']) && $_POST['working_days'] !== '' ? intval($_POST['working_days']) : 0;
        $inclusive_date_start = isset($_POST['inclusive_date_start']) ? $conn->real_escape_string($_POST['inclusive_date_start']) : '';
        $inclusive_date_end = isset($_POST['inclusive_date_end']) ? $conn->real_escape_string($_POST['inclusive_date_end']) : '';
        $date_incurred = isset($_POST['date_incurred']) ? $conn->real_escape_string($_POST['date_incurred']) : '';
        $commutation = isset($_POST['commutation']) ? $conn->real_escape_string($_POST['commutation']) : '';

        // Fetch division and designation if needed for leave_applications
        $division = '';
        $designation = '';
        $emp_info = $conn->query("SELECT division, designation FROM employee_list WHERE id='$employee_id' LIMIT 1");
        if ($emp_info && $emp_info->num_rows > 0) {
            $row = $emp_info->fetch_assoc();
            $division = $conn->real_escape_string($row['division']);
            $designation = $conn->real_escape_string($row['designation']);
        }

        $sql = "INSERT INTO leave_applications 
                (employee_id, name, division, designation, leave_type, leave_details, working_days, inclusive_date_start, inclusive_date_end, date_incurred, commutation)
                VALUES 
                ('$employee_id', '$name', '$division', '$designation', '$leave_type', '$leave_details', '$working_days', '$inclusive_date_start', '$inclusive_date_end', '$date_incurred', '$commutation')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "<div class='success-message'>Application submitted successfully!</div>";
            // Do not unset session so employee details remain visible
            // unset($_SESSION['found_employee_id']);
        } else {
            $message = "<div class='error-message'>Error: " . $conn->error . "</div>";
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application for Leave</title>
    <link rel="stylesheet" type="text/css" href="styles.css"> 
    <style>

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
        .emp-form { margin: 15px 0; padding: 12px; background: #f9f9f9; border: 1px solid #ddd; width: 350px; }
        .emp-form label { display: block; margin-top: 8px; }
        .emp-form input { width: 100%; padding: 4px 6px; margin-top: 2px; }
        .emp-details { margin: 10px 0 15px 0; padding: 10px; background: #e7f7e7; border: 1px solid #b2d8b2; width: 350px; }
    </style>
    <script>
        function showForm(form) {
            document.getElementById('addForm').style.display = (form === 'add') ? 'block' : 'none';
            document.getElementById('findForm').style.display = (form === 'find') ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <!-- Switch Buttons -->
    <form method="POST" style="margin-top:10px;">
        <button type="submit" name="show_find">Find Employee</button>
        <button type="submit" name="show_add">Add Employee</button>
    </form>

    <?php echo $find_message; ?>

    <!-- Find Employee Form -->
    <form method="POST" id="findForm" class="emp-form" style="display:<?php echo $show_find_form ? 'block' : 'none'; ?>;">
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        <button type="submit" name="find_employee">Find Employee</button>
    </form>

    <!-- Add Employee Form -->
    <form method="POST" id="addForm" class="emp-form" style="display:<?php echo $show_add_form ? 'block' : 'none'; ?>;">
        <label for="name">Name (Last, First, Middle):</label>
        <input type="text" name="name" required>
        <label for="division">Division:</label>
        <input type="text" name="division" required>
        <label for="designation">Designation:</label>
        <input type="text" name="designation" required>
        <label for="salary">Salary:</label>
        <input type="number" step="0.01" name="salary" required>
        <button type="submit" name="add_employee">Add Employee</button>
    </form>

    <!-- Show found employee details -->
    <?php if (isset($_SESSION['found_employee_id'])): ?>
        <div class="emp-details">
            <b>Name:</b> <?php echo htmlspecialchars($_SESSION['found_employee_name']); ?><br>
            <b>Division:</b> <?php echo htmlspecialchars($_SESSION['found_employee_division']); ?><br>
            <b>Designation:</b> <?php echo htmlspecialchars($_SESSION['found_employee_designation']); ?><br>
            <b>Salary:</b> <?php echo htmlspecialchars($_SESSION['found_employee_salary']); ?><br>
        </div>
    <?php endif; ?>


        <div class="tab-container">
            <div class="tab-buttons">
                <button type="button" onclick="showTab(0)">Application for Leave</button>
                <button type="button" onclick="showTab(1)">Leave Cards</button>
            </div>
            <div class="tab-content" id="tab-application">
                <div class="container">
                    <div class="left-side">
                        <form method="post" action="">
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

                        <div class="section">
                            <div class="section-title">Details of Action on Application</div>
                            <!-- to be filled -->
                        </div>
                    </div>
                </div>
            </div>

        <div class="tab-content" id="tab-leavecards">
            <div class="container">
                <div class="right-side">
                    <?php
                        $employee_id = 1;

                        // Handle update if AJAX POST
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_leave'])) {
                            $leave_id = intval($_POST['leave_id']);
                            $date_filed = $conn->real_escape_string($_POST['date_filed']);
                            $date_incurred = $conn->real_escape_string($_POST['date_incurred']);
                            $leave_type = $conn->real_escape_string($_POST['leave_type']);
                            $inclusive_date_start = $conn->real_escape_string($_POST['inclusive_date_start']);
                            $inclusive_date_end = $conn->real_escape_string($_POST['inclusive_date_end']);

                            $sql = "UPDATE leave_applications SET 
                                date_filed='$date_filed',
                                date_incurred='$date_incurred',
                                leave_type='$leave_type',
                                inclusive_date_start='$inclusive_date_start',
                                inclusive_date_end='$inclusive_date_end'
                                WHERE id=$leave_id AND employee_id=$employee_id";
                            if ($conn->query($sql)) {
                                echo 'success';
                            } else {
                                echo 'error';
                            }
                            exit;
                        }

                        // Fetch leave records
                        $query = "
                        SELECT 
                            id,
                            employee_id,
                            DATE_FORMAT(date_incurred, '%Y-%m') AS month,
                            date_filed, date_incurred, leave_type, inclusive_date_start, inclusive_date_end
                        FROM leave_applications
                        WHERE employee_id = $employee_id
                        ORDER BY date_incurred
                        ";
                        $result = $conn->query($query);
                        $leaves = [];
                        while ($row = $result->fetch_assoc()) {
                            $start = $row['inclusive_date_start'];
                            $end = $row['inclusive_date_end'];
                            if ($start && $end) {
                                $days = (strtotime($end) - strtotime($start)) / (60 * 60 * 24) + 1;
                                $days = $days > 0 ? $days : 0;
                            } else {
                                $days = '';
                            }
                            $row['days'] = $days;
                            $leaves[$row['month']][] = $row;
                        }
                    ?>

                    <style>
                        .leave-table-container {
                            overflow-x: auto;
                            margin-top: 16px;
                        }
                        table.leave-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 5px;
                            background: #fff;
                            font-size: 15px;
                        }
                        table.leave-table th, table.leave-table td {
                            border: 1px solid #bfc9d1;
                            padding: 8px 10px;
                            text-align: center;
                            vertical-align: middle;
                        }
                        table.leave-table th {
                            background: #f4f7fa;
                            font-weight: bold;
                        }
                        table.leave-table tr:nth-child(even) {
                            background: #f9fbfd;
                        }
                        table.leave-table tr.editing td {
                            background: #f0f8ff;
                        }
                        .edit-btn, .save-btn, .cancel-btn {
                            cursor: pointer;
                            color: #007bff;
                            border: none;
                            background: none;
                            font-size: 14px;
                            margin: 0 2px;
                        }
                        .edit-btn:hover, .save-btn:hover, .cancel-btn:hover {
                            text-decoration: underline;
                        }
                        input[type="date"], input[type="text"], select {
                            width: 100%;
                            box-sizing: border-box;
                            font-size: 14px;
                            padding: 2px 4px;
                        }
                        details { margin-bottom: 12px; }
                        summary { font-weight: bold; cursor: pointer; font-size: 16px; }
                    </style>
                    <script src = script.js></script>
                    <div class="leave-table-container">
                        
                    <?php
                        // Table header (shows only once)
                        echo '<table class="leave-table">
                            <tr>
                                <th rowspan="2" style="background:#c6e2e9;">Date Filed</th>
                                <th rowspan="2" style="background:#b5cdfa;">Date Incurred</th>
                                <th colspan="6" style="background:#fdf5d6; text-align:center;">Leave Incurred (Days)</th>
                                <th rowspan="2" style="background:#fdf5d6;">Remarks</th>
                                <th rowspan="2" style="background:#f4f7fa;"></th>
                            </tr>
                            <tr>
                                <th style="background:#fdf5d6;">VL</th>
                                <th style="background:#fdf5d6;">SL</th>
                                <th style="background:#fdf5d6;">SPL</th>
                                <th style="background:#fdf5d6;">FL</th>
                                <th style="background:#fdf5d6;">Solo Parent</th>
                                <th style="background:#fdf5d6;">Others</th>
                            </tr>
                        </table>';

                        $months = [
                            "2025-01","2025-02","2025-03","2025-04","2025-05","2025-06",
                            "2025-07","2025-08","2025-09","2025-10","2025-11","2025-12"
                        ];

                        foreach ($months as $month) {
                            echo "<details><summary>".date("F Y", strtotime($month))."</summary>";
                            echo '<table class="leave-table">';
                            if (isset($leaves[$month])) {
                                foreach ($leaves[$month] as $leave) {
                                    // Determine which column to fill based on leave_type
                                    $vl = $sl = $spl = $fl = $solo = $others = '';
                                    switch (strtolower($leave['leave_type'])) {
                                        case 'vacation': $vl = $leave['days']; break;
                                        case 'sick': $sl = $leave['days']; break;
                                        case 'spl': $spl = $leave['days']; break;
                                        case 'fl': $fl = $leave['days']; break;
                                        case 'solo parent': $solo = $leave['days']; break;
                                        default: $others = $leave['days']; break;
                                    }
                                    echo "<tr id='row-{$leave['id']}'>
                                        <td data-field='date_filed'>{$leave['date_filed']}</td>
                                        <td data-field='date_incurred'>{$leave['date_incurred']}</td>
                                        <td>".($vl!==''?$vl:'')."</td>
                                        <td>".($sl!==''?$sl:'')."</td>
                                        <td>".($spl!==''?$spl:'')."</td>
                                        <td>".($fl!==''?$fl:'')."</td>
                                        <td>".($solo!==''?$solo:'')."</td>
                                        <td>".($others!==''?$others:'')."</td>
                                        <td></td>
                                        <td>
                                            <button class='edit-btn' onclick='editRow({$leave['id']})'>Edit</button>
                                            <button class='save-btn' style='display:none' onclick='saveRow({$leave['id']})'>Save</button>
                                            <button class='cancel-btn' style='display:none' onclick='cancelEdit({$leave['id']})'>Cancel</button>
                                        </td>
                                        <td style='display:none' data-field='leave_type'>{$leave['leave_type']}</td>
                                        <td style='display:none' data-field='inclusive_date_start'>{$leave['inclusive_date_start']}</td>
                                        <td style='display:none' data-field='inclusive_date_end'>{$leave['inclusive_date_end']}</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='10' style='text-align:center;'>No leaves filed</td></tr>";
                            }
                            echo "</table></details>";
                        }

                        $conn->close();
                    ?>
                    </div>
                </div>
            </div>
        </div>
        </body>
    </html>
