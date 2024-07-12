<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Places</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .nav {
            background-color: #333;
            overflow: hidden;
        }
        .nav a {
            float: left;
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 20px;
            text-decoration: none;
        }
        .nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .actions {
            display: flex;
            gap: 10px;
        }
        .actions button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .actions .edit {
            background-color: #007bff;
            color: white;
        }
        .actions .delete {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="view.php">View Places</a>
    </div>
    <div class="container">
        <h1>View Places</h1>
        <?php
        // Database connection
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "places_db";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Handle delete request
        if (isset($_GET['delete'])) {
            $id = $_GET['delete'];
            $stmt = $conn->prepare("DELETE FROM places WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<p>Data deleted successfully.</p>";
            } else {
                echo "<p>Error deleting data: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }

        // Handle edit request
        if (isset($_POST['edit'])) {
            $id = $_POST['id'];
            $name = $_POST['name'];
            $fullAddress = $_POST['fullAddress'];
            $latitude = $_POST['latitude'];
            $longitude = $_POST['longitude'];
            $stmt = $conn->prepare("UPDATE places SET name = ?, full_address = ?, latitude = ?, longitude = ? WHERE id = ?");
            $stmt->bind_param("ssddi", $name, $fullAddress, $latitude, $longitude, $id);
            if ($stmt->execute()) {
                echo "<p>Data updated successfully.</p>";
            } else {
                echo "<p>Error updating data: " . $stmt->error . "</p>";
            }
            $stmt->close();
        }

        // Fetch all data from database
        $result = $conn->query("SELECT * FROM places");
        if ($result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Full Address</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Place ID</th>
                        <th>Place Type</th>
                        <th>Comment</th>
                        <th>Actions</th>
                    </tr>";
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['full_address']}</td>
                        <td>{$row['latitude']}</td>
                        <td>{$row['longitude']}</td>
                        <td>{$row['place_id']}</td>
                        <td>{$row['place_type']}</td>
                        <td>{$row['comment']}</td>
                        <td class='actions'>
                            <form action='' method='post' style='display:inline;'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='hidden' name='name' value='{$row['name']}'>
                                <input type='hidden' name='fullAddress' value='{$row['full_address']}'>
                                <input type='hidden' name='latitude' value='{$row['latitude']}'>
                                <input type='hidden' name='longitude' value='{$row['longitude']}'>
                                <button type='submit' name='edit' class='edit'>Edit</button>
                            </form>
                            <form action='' method='get' style='display:inline;'>
                                <input type='hidden' name='delete' value='{$row['id']}'>
                                <button type='submit' class='delete'>Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No data found.</p>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
