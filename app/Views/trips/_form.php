<div class="trip-form-shell">

    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Trip Details</h3>
            <p class="section-subtitle">Enter the main information for this travel plan.</p>
        </div>

        <div class="section-body">
            <div class="row g-4">

                <div class="col-md-6 position-relative">
                    <label class="form-label">Destination</label>
                    <input type="text" class="form-control" id="destination" placeholder="Search city or destination">
                    <div class="field-hint">Start typing to see location suggestions.</div>
                    <div id="destinationSuggestions" class="list-group position-absolute w-100 shadow-sm suggestions-menu"></div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" placeholder="Country name">
                    <div class="field-hint">This will auto-fill when a suggestion is selected.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Travel Date</label>
                    <input type="date" class="form-control" id="travel_date">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Budget</label>
                    <input type="number" class="form-control" id="budget" placeholder="Enter estimated budget">
                </div>

                <div class="col-lg-6">
                    <div class="coord-box">
                        <div class="coord-box-title">Coordinates</div>
                        <p>Pick a location from the map, use your current location, or choose from destination suggestions.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Latitude</label>
                    <input type="text" class="form-control" id="latitude" placeholder="Selected from map">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Longitude</label>
                    <input type="text" class="form-control" id="longitude" placeholder="Selected from map">
                </div>

                <div class="col-12">
                    <div class="action-bar">
                        <button type="button" class="btn btn-outline-secondary" id="btnCurrentLocation">
                            Use My Current Location
                        </button>
                        <button type="button" class="btn btn-primary" id="btnLoadWeather">
                            Load Weather
                        </button>
                        <button type="button" class="btn btn-outline-dark" id="btnNearbyTrips">
                            Nearby Saved Trips
                        </button>
                    </div>
                </div>

                <div class="col-12">
                    <div id="weatherCard" class="info-panel weather d-none"></div>
                </div>

                <div class="col-12">
                    <div id="nearbyTripsCard" class="info-panel nearby d-none">
                        <div class="panel-title">Nearby Saved Trips</div>
                        <div id="nearbyTripsList"></div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" rows="4" placeholder="Trip notes, itinerary, recommendations..."></textarea>
                </div>

            </div>
        </div>
    </div>

    <div class="map-wrap mt-4">
        <div class="map-toolbar">
            <div>
                <p class="map-title">Interactive Location Picker</p>
                <p class="map-help">Click anywhere on the map or drag the marker to select the exact trip location.</p>
            </div>
        </div>
        <div id="tripMap"></div>
    </div>

</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
const destinationInput = document.getElementById('destination');
const countryInput = document.getElementById('country');
const latitudeInput = document.getElementById('latitude');
const longitudeInput = document.getElementById('longitude');
const suggestionBox = document.getElementById('destinationSuggestions');
const weatherCard = document.getElementById('weatherCard');
const nearbyTripsCard = document.getElementById('nearbyTripsCard');
const nearbyTripsList = document.getElementById('nearbyTripsList');
const btnCurrentLocation = document.getElementById('btnCurrentLocation');
const btnLoadWeather = document.getElementById('btnLoadWeather');
const btnNearbyTrips = document.getElementById('btnNearbyTrips');

let map = null;
let marker = null;

function ensureMarker(lat, lng) {
    if (!map) return;
    if (!marker) {
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    } else {
        marker.setLatLng([lat, lng]);
    }
}

function initMap() {
    map = L.map('tripMap').setView([20, 0], 2);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        latitudeInput.value = lat.toFixed(6);
        longitudeInput.value = lng.toFixed(6);
        ensureMarker(lat, lng);
    });
}

btnCurrentLocation.addEventListener('click', function() {
    navigator.geolocation.getCurrentPosition(function(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;

        latitudeInput.value = lat.toFixed(6);
        longitudeInput.value = lng.toFixed(6);

        ensureMarker(lat, lng);
        map.setView([lat, lng], 12);
    });
});

btnLoadWeather.addEventListener('click', function() {
    weatherCard.classList.remove('d-none');
    weatherCard.innerHTML = `
        <div class="panel-title">Current Weather</div>
        <div class="text-muted">Weather feature requires backend/API.</div>
    `;
});

btnNearbyTrips.addEventListener('click', function() {
    nearbyTripsCard.classList.remove('d-none');
    nearbyTripsList.innerHTML = `<div class="text-muted">Nearby trips require backend/API.</div>`;
});

document.addEventListener('DOMContentLoaded', function() {
    initMap();
});
</script>