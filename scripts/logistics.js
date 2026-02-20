let map;
let directionsService;
let directionsRenderers = [];
let markers = [];
let infoWindow;
let truckIcon;
let SHOW_ROUTES = false;
let allTrips = [];
let selectedTripId = null; // null = show all

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

            allTrips = data; // store everything
            renderTrips();

            const bounds = new google.maps.LatLngBounds();
            let hasData = false;

            data.forEach(truck => {
                renderTruck(truck, bounds);
                hasData = true;
            });

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

    const truckColor = getTruckColor(truck.plate_number);

    addOriginMarker(origin, bounds);
    addJobMarkers(truck, truckColor, bounds);
    addTruckMarker(truck, bounds);

    if (SHOW_ROUTES) {
        drawMultiStopRoute(origin, truck.jobs, truckColor);
    }
}

/* ================================
   Origin Marker
================================ */
function addOriginMarker(origin, bounds) {
    const marker = new google.maps.Marker({
        position: origin,
        map: map,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 6,
            fillColor: "#6b7280",
            fillOpacity: 1,
            strokeColor: "#111827",
            strokeWeight: 2
        },
        zIndex: 200
    });

    markers.push(marker);
    bounds.extend(origin);
}

/* ================================
   Job Markers
================================ */
function addJobMarkers(truck, truckColor, bounds) {

    const nextJob = truck.jobs.find(job => job.status !== 'completed');

    truck.jobs.forEach((job) => {

        const lat = parseFloat(job.destination_lat);
        const lng = parseFloat(job.destination_lng);

        if (isNaN(lat) || isNaN(lng)) return;

        const isCompleted = job.status === 'completed';
        const isNext = nextJob && job.job_id === nextJob.job_id;

        let scale = 7;
        let opacity = 0.4;
        let zIndex = 400;

        if (isCompleted) {
            opacity = 0.25;
            zIndex = 200;
        }
        else if (isNext) {
            scale = 11;
            opacity = 1;
            zIndex = 800;
        }
        else {
            opacity = 0.6;
        }

        const marker = new google.maps.Marker({
            position: { lat, lng },
            map: map,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                scale: scale,
                fillColor: truckColor,
                fillOpacity: opacity,
                strokeColor: isNext ? "#ffffff" : "#111827",
                strokeWeight: isNext ? 3 : 2
            },
            zIndex: zIndex,
            title: job.destination_name
        });

        marker.addListener("click", () => {
            infoWindow.setContent(`
                <div style="font-size:14px">
                    <strong>Truck:</strong> ${truck.plate_number}<br>
                    <strong>Status:</strong> ${job.status}<br>
                    <strong>Destination:</strong> ${job.destination_name}
                </div>
            `);
            infoWindow.open(map, marker);
        });

        markers.push(marker);
        bounds.extend({ lat, lng });
    });
}
/* ================================
   Truck Marker
================================ */
function addTruckMarker(truck, bounds) {

    const lat = parseFloat(truck.current_lat);
    const lng = parseFloat(truck.current_lng);

    if (isNaN(lat) || isNaN(lng)) return;

    const position = { lat, lng };

    const marker = new google.maps.Marker({
        position,
        map: map,
        icon: {
            url: "../imgs/red_truck.png",
            scaledSize: new google.maps.Size(48, 48)
        },
        zIndex: 1000,
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
    bounds.extend(position);
}
/* ================================
   Draw Route
================================ */
function drawMultiStopRoute(origin, jobs, color) {
    if (!jobs || jobs.length === 0) return;
    if (!SHOW_ROUTES) return; //

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

function renderTrips() {

    clearMap();

    const bounds = new google.maps.LatLngBounds();
    let hasData = false;

    const tripsToRender = selectedTripId
        ? allTrips.filter(t => t.trip_id == selectedTripId)
        : allTrips;

    tripsToRender.forEach(truck => {
        renderTruck(truck, bounds);
        hasData = true;
    });

    if (hasData) {
        map.fitBounds(bounds);
    }
}

function filterByTrip(tripId) {
    selectedTripId = tripId;
    renderTrips();
}

function showAllTrips() {
    selectedTripId = null;
    renderTrips();
}