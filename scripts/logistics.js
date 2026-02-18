let map;
let directionsService;
let directionsRenderers = [];
let markers = [];
let truckIcon;
let infoWindow;

// Route colors
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

function getTruckColor(plateNumber) {
    if (!truckColorMap[plateNumber]) {
        truckColorMap[plateNumber] =
            routeColors[colorIndex % routeColors.length];
        colorIndex++;
    }
    return truckColorMap[plateNumber];
}

// Offset overlapping markers slightly
function offsetPosition(lat, lng, index, total) {
    if (total === 1) return { lat, lng };

    const radius = 0.0003;
    const angle = (index / total) * (2 * Math.PI);

    return {
        lat: lat + radius * Math.cos(angle),
        lng: lng + radius * Math.sin(angle)
    };
}

function initMap() {
    map = new google.maps.Map(document.getElementById("map"), {
        zoom: 10,
        center: { lat: 14.55, lng: 121.00 },
        disableDefaultUI: true,
        styles: [
            { featureType: "poi", stylers: [{ visibility: "off" }] },
            { featureType: "transit", stylers: [{ visibility: "off" }] }
        ]
    });

    directionsService = new google.maps.DirectionsService();
    infoWindow = new google.maps.InfoWindow();

    truckIcon = {
        url: '../imgs/red_truck.png', scaledSize: new google.maps.Size(50, 50),
        scaledSize: new google.maps.Size(40, 40)
    };

    loadTrackingData();
    setInterval(loadTrackingData, 20000);
}

function loadTrackingData() {
    clearMap();

    fetch("../api/get_tracking_map.php")
        .then(response => {
            if (!response.ok) {
                throw new Error("HTTP error " + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (!Array.isArray(data)) {
                console.error("Invalid API response:", data);
                return;
            }

            data.forEach(truck => {
                if (!truck.origin_lat || !truck.jobs || truck.jobs.length === 0) return;

                const origin = {
                    lat: parseFloat(truck.origin_lat),
                    lng: parseFloat(truck.origin_lng)
                };

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

                // Truck marker
                if (truck.current_lat && truck.current_lng) {
                    const truckMarker = new google.maps.Marker({
                        position: {
                            lat: parseFloat(truck.current_lat),
                            lng: parseFloat(truck.current_lng)
                        },
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
        })
        .catch(error => console.error("API error:", error));
}

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

    const directionsRenderer = new google.maps.DirectionsRenderer({
        map: map,
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: color,
            strokeWeight: 5
        }
    });

    directionsService.route(
        {
            origin: origin,
            destination: destination,
            waypoints: waypoints,
            optimizeWaypoints: true,
            travelMode: google.maps.TravelMode.DRIVING
        },
        (result, status) => {
            if (status === "OK") {
                directionsRenderer.setDirections(result);
                directionsRenderers.push(directionsRenderer);
            } else {
                console.error("Directions request failed:", status);
            }
        }
    );
}

function clearMap() {
    directionsRenderers.forEach(renderer => renderer.setMap(null));
    directionsRenderers = [];

    markers.forEach(marker => marker.setMap(null));
    markers = [];
}
