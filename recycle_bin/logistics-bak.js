console.log("Logistics dashboard loaded");

let map;

function initMap() {
    const antipolo = { lat: 14.6253, lng: 121.1245 };

    map = new google.maps.Map(document.getElementById("map"), {
        center: antipolo,
        zoom: 12,
    });

    // Sample warehouse marker
    new google.maps.Marker({
        position: antipolo,
        map: map,
        title: "Warehouse: Antipolo",
    });
}
