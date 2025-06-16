<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connector.php';

// Create table if not exists
$table_sql = "CREATE TABLE IF NOT EXISTS leave_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    division VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    date_of_filing DATE NOT NULL,
    designation VARCHAR(255) NOT NULL,
    salary VARCHAR(100),
    leave_type VARCHAR(100),
    leave_details VARCHAR(100),
    working_days INT,
    inclusive_dates VARCHAR(255),
    commutation VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($table_sql);

if (isset($_POST['submit_leave'])) {
    $division = $conn->real_escape_string($_POST['division']);
    $name = $conn->real_escape_string($_POST['name']);
    $date_of_filing = $conn->real_escape_string($_POST['date_of_filing']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $salary = $conn->real_escape_string($_POST['salary']);
    $leave_type = $conn->real_escape_string($_POST['leave_type']);
    $leave_details = $conn->real_escape_string($_POST['leave_details']);
    $working_days = $conn->real_escape_string($_POST['working_days']);
    $inclusive_dates = $conn->real_escape_string($_POST['inclusive_dates']);
    $commutation = $conn->real_escape_string($_POST['commutation']);

    $sql = "INSERT INTO leave_applications 
            (division, name, date_of_filing, designation, salary, leave_type, leave_details, working_days, inclusive_dates, commutation)
            VALUES 
            ('$division', '$name', '$date_of_filing', '$designation', '$salary', '$leave_type', '$leave_details', '$working_days', '$inclusive_dates', '$commutation')";
    
    if ($conn->query($sql) === TRUE) {
        $message = "<div class='success-message'>Application submitted successfully!</div>";
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
        // Simple tab switching
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
                        <div class="section-title">Application for Leave</div>
                        <div class="row">
                            <label for="name">Name (Last, First, Middle):</label>
                            <input type="text" id="name" name="name" list="name_list" autocomplete="off" required>
                            <datalist id="name_list">
                                <?php
                                // Fetch distinct name values from the database
                                $name_result = $conn->query("SELECT DISTINCT name FROM leave_applications ORDER BY name ASC");
                                if ($name_result && $name_result->num_rows > 0) {
                                    while ($row = $name_result->fetch_assoc()) {
                                        $name_val = htmlspecialchars($row['name']);
                                        echo "<option value=\"$name_val\">";
                                    }
                                }
                                ?>
                            </datalist>
                        </div>
                        <div class="row">
                            <label for="division">Division:</label>
                            <input type="text" id="division" name="division" list="division_list" autocomplete="off" required>
                            <datalist id="division_list">
                                <?php
                                // Fetch distinct division values from the database
                                $division_result = $conn->query("SELECT DISTINCT division FROM leave_applications ORDER BY division ASC");
                                if ($division_result && $division_result->num_rows > 0) {
                                    while ($row = $division_result->fetch_assoc()) {
                                        $division_val = htmlspecialchars($row['division']);
                                        echo "<option value=\"$division_val\">";
                                    }
                                }
                                ?>
                            </datalist>
                        </div>
                        <div class="row">
                            <label for="date_of_filing">Date of Filing:</label>
                            <input type="date" id="date_of_filing" name="date_of_filing" required>
                        </div>
                        <div class="row">
                            <label for="designation">Designation:</label>
                            <input type="text" id="designation" name="designation" required>
                        </div>
                        <div class="row">
                            <label for="salary">Salary:</label>
                            <input type="text" id="salary" name="salary">
                        </div>
                    </div>

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
                            <input type="number" id="working_days" name="working_days" min="1">
                        </div>
                        <div class="row">
                            <label for="inclusive_dates">Inclusive Dates:</label>
                            <input type="text" id="inclusive_dates" name="inclusive_dates" placeholder="e.g. 2024-07-01 to 2024-07-05">
                        </div>
                        <div class="row">
                            <label for="commutation">Commutation:</label>
                            <input type="text" id="commutation" name="commutation">
                        </div>
                    </div>

                    <button type="submit" name="submit_leave">Submit</button>
                </form>

                <?php
                if (isset($message)) {
                    echo $message;
                }
                ?>

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
 
            </div>
        </div>
    </div>
</div>
</body>
</html>
