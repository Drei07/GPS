<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPS Devices Data</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCWrXtUyyqoWHwLddsIRgZKjKc9YGeW7FI"></script> <!-- Replace with your Google Maps API key -->
</head>
<body>
    <h2>GPS Devices Data</h2>
    
    <div id="gpsDataContainer"></div>
    
    <h3>Map for GPS01</h3>
    <div id="map_gps01" style="height: 500px; width: 100%;"></div>
    
    <h3>Map for GPS02</h3>
    <div id="map_gps02" style="height: 500px; width: 100%;"></div>

    <script>
        var map_gps01, map_gps02;
        var markers = {}; // Store markers by device ID

        $(document).ready(function() {
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

            function fetchGPSData() {
                $.ajax({
                    url: 'controller.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#gpsDataContainer').empty();

                        $.each(data, function(index, device) {
                            var deviceData = '<div>' +
                                '<p><strong>Device ID:</strong> ' + (device.gps_id || 'N/A') + '</p>' +
       
                                '<p><strong>Speed:</strong> ' + (device.speed || 0.0) + '</p>' +
                                '<p><strong>Satellites:</strong> ' + (device.satellites || 0) + '</p>' +
                                '<p><strong>Latitude:</strong> ' + (device.latitude) + '</p>' +
                                '<p><strong>Longitude:</strong> ' + (device.longitude) + '</p>' +
                                '<p><strong>Timestamp:</strong> ' + (device.timestamp ? new Date(device.timestamp * 1000).toLocaleString() : 'N/A') + '</p>' +
                                '<p><strong>Street:</strong> <span id="street_' + device.gps_id + '">Loading...</span></p>' +
                                '</div><hr>';

                            $('#gpsDataContainer').append(deviceData);

                            if (device.latitude && device.longitude) {
                                var position = {lat: parseFloat(device.latitude), lng: parseFloat(device.longitude)};
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

                                // Get and display the street name
                                getStreetName(position, device.gps_id);
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching GPS data:', error);
                    }
                });
            }

            function getStreetName(position, gps_id) {
                var geocodeUrl = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' + position.lat + ',' + position.lng + '&key=AIzaSyCDg7pLs7iesp74vQ-KSEjnFJW3BKhVq7k';

                $.getJSON(geocodeUrl, function(response) {
                    var street = 'Unavailable';
                    if (response.status === 'OK' && response.results.length > 0) {
                        street = response.results[0].formatted_address;
                    }
                    $('#street_' + gps_id).text(street);
                }).fail(function() {
                    $('#street_' + gps_id).text('Unavailable');
                });
            }

            initMaps();
            fetchGPSData();
            setInterval(fetchGPSData, 2000);
            setInterval(fetchAndStoreGPSData, 2000);
        });

        function fetchAndStoreGPSData() {
            $.ajax({
                url: 'store_gps_data.php',
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
