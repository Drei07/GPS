<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS Devices Data</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCDg7pLs7iesp74vQ-KSEjnFJW3BKhVq7k"></script> <!-- Replace with your Google Maps API key -->
</head>
<body>
    <h2>GPS Devices Data</h2>
    
    <div id="gpsDataContainer"></div> <!-- Container for displaying GPS data -->
    <div id="map" style="height: 500px; width: 100%;"></div> <!-- Container for the map -->

    <script>
        var map;
        var markers = [];

        $(document).ready(function() {
            // Initialize Google Map
            function initMap() {
                map = new google.maps.Map(document.getElementById('map'), {
                    center: {lat: 0, lng: 0},
                    zoom: 20
                });
            }

            // Function to fetch and display GPS data
            function fetchGPSData() {
                $.ajax({
                    url: 'controller.php', // Update with your PHP script URL
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#gpsDataContainer').empty(); // Clear existing data

                        // Clear existing markers
                        markers.forEach(function(marker) {
                            marker.setMap(null);
                        });
                        markers = [];

                        // Iterate over each device data
                        $.each(data, function(index, device) {
                            var deviceData = '<div>' +
                                '<p><strong>Device ID:</strong> ' + (device.gps_id || 'N/A') + '</p>' +
                                '<p><strong>Latitude:</strong> ' + (device.latitude || 0.0) + '</p>' +
                                '<p><strong>Longitude:</strong> ' + (device.longitude || 0.0) + '</p>' +
                                '<p><strong>Speed:</strong> ' + (device.speed || 0.0) + '</p>' +
                                '<p><strong>Satellites:</strong> ' + (device.satellites || 0) + '</p>' +
                                '<p><strong>GPS Status:</strong> ' + (device.gps_status || 'No Signal') + '</p>' +
                                '<p><strong>Timestamp:</strong> ' + (device.timestamp ? new Date(device.timestamp * 1000).toLocaleString() : 'N/A') + '</p>' +
                                '</div><hr>';

                            $('#gpsDataContainer').append(deviceData);

                            // Add marker to map
                            if (device.latitude && device.longitude) {
                                var marker = new google.maps.Marker({
                                    position: {lat: parseFloat(device.latitude), lng: parseFloat(device.longitude)},
                                    map: map,
                                    title: device.gps_id || 'Unknown Device'
                                });
                                markers.push(marker);
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching GPS data:', error);
                    }
                });
            }

            // Initialize map
            initMap();

            // Fetch GPS data initially
            fetchGPSData();

            // Refresh data every 10 seconds
            setInterval(fetchGPSData, 2000); // Adjust interval as needed
        });
    </script>
</body>
</html>
