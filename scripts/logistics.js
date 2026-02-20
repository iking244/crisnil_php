let map;
let directionsService;
let directionsRenderers = [];
let markers = [];
let infoWindow;
let truckIcon;

const routeColors = [
    "#007bff",
    "#28a745",
    "#dc3545",
    "#ffc107",
    "#6f42c1",
    "#fd7e14",
    "#20c997"
];

let truckColorMap = {};
let colorIndex = 0;

/* ================================
   Utility: Assign Unique Color
================================ */
function getTruckColor(plateNumber) {
    if (!truckColorMap[plateNumber]) {
        truckColorMap[plateNumber] =
            routeColors[colorIndex % routeColors.length];
        colorIndex++;
    }
    return truckColorMap[plateNumber];
}

/* ================================
   Utility: Offset overlapping markers
================================ */
function offsetPosition(lat, lng, index, total) {
    if (total === 1) return { lat, lng };

    const radius = 0.0003;
    const angle = (index / total) * (2 * Math.PI);

    return {
        lat: lat + radius * Math.cos(angle),
        lng: lng + radius * Math.sin(angle)
    };
}

/* ================================
   Initialize Map
================================ */
function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 10,
        center: { lat: 14.55, lng: 121.00 },
        disableDefaultUI: true,
        zoomControl: true,
        styles: [

            // Very clean light background
            {
                elementType: "geometry",
                stylers: [{ color: "#f9fafb" }]
            },

            // Remove landscape details
            {
                featureType: "landscape",
                stylers: [{ visibility: "off" }]
            },

            // Remove POIs completely
            {
                featureType: "poi",
                stylers: [{ visibility: "off" }]
            },

            // Remove transit layers
            {
                featureType: "transit",
                stylers: [{ visibility: "off" }]
            },

            // Remove local roads entirely
            {
                featureType: "road.local",
                stylers: [{ visibility: "off" }]
            },

            // Keep arterial roads but soften
            {
                featureType: "road.arterial",
                elementType: "geometry",
                stylers: [{ color: "#e5e7eb" }]
            },

            // Emphasize highways slightly
            {
                featureType: "road.highway",
                elementType: "geometry",
                stylers: [{ color: "#cbd5e1" }]
            },

            {
                featureType: "road.highway",
                elementType: "labels.text.fill",
                stylers: [{ color: "#111827" }]
            },

            // Keep only city names
            {
                featureType: "administrative.locality",
                elementType: "labels.text.fill",
                stylers: [{ color: "#111827" }]
            },

            // Hide smaller administrative labels
            {
                featureType: "administrative.neighborhood",
                stylers: [{ visibility: "off" }]
            },

            // Soft water
            {
                featureType: "water",
                elementType: "geometry",
                stylers: [{ color: "#e2e8f0" }]
            }
        ]
    });

    directionsService = new google.maps.DirectionsService();
    infoWindow = new google.maps.InfoWindow();
    truckIcon = {
    url: "../imgs/red_truck.png",
    scaledSize: new google.maps.Size(40, 40)
};
    loadTrackingData();
    setInterval(loadTrackingData, 20000);
}

/* ================================
   Load Tracking Data
================================ */
function loadTrackingData() {
    clearMap();

    fetch("../api/get_tracking_map.php")
        .then(response => {
            if (!response.ok) throw new Error("HTTP error " + response.status);
            return response.json();
        })
        .then(data => {

            if (!Array.isArray(data)) {
                console.error("Invalid API response:", data);
                return;
            }

            const bounds = new google.maps.LatLngBounds();
            let hasData = false;

            data.forEach(truck => {

                if (!truck.origin_lat || !truck.jobs || truck.jobs.length === 0) return;

                hasData = true;

                const origin = {
                    lat: parseFloat(truck.origin_lat),
                    lng: parseFloat(truck.origin_lng)
                };

                bounds.extend(origin);

                // Origin marker
                const originMarker = new google.maps.Marker({
                    position: origin,
                    map: map,
                    label: "O",
                    title: truck.origin_name
                });

                markers.push(originMarker);

                // Job markers
                truck.jobs.forEach((job, index) => {

                    const baseLat = parseFloat(job.destination_lat);
                    const baseLng = parseFloat(job.destination_lng);

                    const position = offsetPosition(
                        baseLat,
                        baseLng,
                        index,
                        truck.jobs.length
                    );

                    bounds.extend(position);

                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        label: {
                            text: (index + 1).toString(),
                            color: "white",
                            fontWeight: "bold"
                        },
                        title: "Stop " + (index + 1)
                    });

                    marker.addListener("click", () => {
                        const content = `
                    <div style="font-size:14px">
                        <strong>Truck:</strong> ${truck.plate_number}<br>
                        <strong>Trip ID:</strong> ${truck.trip_id}<br>
                        <strong>Stop #:</strong> ${index + 1}<br>
                        <strong>Destination:</strong> ${job.destination_name}<br>
                        <strong>Job ID:</strong> ${job.job_id}
                    </div>
                `;
                        infoWindow.setContent(content);
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                });

                // Draw route
                const color = getTruckColor(truck.plate_number);
                drawMultiStopRoute(origin, truck.jobs, color);
                console.log("Truck GPS:", truck.current_lat, truck.current_lng);
                // Truck marker
                if (truck.current_lat && truck.current_lng) {

                    const truckPosition = {
                        lat: parseFloat(truck.current_lat),
                        lng: parseFloat(truck.current_lng)
                    };

                    bounds.extend(truckPosition);

                    const truckMarker = new google.maps.Marker({
                        position: truckPosition,
                        map: map,
                        icon: truckIcon,
                        title: "Truck: " + truck.plate_number
                    });

                    truckMarker.addListener("click", () => {
                        const content = `
                    <div style="font-size:14px">
                        <strong>Truck:</strong> ${truck.plate_number}<br>
                        <strong>Trip ID:</strong> ${truck.trip_id}
                    </div>
                `;
                        infoWindow.setContent(content);
                        infoWindow.open(map, truckMarker);
                    });

                    markers.push(truckMarker);
                }

            });

            // Auto fit only if we have data
            if (hasData) {
                map.fitBounds(bounds);
            }

        })
        .catch(error => console.error("API error:", error));
}

/* ================================
   Render One Truck
================================ */
function renderTruck(truck, bounds) {
    if (!truck.origin_lat || !truck.jobs || truck.jobs.length === 0) return;

    const origin = {
        lat: parseFloat(truck.origin_lat),
        lng: parseFloat(truck.origin_lng)
    };

    if (isNaN(origin.lat) || isNaN(origin.lng)) return;

    addOriginMarker(origin, truck.origin_name, bounds);
    addJobMarkers(truck, bounds);

    const color = getTruckColor(truck.plate_number);
    drawMultiStopRoute(origin, truck.jobs, color);

    addTruckMarker(truck, bounds);
}

/* ================================
   Origin Marker
================================ */
function addOriginMarker(origin, name, bounds) {
    const marker = new google.maps.Marker({
        position: origin,
        map: map,
        label: "O",
        title: name
    });

    markers.push(marker);
    bounds.extend(marker.getPosition());
}

/* ================================
   Job Markers
================================ */
function addJobMarkers(truck, bounds) {
    truck.jobs.forEach((job, index) => {
        const baseLat = parseFloat(job.destination_lat);
        const baseLng = parseFloat(job.destination_lng);

        if (isNaN(baseLat) || isNaN(baseLng)) return;

        const position = offsetPosition(
            baseLat,
            baseLng,
            index,
            truck.jobs.length
        );

        const marker = new google.maps.Marker({
            position,
            map: map,
            label: {
                text: (index + 1).toString(),
                color: "white",
                fontWeight: "bold"
            },
            title: "Stop " + (index + 1)
        });

        marker.addListener("click", () => {
            infoWindow.setContent(`
                <div style="font-size:14px">
                    <strong>Truck:</strong> ${truck.plate_number}<br>
                    <strong>Trip ID:</strong> ${truck.trip_id}<br>
                    <strong>Stop #:</strong> ${index + 1}<br>
                    <strong>Destination:</strong> ${job.destination_name}<br>
                    <strong>Job ID:</strong> ${job.job_id}
                </div>
            `);
            infoWindow.open(map, marker);
        });

        markers.push(marker);
        bounds.extend(marker.getPosition());
    });
}

/* ================================
   Truck Marker
================================ */
function addTruckMarker(truck, bounds) {
    if (!truck.current_lat || !truck.current_lng) return;

    const position = {
        lat: parseFloat(truck.current_lat),
        lng: parseFloat(truck.current_lng)
    };

    if (isNaN(position.lat) || isNaN(position.lng)) return;

    const marker = new google.maps.Marker({
        position,
        map: map,
        icon: {
            url: "../imgs/red_truck.png",
            scaledSize: new google.maps.Size(40, 40)
        },
        title: "Truck: " + truck.plate_number
    });

    marker.addListener("click", () => {
        infoWindow.setContent(`
            <div style="font-size:14px">
                <strong>Truck:</strong> ${truck.plate_number}<br>
                <strong>Trip ID:</strong> ${truck.trip_id}
            </div>
        `);
        infoWindow.open(map, marker);
    });

    markers.push(marker);
    bounds.extend(marker.getPosition());
}

/* ================================
   Draw Route
================================ */
function drawMultiStopRoute(origin, jobs, color) {
    if (!jobs || jobs.length === 0) return;

    const destination = {
        lat: parseFloat(jobs[jobs.length - 1].destination_lat),
        lng: parseFloat(jobs[jobs.length - 1].destination_lng)
    };

    const waypoints = jobs.slice(0, -1).map(job => ({
        location: {
            lat: parseFloat(job.destination_lat),
            lng: parseFloat(job.destination_lng)
        },
        stopover: true
    }));

    const renderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: color,
            strokeWeight: 7,
            strokeOpacity: 0.95
        }
    });

    directionsService.route(
        {
            origin,
            destination,
            waypoints,
            optimizeWaypoints: true,
            travelMode: google.maps.TravelMode.DRIVING
        },
        (result, status) => {
            if (status === "OK") {
                renderer.setDirections(result);
                directionsRenderers.push(renderer);
            } else {
                console.error("Directions request failed:", status);
            }
        }
    );
}

/* ================================
   Clear Map
================================ */
function clearMap() {
    directionsRenderers.forEach(renderer => renderer.setMap(null));
    directionsRenderers = [];

    markers.forEach(marker => marker.setMap(null));
    markers = [];
}