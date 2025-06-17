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
                    header("Location: ?employee_id=" . $emp['id']);

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
    if ($conn->query($sql) === TRUE) {
        header("Location: ".$_SERVER['PHP_SELF']."?submitted=1");
        exit();
    }
        }

        $leave_type = isset($_POST['leave_type']) ? $conn->real_escape_string($_POST['leave_type']) : '';
        $leave_details = isset($_POST['leave_details']) ? $conn->real_escape_string($_POST['leave_details']) : '';
        $working_days = isset($_POST['working_days']) && $_POST['working_days'] !== '' ? intval($_POST['working_days']) : 0;
        $inclusive_date_start = isset($_POST['inclusive_date_start']) ? $conn->real_escape_string($_POST['inclusive_date_start']) : '';
        $inclusive_date_end = isset($_POST['inclusive_date_end']) ? $conn->real_escape_string($_POST['inclusive_date_end']) : '';
        $date_filed = isset($_POST['date_filed']) ? $conn->real_escape_string($_POST['date_filed']) : '';
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
                (employee_id, leave_type, leave_details, working_days, inclusive_date_start, inclusive_date_end, date_filed, date_incurred, commutation)
                VALUES 
                ('$employee_id','$leave_type', '$leave_details', '$working_days', '$inclusive_date_start', '$inclusive_date_end', '$date_filed', '$date_incurred', '$commutation')";
        
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
                        <?php if (isset($message)) echo $message; ?>
                        <form method="post" action="">
                            <div class="section">
                                <div class="section-title">Details of Applications</div>
                                <div class="row">
                                    <label for="leave_type">Type of Leave:</label>
                                    <select id="leave_type" name="leave_type">
                                        <option value="">Select</option>
                                        <option value="VL">Vacation Leave</option>
                                        <option value="SL">Sick Leave</option>
                                        <option value="ML">Maternity Leave</option>
                                        <option value="PL">Paternity Leave</option>
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
                                    <input type="text" id="working_days" name="working_days">
                                </div>
                                <div class="row">
                                    <label for="inclusive_date_start">Start Date:</label>
                                    <input type="date" id="inclusive_date_start" name="inclusive_date_start">
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
            
            // Get employee_id from URL if set, otherwise fallback to session or default
            if (isset($_GET['employee_id'])) {
                $employee_id = intval($_GET['employee_id']);
            } elseif (isset($_SESSION['found_employee_id'])) {
                $employee_id = intval($_SESSION['found_employee_id']);
            } else {
                $employee_id = 1;
            }

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

            // FETCH BALANCE FORWARDED
            $emp_prev_query = "SELECT balance_forwarded_vl, balance_forwarded_sl FROM employee_list WHERE id = $employee_id LIMIT 1";
            $emp_prev_result = $conn->query($emp_prev_query);
            $balance_forwarded_vl = 0;
            $balance_forwarded_sl = 0;
            if ($emp_prev_result && $emp_prev_result->num_rows > 0) {
                $bal_row = $emp_prev_result->fetch_assoc();
                $balance_forwarded_vl = floatval($bal_row['balance_forwarded_vl']);
                $balance_forwarded_sl = floatval($bal_row['balance_forwarded_sl']);
            }
            // FETCH CURRENT BALANCE
                $emp_curr_query = "SELECT current_vl, current_sl FROM leave_applications WHERE employee_id = $employee_id ORDER BY id DESC LIMIT 1";
                $emp_curr_result = $conn->query($emp_curr_query);
                if ($emp_curr_result && $emp_curr_result->num_rows > 0) {
                    $bal_row = $emp_curr_result->fetch_assoc();
                    $current_vl = floatval($bal_row['current_vl']);
                    $current_sl = floatval($bal_row['current_sl']);
                }

                $running_vl = $balance_forwarded_vl;
                $running_sl = $balance_forwarded_sl;


                // HANDLE ADD CREDITS EARNED
            if (isset($_POST['add_credits_earned'])) {
                $earned_date = $conn->real_escape_string($_POST['earned_date']);
                $earned_sl = isset($_POST['earned_sl']) ? floatval($_POST['earned_sl']) : 0;
                $earned_vl = isset($_POST['earned_vl']) ? floatval($_POST['earned_vl']) : 0;

                // Get current running balance
                $current_balance_query = "
                    SELECT 
                        COALESCE(
                            (SELECT current_vl FROM leave_applications 
                             WHERE employee_id = $employee_id 
                             ORDER BY 
                                CASE WHEN earned_date IS NOT NULL THEN earned_date ELSE date_incurred END DESC,
                                id DESC 
                             LIMIT 1), 
                            $balance_forwarded_vl
                        ) as current_vl,
                        COALESCE(
                            (SELECT current_sl FROM leave_applications 
                             WHERE employee_id = $employee_id 
                             ORDER BY 
                                CASE WHEN earned_date IS NOT NULL THEN earned_date ELSE date_incurred END DESC,
                                id DESC 
                             LIMIT 1), 
                            $balance_forwarded_sl
                        ) as current_sl
                            ";
                            $current_balance_result = $conn->query($current_balance_query);
                            if ($current_balance_result && $current_balance_result->num_rows > 0) {
                                $balance_row = $current_balance_result->fetch_assoc();
                                $new_vl = floatval($balance_row['current_vl']) + $earned_vl;
                                $new_sl = floatval($balance_row['current_sl']) + $earned_sl;
                            } else {
                                $new_vl = $balance_forwarded_vl + $earned_vl;
                                $new_sl = $balance_forwarded_sl + $earned_sl;
                            }
                            $sql = "INSERT INTO leave_applications 
                            (employee_id, leave_type, leave_details, working_days, inclusive_date_start, inclusive_date_end, date_filed, date_incurred, commutation, current_vl, current_sl, is_credit_earned, earned_date)
                            VALUES 
                            ('$employee_id', '', '', 0, NULL, NULL, NULL, NULL, '', $new_vl, $new_sl, 1, '$earned_date')";

                            $conn->query($sql);
                            echo "<meta http-equiv='refresh' content='0'>";
                            exit;
            }

                // HANDLE ADD LEAVE ROW
                if (isset($_POST['add_leave_row'])) {
                    $leave_date_filed = $conn->real_escape_string($_POST['leave_date_filed']);
                    $leave_type = $conn->real_escape_string($_POST['leave_type']);
                    $leave_date_incurred = $conn->real_escape_string($_POST['leave_date_incurred']);
                    $working_days = floatval($_POST['working_days']);

                    // Get current running balance
                    $current_balance_query = "
                        SELECT 
                            COALESCE(
                                (SELECT current_vl FROM leave_applications 
                                WHERE employee_id = $employee_id 
                                ORDER BY 
                                    CASE WHEN earned_date IS NOT NULL THEN earned_date ELSE date_incurred END DESC,
                                    id DESC 
                                LIMIT 1), 
                                $balance_forwarded_vl
                            ) as current_vl,
                            COALESCE(
                                (SELECT current_sl FROM leave_applications 
                                WHERE employee_id = $employee_id 
                                ORDER BY 
                                    CASE WHEN earned_date IS NOT NULL THEN earned_date ELSE date_incurred END DESC,
                                    id DESC 
                                LIMIT 1), 
                                $balance_forwarded_sl
                            ) as current_sl
                    ";
                    $current_balance_result = $conn->query($current_balance_query);
                    if ($current_balance_result && $current_balance_result->num_rows > 0) {
                        $balance_row = $current_balance_result->fetch_assoc();
                        $current_vl = floatval($balance_row['current_vl']);
                        $current_sl = floatval($balance_row['current_sl']);
                    } else {
                        $current_vl = $balance_forwarded_vl;
                        $current_sl = $balance_forwarded_sl;
                    }

                    // Calculate new balance based on leave type
                    if ($leave_type == 'VL') {
                        $new_vl = $current_vl - $working_days;
                        $new_sl = $current_sl;
                    } elseif ($leave_type == 'SL') {
                        $new_vl = $current_vl;
                        $new_sl = $current_sl - $working_days;
                    } else {
                        $new_vl = $current_vl;
                        $new_sl = $current_sl;
                    }

                    $sql = "INSERT INTO leave_applications 
                    (employee_id, leave_type, leave_details, working_days, inclusive_date_start, inclusive_date_end, date_filed, date_incurred, commutation, is_credit_earned, current_vl, current_sl, earned_date)
                    VALUES 
                    ('$employee_id','$leave_type', '', $working_days, NULL, NULL, '$leave_date_filed', '$leave_date_incurred', '', 0, $new_vl, $new_sl, NULL)";

                    $conn->query($sql);
                    echo "<meta http-equiv='refresh' content='0'>";
                    exit;
                }

                // FETCH ALL RECORDS IN CHRONOLOGICAL ORDER
                $all_records_query = "
                    SELECT *, 
                        CASE 
                            WHEN earned_date IS NOT NULL THEN earned_date 
                            ELSE date_incurred 
                        END as sort_date
                    FROM leave_applications 
                    WHERE employee_id = $employee_id 
                    ORDER BY sort_date ASC, id ASC
                ";
                $all_records_result = $conn->query($all_records_query);
                $all_records = [];
                if ($all_records_result) {
                    while ($row = $all_records_result->fetch_assoc()) {
                        $all_records[] = $row;
                    }
                }


                // FETCH ALL CREDITS EARNED ROWS
                $credits_earned_query = "SELECT * FROM leave_applications WHERE employee_id = $employee_id AND is_credit_earned = 1 ORDER BY earned_date";
                $credits_earned_result = $conn->query($credits_earned_query);
                $credits_earned_rows = [];
                if ($credits_earned_result) {
                    while ($row = $credits_earned_result->fetch_assoc()) {
                        $credits_earned_rows[] = $row;
                    }
                }

                // FETCH ALL LEAVE ROWS (NO CREDITS EARNED)
                $leave_rows_query = "SELECT * FROM leave_applications WHERE employee_id = $employee_id AND (is_credit_earned IS NULL OR is_credit_earned = 0) ORDER BY date_incurred";
                $leave_rows_result = $conn->query($leave_rows_query);
                $leave_rows = [];
                if ($leave_rows_result) {
                    while ($row = $leave_rows_result->fetch_assoc()) {
                        $leave_rows[] = $row;
                    }
                }
            ?>
            <script src="script.js"></script>
            <div class="leave-table-container">
            <?php

            echo '<br><table class="leave-table" style="margin-top:30px;">
            <tr>
                <th style="background:#e0e0e0;">Date</th>
                <th style="background:#e0e0e0;">Earned SL</th>
                <th style="background:#e0e0e0;">Earned VL</th>
                <th style="background:#c6e2e9;">Date Filed</th>
                <th style="background:#b5cdfa;">Date Incurred</th>
                <th colspan="6" style="background:#fdf5d6; text-align:center;">Leave Incurred (Days)</th>
                <th style="background:#fdf5d6;">Remarks</th>
                <th style="background:#e2f7d6;">Current VL</th>
                <th style="background:#e2f7d6;">Current SL</th>
            </tr>
            <tr>
                <td colspan="3" style="background:#f7f7f7;"><b>Balance Forwarded</b></td>
                <td colspan="9"></td>
                <td style="background:#e2f7d6;">' . number_format($balance_forwarded_vl, 2) . '</td>
                <td style="background:#e2f7d6;">' . number_format($balance_forwarded_sl, 2) . '</td>
            </tr>
            ';

            // Display all records in chronological order
            foreach ($all_records as $row) {
                if (isset($row['is_credit_earned']) && $row['is_credit_earned'] == 1) {
                    // Credits earned row
                    $earned_sl = 1.25; // You can make this dynamic if needed
                    $earned_vl = 1.25; // You can make this dynamic if needed
                    $earned_date = isset($row['earned_date']) ? $row['earned_date'] : '';
                    echo '<tr>
                        <td>'.htmlspecialchars($earned_date).'</td>
                        <td>'.number_format($earned_sl,2).'</td>
                        <td>'.number_format($earned_vl,2).'</td>
                        <td colspan="9" style="text-align:center;">Credits Earned</td>
                        <td style="background:#e2f7d6;">'.number_format($row['current_vl'],2).'</td>
                        <td style="background:#e2f7d6;">'.number_format($row['current_sl'],2).'</td>
                    </tr>';
                } else {
                    // Leave application row
                    $leave_type = $row['leave_type'];
                    $leave_days = isset($row['working_days']) ? floatval($row['working_days']) : 0;
                    $vl_incurred = $sl_incurred = 0;
                    if ($leave_type == 'VL') {
                        $vl_incurred = $leave_days;
                    } elseif ($leave_type == 'SL') {
                        $sl_incurred = $leave_days;
                    }
                    echo '<tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>'.htmlspecialchars($row['date_filed']).'</td>
                        <td>'.htmlspecialchars($row['date_incurred']).'</td>
                        <td>'.($vl_incurred ? number_format($vl_incurred,2) : '').'</td>
                        <td>'.($sl_incurred ? number_format($sl_incurred,2) : '').'</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td style="background:#e2f7d6;">'.number_format($row['current_vl'] ?? 0, 2).'</td>
                        <td style="background:#e2f7d6;">'.number_format($row['current_sl'] ?? 0, 2).'</td>

                    </tr>';
                }
            }
            echo '</table>';
            ?>

             <!-- Add Credits Earned Button and Form -->
            <button onclick="document.getElementById('creditsEarnedForm').style.display='block';">Add Credits Earned</button>
            <div id="creditsEarnedForm" style="display:none; margin:10px 0; padding:10px; background:#f9f9f9; border:1px solid #ccc;">
                <form method="POST">
                    <label>Date (Month Earned): <input type="date" name="earned_date" required></label>
                    <label>Earned SL: <input type="number" step="0.01" name="earned_sl" value="1.25" required></label>
                    <label>Earned VL: <input type="number" step="0.01" name="earned_vl" value="1.25" required></label>
                    <button type="submit" name="add_credits_earned">Add</button>
                    <button type="button" onclick="document.getElementById('creditsEarnedForm').style.display='none';">Cancel</button>
                </form>
            </div>

            <!-- Add Leave Row Button and Form -->
            <button onclick="document.getElementById('leaveRowForm').style.display='block';">Add Leave Row</button>
            <div id="leaveRowForm" style="display:none; margin:10px 0; padding:10px; background:#f9f9f9; border:1px solid #ccc;">
                <form method="POST">
                    <label>Date Filed: <input type="date" name="leave_date_filed" required></label>
                    <label>Leave Type: 
                        <select name="leave_type" required>
                            <option value="VL">Vacation Leave</option>
                            <option value="SL">Sick Leave</option>
                        </select>
                    </label>
                    <label>Date Incurred: <input type="date" name="leave_date_incurred" required></label>
                    <label>Working Days: <input type="number" step="0.01" name="working_days" required></label>
                    <button type="submit" name="add_leave_row">Add</button>
                    <button type="button" onclick="document.getElementById('leaveRowForm').style.display='none';">Cancel</button>
                </form>
            </div>
            <script>
                // Optional: Hide forms on page load if JS enabled
                document.getElementById('creditsEarnedForm').style.display = 'none';
                document.getElementById('leaveRowForm').style.display = 'none';
            </script>
            </div>
            </div>
            </div>
        </div>
        </body>
    </html>
