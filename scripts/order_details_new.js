// scripts/order_details_new.js

document.addEventListener('DOMContentLoaded', function () {
    // The order data is passed from PHP as window.orderData
    const order = window.orderData || {};

    // Check for required coordinates
    if (!order.origin_lat || !order.origin_lng || !order.destination_lat || !order.destination_lng) {
        console.warn("Missing coordinates for route");
        return;
    }

    const origin = { lat: parseFloat(order.origin_lat), lng: parseFloat(order.origin_lng) };
    const destination = { lat: parseFloat(order.destination_lat), lng: parseFloat(order.destination_lng) };

    // Wait for Google Maps API to load, then init
    window.initMap = function () {
        const map = new google.maps.Map(document.getElementById("orderMap"), {
            zoom: 12,
            center: origin,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: false,
            polylineOptions: {
                strokeColor: "#007bff",  // Blue line for pathway
                strokeWeight: 5
            }
        });

        // Request and render the real road pathway
        directionsService.route({
            origin: origin,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING
        }, function (response, status) {
            if (status === 'OK') {
                directionsRenderer.setDirections(response);
            } else {
                console.error('Directions request failed: ' + status);
            }
        });


        if (order.current_latitude && order.current_longitude) {
            const truckPos = { lat: parseFloat(order.current_latitude), lng: parseFloat(order.current_longitude) };
            
            const truckMarker = new google.maps.Marker({
                position: truckPos,
                map: map,
                icon: { url: '../imgs/red_truck.png', scaledSize: new google.maps.Size(50, 50) }
            });

            const infoWindow = new google.maps.InfoWindow({
                content: '<div style="font-weight:bold; color:#dc3545;">Current Truck Location</div>'
            });

            truckMarker.addListener('click', () => {
                infoWindow.open(map, truckMarker);
            });

            // Auto-open popup on load (optional)
            infoWindow.open(map, truckMarker);
        }
    };
});