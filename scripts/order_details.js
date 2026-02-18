let map;
let truckMarker;
let directionsRenderer;

document.addEventListener('DOMContentLoaded', function () {
    const order = window.orderData;  // ← safe, no PHP here

    if (!order) {
        console.error("Order data not found");
        return;
    }

    // Dummy if no lat/long (replace with geocode if needed)
    const origin = { lat: parseFloat(order.origin_lat || 14.5995), lng: parseFloat(order.origin_lng || 120.9842) };
    const destination = { lat: parseFloat(order.destination_lat || 14.5537), lng: parseFloat(order.destination_lng || 121.0244) };
    const current = order.current_latitude && order.current_longitude 
        ? { lat: parseFloat(order.current_latitude), lng: parseFloat(order.current_longitude) }
        : origin;

    // Init map
    map = new google.maps.Map(document.getElementById('orderMap'), {
        zoom: 12,
        center: current,
        styles: [ /* Your map styles from logistics.js if needed */ ]
    });

    // Directions
    directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: true
    });

    // Origin pin (green)
    new google.maps.Marker({
        position: origin,
        map: map,
        icon: { url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png' },
        title: 'Origin'
    });

    // Destination pin (red)
    new google.maps.Marker({
        position: destination,
        map: map,
        icon: { url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png' },
        title: 'Destination'
    });

    // Truck marker (blue car)
    truckMarker = new google.maps.Marker({
        position: current,
        map: map,
        icon: { url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png' },
        title: 'Truck Location'
    });

    // Route
    const directionsService = new google.maps.DirectionsService();
    directionsService.route({
        origin: origin,
        destination: destination,
        travelMode: 'DRIVING'
    }, (result, status) => {
        if (status === 'OK') {
            directionsRenderer.setDirections(result);
        }
    });

    // Auto-refresh if dispatched
    if (order.trip_id) {
        setInterval(() => {
            fetch(`../api/get_trip_location.php?trip_id=${order.trip_id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.lat && data.lng) {
                        const newPos = { lat: parseFloat(data.lat), lng: parseFloat(data.lng) };
                        truckMarker.setPosition(newPos);
                        map.setCenter(newPos);
                    }
                });
        }, 30000);  // 30 seconds
    }
});