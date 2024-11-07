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
    
    <div id="gpsDataContainer"></div>
    
    <!-- Separate map divs for each GPS device -->
    <h3>Map for GPS01</h3>
    <div id="map_gps01" style="height: 500px; width: 100%;"></div>
    
    <h3>Map for GPS02</h3>
    <div id="map_gps02" style="height: 500px; width: 100%;"></div>

    <script>
        // Initialize map objects for each GPS device
        var map_gps01, map_gps02;
        var markers = {}; // Store markers by device ID

        $(document).ready(function() {
            // Initialize Google Maps for each device
            function initMaps() {
                map_gps01 = new google.maps.Map(document.getElementById('map_gps01'), {
                    center: {lat: 0, lng: 0},
                    zoom: 18
                });

                map_gps02 = new google.maps.Map(document.getElementById('map_gps02'), {
                    center: {lat: 0, lng: 0},
                    zoom: 18
                });
            }

            // Fetch and display GPS data
            function fetchGPSData() {
                $.ajax({
                    url: 'controller.php', // Replace with your actual PHP script
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#gpsDataContainer').empty(); // Clear existing data

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

                            if (device.latitude && device.longitude) {
                                var position = {lat: parseFloat(device.latitude), lng: parseFloat(device.longitude)};

                                // Determine the correct map and update marker based on device ID
                                var map = device.gps_id === 'GPS01' ? map_gps01 : map_gps02;

                                if (markers[device.gps_id]) {
                                    markers[device.gps_id].setPosition(position);
                                    map.panTo(position);
                                } else {
                                    var marker = new google.maps.Marker({
                                        position: position,
                                        map: map,
                                        title: device.gps_id,
                                        icon: {
                                            url: 'src/img/gps-car-icon-68.png',
                                            scaledSize: new google.maps.Size(60, 60),
                                        }
                                    });
                                    markers[device.gps_id] = marker;
                                    map.setCenter(position);
                                }
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching GPS data:', error);
                    }
                });
            }

            // Initialize the maps
            initMaps();

            // Fetch GPS data initially
            fetchGPSData();

            // Set intervals for fetching and storing data
            setInterval(fetchGPSData, 2000);
            setInterval(fetchAndStoreGPSData, 2000);
        });

        // Function to store GPS data in the database
        function fetchAndStoreGPSData() {
            $.ajax({
                url: 'store_gps_data.php', // Update to the new PHP script
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log(response.message);
                },
                error: function(xhr, status, error) {
                    console.error('Error storing GPS data:', error);
                }
            });
        }
    </script>
</body>
</html>
