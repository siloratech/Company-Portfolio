<?php
// Include database connection
include 'db_con.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve search term from request
$search_query = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $search_query = " WHERE name LIKE '%$search%' OR id LIKE '%$search%'";
}

// Set the number of results per page
$results_per_page = 10;

// Find out the number of pages
$sql = "SELECT COUNT(id) AS total FROM doctor" . $search_query;
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_results = $row['total'];
$total_pages = ceil($total_results / $results_per_page);

// Get the current page number from URL, default to 1 if not set
if (!isset($_GET['page']) || $_GET['page'] < 1) {
    $current_page = 1;
} else {
    $current_page = intval($_GET['page']);
}

// Calculate the starting row for the current page
$start_row = ($current_page - 1) * $results_per_page;

// Fetch doctors from the database for the current page with search filter
$sql = "SELECT id, name, designation, department, email, biography, monday, tuesday, wednesday, thursday, friday, saturday, sunday, time_slot1, time_slot2, photo FROM doctor" . $search_query . " LIMIT $start_row, $results_per_page";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Build working hours string
        $working_hours = '';
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'time_slot1', 'time_slot2'] as $day) {
            if ($row[$day] && $row[$day] !== 'Not Available') {
                $working_hours .= $row[$day] . ', ';
            }
        }

        // Remove the last comma and space
        $working_hours = rtrim($working_hours, ', ');

        // Build card with delete button
        echo '
          <div class="card">
            <div class="card-body">
              <table>
              <tr>
              <td style="padding-right: 20px;">
                  <img style="width: 300px; height: 300px; object-fit: cover; display: block; border: none; margin: 2; padding: 2; border-radius: 0;" src="uploads/' . $row["photo"] . '" alt="' . $row["name"] . '">
              </td>
              <td>
              <div class="card-content">
                <h5 class="card-title">' . $row["name"] . '</h5><br>
                <p class="card-text"><strong>Designation:</strong> ' . $row["designation"] . '</p>
                <p class="card-text"><strong>Department:</strong> ' . $row["department"] . '</p>
                <p class="card-text"><strong>Email:</strong> ' . $row["email"] . '</p>
                <p class="card-text"><strong>Biography:</strong> ' . $row["biography"] . '</p>
                <p class="card-text"><strong>Working Hours:</strong><br>' . $working_hours . '</p>
              </div>
              </td>
              </tr>
              </table>
            </div>
          </div>';
    }
} else {
    echo "No doctors found.";
}

$conn->close();
