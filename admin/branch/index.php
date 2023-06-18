<!DOCTYPE html>
<html>
<head>
    <title>Branch Income Summary</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Include custom CSS for styling -->
    <style>
        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .buttons {
            margin-top: 10px;
        }

        .buttons button {
            margin-right: 10px;
        }

        .table-wrapper {
            margin-bottom: 30px;
        }

        .total-row {
            font-weight: bold;
        }

        .btn-remove {
            padding: 0.375rem 0.5rem;
        }
    </style>
</head>
<body>
    <?php
    // Assuming you have a MySQL database set up with the following credentials
    $host = 'localhost';
    $db = 'spa_data';
    $user = 'root';
    $password = '';

    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the backup button is clicked
    if (isset($_POST['backup'])) {
        // Create a backup table if it doesn't exist
        // Get the current date and time in Sri Lanka
        date_default_timezone_set('Asia/Colombo');
        $date = date('Y_m_d');
        $time = date('His');
        $backupTable = "spa_data_{$date}_{$time}"; // Generate backup table name with current date and time
        $stmt = $conn->query("CREATE TABLE IF NOT EXISTS $backupTable LIKE spa_data");

        // Copy the data from the spa_data table to the backup table
        $stmt = $conn->query("INSERT INTO $backupTable SELECT * FROM spa_data");

        // Truncate (delete all data) from the spa_data table
        $stmt = $conn->query("TRUNCATE TABLE spa_data");

        // Display the success alert message
        echo '<script>alert("The table has been deleted.");</script>';
        echo '<script>window.location.href = window.location.href;</script>'; // Refresh the page
        exit; // Stop further execution of the script
    }

    // Check if the remove button is clicked
    if (isset($_POST['remove'])) {
        $rowId = $_POST['rowId'];
        $stmt = $conn->prepare("DELETE FROM spa_data WHERE id = :rowId");
        $stmt->bindValue(':rowId', $rowId);
        $stmt->execute();
        echo '<script>alert("The row has been removed.");</script>';
        echo '<script>window.location.href = window.location.href;</script>'; // Refresh the page
        exit; // Stop further execution of the script
    }

    // Retrieve the saved form data grouped by branch and date
    $tableName = 'spa_data'; // Change this to your actual table name
    $stmt = $conn->query("SELECT DISTINCT branch, DATE(date) AS date FROM $tableName ORDER BY in_time, date DESC");
    $dataRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="container">
        <div class="header">
            <h1>Branch Income Summary</h1>
            <p>Manage income data and generate summaries</p>
        </div>

        <div class="row">
            <div class="col-md-4 offset-md-4">
                <div class="form-group">
                    <label for="dateFilter">Filter by Date:</label>
                    <input type="date" id="dateFilter" class="form-control" onchange="applyDateFilter(this.value)">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 offset-md-4">
                <div class="buttons">
                    <button type="button" class="btn btn-primary" onclick="deleteData()">Delete Data</button>
                    <button type="button" class="btn btn-primary" onclick="location.reload();">Refresh</button>
                </div>
            </div>
        </div>

        <!-- Create tabs for each branch -->
        <ul class="nav nav-tabs mt-4" id="branchTabs" role="tablist">
            <?php foreach ($dataRows as $index => $dataRow) {
                $branch = $dataRow['branch'];
                $date = $dataRow['date'];
                $tabId = "tab-$index";
                $panelId = "panel-$index";

                // Set the active class for the first tab
                $activeClass = ($index === 0) ? 'active' : '';

                echo "<li class='nav-item'>";
                echo "<a class='nav-link $activeClass' id='$tabId' data-toggle='tab' href='#$panelId' role='tab' aria-controls='$panelId' aria-selected='true'>$branch</a>";
                echo "</li>";
            } ?>
        </ul>

        <!-- Create tab content for each branch -->
        <div class="tab-content mt-4" id="branchTabsContent">
            <?php foreach ($dataRows as $index => $dataRow) {
                $branch = $dataRow['branch'];
                $date = $dataRow['date'];
                $table = "spa_data_$branch"; // Table name with branch prefix

                // Retrieve the data for the specific branch and date
                $filterDate = $_GET['filterDate'] ?? '';
                $dateCondition = ($filterDate !== '') ? "AND DATE(date) = '$filterDate'" : '';
                $stmt = $conn->query("SELECT * FROM $tableName WHERE branch = '$branch' $dateCondition");
                $branchDataRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $panelId = "panel-$index";

                // Set the active class for the first tab content
                $activeClass = ($index === 0) ? 'active show' : '';

                echo "<div class='tab-pane fade $activeClass' id='$panelId' role='tabpanel' aria-labelledby='$tabId'>";
                echo "<div class='table-wrapper'>";
                echo "<div class='table-responsive'>";
                echo "<table class='table table-striped table-bordered'>";
                echo "<h2 class='text-center'>Branch: $branch - Date: $date</h2>";
                echo "<thead class='thead-dark'>";
                echo "<tr>";
                echo "<th>Name</th>";
                echo "<th>Date</th>";
                echo "<th>In Time</th>";
                echo "<th>Out Time</th>";
                echo "<th>Service Type</th>";
                echo "<th>Amount</th>";
                echo "<th>Action</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                $totalAmount = 0;

                foreach ($branchDataRows as $branchDataRow) {
                    echo "<tr>";
                    echo "<td>{$branchDataRow['name']}</td>";
                    echo "<td>{$branchDataRow['date']}</td>";
                    echo "<td>{$branchDataRow['in_time']}</td>";
                    echo "<td>{$branchDataRow['out_time']}</td>";
                    echo "<td>{$branchDataRow['service_type']}</td>";
                    echo "<td>{$branchDataRow['amount']}</td>";
                    echo "<td><button class='btn btn-danger btn-remove' onclick='removeRow({$branchDataRow['id']})'>Remove</button></td>";
                    echo "</tr>";
                    $totalAmount += $branchDataRow['amount'];
                }

                echo "</tbody>";
                echo "<tfoot>";
                echo "<tr class='total-row'>";
                echo "<td colspan='5'>Total Amount</td>";
                echo "<td>$totalAmount</td>";
                echo "<h2 class='text-center'>Total Amount: $totalAmount </h2>";
                echo "<td></td>";
                echo "</tr>";
                echo "</tfoot>";
                echo "</table>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            } ?>
        </div>
    </div>

    <!-- Include Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- JavaScript for filtering by date -->
    <script>
        function applyDateFilter(date) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('filterDate', date);
            window.location.href = currentUrl.href;
        }
    </script>
<script>
    // JavaScript code to handle remove button
    function removeRow(adminId) {
        if (confirm("Are you sure you want to remove this admin?")) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'adminId';
            input.value = adminId;

            var button = document.createElement('button');
            button.type = 'submit';
            button.name = 'remove';
            button.style.display = 'none';

            form.appendChild(input);
            form.appendChild(button);

            document.body.appendChild(form);
            button.click();

            document.body.removeChild(form);
        }
    }

    // Attach event listeners to the remove buttons
    var removeButtons = document.getElementsByClassName('btn-remove');
    for (var i = 0; i < removeButtons.length; i++) {
        removeButtons[i].addEventListener('click', function(event) {
            event.preventDefault();
            removeRow(this.dataset.adminId);
        });
    }

    // JavaScript code to handle update button
    function updateRow(adminId, firstname, lastname, dateAdded, dateUpdated) {
        // Get the form fields
        var updateFirstnameField = document.getElementById('updateFirstname');
        var updateLastnameField = document.getElementById('updateLastname');
        var updateDateAddedField = document.getElementById('updateDateAdded');
        var updateDateUpdatedField = document.getElementById('updateDateUpdated');

        // Set the field values with admin information
        updateFirstnameField.value = firstname;
        updateLastnameField.value = lastname;
        updateDateAddedField.value = dateAdded;
        updateDateUpdatedField.value = dateUpdated;

        // Open the update modal
        $('#updateAdminModal').modal('show');
    }

    // Attach event listeners to the update buttons
    var updateButtons = document.getElementsByClassName('btn-update');
    for (var i = 0; i < updateButtons.length; i++) {
        updateButtons[i].addEventListener('click', function(event) {
            event.preventDefault();
            var adminId = this.dataset.adminId;
            var firstname = this.parentNode.parentNode.getElementsByTagName('td')[0].innerText;
            var lastname = this.parentNode.parentNode.getElementsByTagName('td')[1].innerText;
            var dateAdded = this.parentNode.parentNode.getElementsByTagName('td')[5].innerText;
            var dateUpdated = this.parentNode.parentNode.getElementsByTagName('td')[6].innerText;
            updateRow(adminId, firstname, lastname, dateAdded, dateUpdated);
        });
    }
</script>






</body>
</html>
