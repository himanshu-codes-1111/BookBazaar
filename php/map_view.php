<?php 
// map_view.php

include 'db_connection.php'; // Include the database connection file

// Get the search query from the URL
$search_query = isset($_GET['query']) ? $_GET['query'] : '';

if ($search_query) {
    // Prepare the SQL query to fetch book locations
    $sql = "SELECT b.book_id, b.title, l.latitude, l.longitude
            FROM books b
            JOIN locations l ON b.book_id = l.book_id
            WHERE (b.title LIKE :query OR b.author LIKE :query OR b.genre LIKE :query)
            AND b.status = 'Available'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['query' => "%$search_query%"]);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $locations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Bazaar - Map View</title>
    <link rel="stylesheet" href="../css/map_view.css"> <!-- Ensure this path is correct -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
        header {
            background-color: #D6CFC4; /* Beige header */
            color: black;
            padding: 5px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        /* Navigation Bar */
        nav {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-container {
            display: flex;
            align-items: center;
        }
        .logo img {
            width: 50px;
            border-radius: 50%;
        }
        .platform-name {
            margin-left: 5px; /* Space between logo and name */
            font-size: 30px;
            font-family: 'Kristen ITC', sans-serif;
        }
        .nav-links {
            display: flex;
            gap: 15px;
        }
        .nav-links a {
            color: black;
            text-decoration: none;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .nav-links a i {
            font-size: 25px;
        }
        .nav-links a:hover {
            color: gray; /* Change color on hover */
        }
        #map {
            height: calc(100vh - 60px); /* Adjust height for header */
            width: 100%;
            position: relative;
        }
        .search-bar {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
            background: white;
            padding: 5px;
            border-radius: 3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .search-bar input {
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 5px;
            width: 200px;
        }
        .suggestions {
            position: absolute;
            top: 40px;
            right: 10px;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            max-height: 200px;
            overflow-y: auto;
            width: 200px;
        }
        .suggestions div {
            padding: 5px;
            cursor: pointer;
        }
        .suggestions div:hover {
            background: #f0f0f0;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo-container">
                <div class="logo">
                    <img src="../assets/logo1.jpg" alt="Book Bazaar Logo">
                </div>
                <h1 class="platform-name">Book Bazaar</h1>
            </div>
            <div class="nav-links">
                <a href="../home.html"><i class="fa-solid fa-house-chimney"></i></a>
                <a href="../login.html"><i class="fa-solid fa-right-to-bracket"></i></a>
                <a href="userprofile.php"><i class="fa-solid fa-user"></i></a>
            </div>
        </nav>
    </header>

    <div id="map">
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search location...">
            <div id="suggestions" class="suggestions"></div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var map = L.map('map').setView([20.5937, 78.9629], 5); // Centered on India

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var locations = <?php echo json_encode($locations); ?>;

        // Create a custom icon using Font Awesome
        var customIcon = L.divIcon({
            className: 'custom-icon',
            html: '<i class="fa-solid fa-location-dot" style="font-size: 30px; color: black;"></i>', // Font Awesome icon
            iconSize: [24, 24], // Size of the icon
            iconAnchor: [12, 24] // Anchor point
        });

        locations.forEach(function(location) {
            var marker = L.marker([location.latitude, location.longitude], { icon: customIcon }).addTo(map);
            marker.bindPopup('<a href="bookdetails.php?id=' + location.book_id + '">' + location.title + '</a>');
        });

        var searchInput = document.getElementById('searchInput');
        var suggestionsContainer = document.getElementById('suggestions');

        function handleSearch(query) {
            if (query) {
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        suggestionsContainer.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(function(location) {
                                var suggestionItem = document.createElement('div');
                                suggestionItem.textContent = location.display_name;
                                suggestionItem.dataset.lat = location.lat;
                                suggestionItem.dataset.lon = location.lon;
                                suggestionItem.addEventListener('click', function() {
                                    var lat = parseFloat(this.dataset.lat);
                                    var lon = parseFloat(this.dataset.lon);
                                    map.setView([lat, lon], 13); // Zoom into the location
                                    suggestionsContainer.innerHTML = '';
                                });
                                suggestionsContainer.appendChild(suggestionItem);
                            });
                        } else {
                            suggestionsContainer.innerHTML = '<div>No locations found</div>';
                        }
                    });
            } else {
                suggestionsContainer.innerHTML = '';
            }
        }

        searchInput.addEventListener('input', function() {
            handleSearch(this.value);
        });

        document.getElementById('searchInput').addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                handleSearch(this.value);
            }
        });
    });
</script>

</body>
</html>
