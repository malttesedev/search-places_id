<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Place ID Finder</title>
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
        form {
            display: flex;
            flex-direction: column;
        }
        label, input {
            margin-bottom: 10px;
        }
        input[type="text"], input[type="submit"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="view.php">View Places</a>
    </div>
    <div class="container">
        <h1>Google Place ID Finder</h1>
        <form action="" method="post">
            <label for="name">Place Name:</label>
            <input type="text" id="name" name="name" required>
            
            <label for="fullAddress">Full Address:</label>
            <input type="text" id="fullAddress" name="fullAddress" required>
            
            <label for="latitude">Latitude:</label>
            <input type="text" id="latitude" name="latitude" required>
            
            <label for="longitude">Longitude:</label>
            <input type="text" id="longitude" name="longitude" required>
            
            <input type="submit" value="Add Place">
        </form>
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

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Replace with your Google API key
            $API_KEY = 'AIzaSyDUZC0w6ElmNOUwBpT7uoNKs6fp-c7lRdk
';

            // Function to get place ID and types
            function get_place_id_and_types($api_key, $data) {
                $input_text = "{$data['name']} {$data['fullAddress']}";
                $url = "https://maps.googleapis.com/maps/api/place/findplacefromtext/json";
                $params = [
                    "input" => $input_text,
                    "inputtype" => "textquery",
                    "fields" => "place_id,types",
                    "locationbias" => "point:{$data['latitude']},{$data['longitude']}",
                    "key" => $api_key
                ];
                $response = file_get_contents($url . '?' . http_build_query($params));
                $response = json_decode($response, true);

                if (!isset($response['candidates']) || empty($response['candidates'])) {
                    echo "No results found for {$data['name']} at {$data['fullAddress']}<br>";
                    return [null, null, "No results found"];
                }

                foreach ($response['candidates'] as $result) {
                    if (!in_array("street_address", $result['types'])) {
                        return [$result['place_id'], $result['types'], null];
                    }
                }

                return [null, null, "Only street address found"];
            }

            // Get the form data
            $data = [
                'name' => $_POST['name'],
                'fullAddress' => $_POST['fullAddress'],
                'latitude' => $_POST['latitude'],
                'longitude' => $_POST['longitude']
            ];

            // Process the data and get the place ID and types
            list($place_id, $place_types, $comment) = get_place_id_and_types($API_KEY, $data);
            $data["place_id"] = $place_id ?: "Not found";
            $data["place_type"] = $place_types ? implode(", ", $place_types) : "None";
            $data["comment"] = $comment;

            // Store the data in the database
            $stmt = $conn->prepare("INSERT INTO places (name, full_address, latitude, longitude, place_id, place_type, comment) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddsss", $data['name'], $data['fullAddress'], $data['latitude'], $data['longitude'], $data['place_id'], $data['place_type'], $data['comment']);

            if ($stmt->execute()) {
                echo "<p>Data stored in database successfully.</p>";
            } else {
                echo "<p>Error storing data in database: " . $stmt->error . "</p>";
            }

            $stmt->close();
        }

        $conn->close();
        ?>
    </div>
</body>
</html>
