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
    echo '<script>alert("The table has been Deleted.");</script>';
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

// Display the header section
echo '<div class="header">';
echo '<h1>Income Summary</h1>';
echo '<p>Manage income data and generate summaries</p>';
echo '<div class="buttons">';
echo '<form method="post" action="">';
echo '<button type="submit" name="backup" class="btn btn-primary">Delete Data</button>';
echo '</form>';
echo '<button type="button" class="btn btn-primary" onclick="location.reload();">Refresh</button>';
echo '</div>';
echo '</div>';

// Create a separate table for each branch and date combination
foreach ($dataRows as $dataRow) {
    $branch = $dataRow['branch'];
    $date = $dataRow['date'];
    $table = "spa_data_$branch"; // Table name with branch prefix

    // Retrieve the data for the specific branch and date
    $stmt = $conn->query("SELECT * FROM $tableName WHERE branch = '$branch' AND DATE(date) = '$date'");
    $branchDataRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the table for the specific branch and date
    echo "<h2>Branch: $branch - Date: $date</h2>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-striped table-bordered'>";
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
        echo "<td><button class='btn btn-danger btn-remove' data-id='{$branchDataRow['id']}'>Remove</button></td>";
        echo "</tr>";
        $totalAmount += $branchDataRow['amount'];
    }
    echo "</tbody>";
    echo "<tfoot>";
    echo "<tr class='total-row'>";
    echo "<td colspan='5' style='text-align: right;'>Total:</td>";
    echo "<td>Rs. $totalAmount.00</td>";
    echo "<td></td>";
    echo "</tr>";
    echo "</tfoot>";
    echo "</table>";
    echo "</div>";
}
?>

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
</style>

<!-- Include jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Custom script for removing rows -->
<script>
    $(document).ready(function() {
        $('.btn-remove').on('click', function() {
            if (confirm('Are you sure you want to remove this row?')) {
                var rowId = $(this).data('id');
                $.ajax({
                    url: window.location.href,
                    method: 'POST',
                    data: {
                        remove: true,
                        rowId: rowId
                    },
                    success: function() {
                        location.reload();
                    },
                    error: function() {
                        alert('An error occurred while removing the row.');
                    }
                });
            }
        });
    });
</script>