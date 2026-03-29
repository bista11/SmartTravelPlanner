<?php
$errors = session()->getFlashdata('errors') ?? [];
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger rounded-4 shadow-sm border-0">
        <div class="fw-semibold mb-2">Please fix the following:</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="trip-form-shell">
    <div class="section-card">
        <div class="section-header">
            <h3 class="section-title">Trip Details</h3>
            <p class="section-subtitle">Enter the main information for this travel plan.</p>
        </div>

        <div class="section-body">
            <div class="row g-4">
                <div class="col-md-6 position-relative">
                    <label for="destination" class="form-label">Destination</label>
                    <input type="text" class="form-control" id="destination" name="destination" value="<?= esc(old('destination', $trip['destination'] ?? '')) ?>" autocomplete="off" placeholder="Search city or destination" required>
                    <div class="field-hint">Start typing to see location suggestions.</div>
                    <div id="destinationSuggestions" class="list-group position-absolute w-100 shadow-sm suggestions-menu"></div>
                </div>

                <div class="col-md-6">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" name="country" value="<?= esc(old('country', $trip['country'] ?? '')) ?>" placeholder="Country name" required>
                    <div class="field-hint">This will auto-fill when a suggestion is selected.</div>
                </div>

                <div class="col-md-6">
                    <label for="travel_date" class="form-label">Travel Date</label>
                    <input type="date" class="form-control" id="travel_date" name="travel_date" value="<?= esc(old('travel_date', $trip['travel_date'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <label for="budget" class="form-label">Budget</label>
                    <input type="number" step="0.01" class="form-control" id="budget" name="budget" value="<?= esc(old('budget', $trip['budget'] ?? '')) ?>" placeholder="Enter estimated budget">
                </div>

                <div class="col-lg-6">
                    <div class="coord-box">
                        <div class="coord-box-title">Coordinates</div>
                        <p>Pick a location from the map, use your current location, or choose from destination suggestions.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="latitude" class="form-label">Latitude</label>
                    <input type="text" class="form-control" id="latitude" name="latitude" value="<?= esc(old('latitude', $trip['latitude'] ?? '')) ?>" placeholder="Selected from map">
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="longitude" class="form-label">Longitude</label>
                    <input type="text" class="form-control" id="longitude" name="longitude" value="<?= esc(old('longitude', $trip['longitude'] ?? '')) ?>" placeholder="Selected from map">
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
                        <div id="nearbyTripsList" class="mb-0"></div>
                    </div>
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Trip notes, itinerary, recommendations..."><?= esc(old('notes', $trip['notes'] ?? '')) ?></textarea>
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

let suggestionTimer = null;
let map = null;
let marker = null;

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function weatherCodeToText(code) {
    const codes = {
        0: 'Clear sky',
        1: 'Mainly clear',
        2: 'Partly cloudy',
        3: 'Overcast',
        45: 'Fog',
        48: 'Depositing rime fog',
        51: 'Light drizzle',
        53: 'Moderate drizzle',
        55: 'Dense drizzle',
        56: 'Light freezing drizzle',
        57: 'Dense freezing drizzle',
        61: 'Slight rain',
        63: 'Moderate rain',
        65: 'Heavy rain',
        66: 'Light freezing rain',
        67: 'Heavy freezing rain',
        71: 'Slight snow',
        73: 'Moderate snow',
        75: 'Heavy snow',
        77: 'Snow grains',
        80: 'Slight rain showers',
        81: 'Moderate rain showers',
        82: 'Violent rain showers',
        85: 'Slight snow showers',
        86: 'Heavy snow showers',
        95: 'Thunderstorm',
        96: 'Thunderstorm with slight hail',
        99: 'Thunderstorm with heavy hail'
    };

    return codes[code] ?? `Weather code: ${code}`;
}

function bindMarkerDrag() {
    if (!marker) return;

    marker.off('dragend');
    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        setLatLng(pos.lat, pos.lng, false);
        loadWeather();
    });
}

function ensureMarker(lat, lng) {
    if (!map) return;

    if (!marker) {
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);

        bindMarkerDrag();
    } else {
        marker.setLatLng([lat, lng]);
    }
}

function initMap() {
    const lat = parseFloat(latitudeInput.value);
    const lng = parseFloat(longitudeInput.value);

    map = L.map('tripMap');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    if (!isNaN(lat) && !isNaN(lng)) {
        map.setView([lat, lng], 10);
        ensureMarker(lat, lng);
    } else {
        map.setView([20, 0], 2);
    }

    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        ensureMarker(lat, lng);
        setLatLng(lat, lng, false);
        loadWeather();
    });
}

function setLatLng(lat, lng, centerMap = false) {
    latitudeInput.value = Number(lat).toFixed(6);
    longitudeInput.value = Number(lng).toFixed(6);

    ensureMarker(lat, lng);

    if (map && centerMap) {
        map.panTo([lat, lng]);
    }
}

function updateMapFromInputs() {
    const lat = parseFloat(latitudeInput.value);
    const lng = parseFloat(longitudeInput.value);

    if (isNaN(lat) || isNaN(lng)) {
        return;
    }

    ensureMarker(lat, lng);
    setLatLng(lat, lng, true);
}

latitudeInput.addEventListener('change', updateMapFromInputs);
longitudeInput.addEventListener('change', updateMapFromInputs);

function renderSuggestionMessage(message, type = 'muted') {
    const colorClass =
        type === 'danger' ? 'text-danger' :
        type === 'success' ? 'text-success' :
        'text-muted';

    suggestionBox.innerHTML = `
        <div class="list-group-item ${colorClass}">
            ${escapeHtml(message)}
        </div>
    `;
}

function fillLocationFields(item, updateDestination = true) {
    if (updateDestination) {
        destinationInput.value = item.name || '';
    }

    countryInput.value = item.country || '';

    if (item.latitude != null && item.longitude != null) {
        const lat = parseFloat(item.latitude);
        const lng = parseFloat(item.longitude);

        if (!isNaN(lat) && !isNaN(lng)) {
            ensureMarker(lat, lng);
            setLatLng(lat, lng, true);

            if (map) {
                map.setView([lat, lng], 10);
            }

            loadWeather();
        }
    }
}

function createSuggestionButton(item) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'list-group-item list-group-item-action';

    const title = `${item.name || ''}${item.admin1 ? ', ' + item.admin1 : ''}`;
    const subtitle = item.country || '';

    btn.innerHTML = `
        <div class="fw-semibold">${escapeHtml(title)}</div>
        <div class="small text-muted">${escapeHtml(subtitle)}</div>
    `;

    btn.addEventListener('click', () => {
        fillLocationFields(item, true);
        suggestionBox.innerHTML = '';
    });

    return btn;
}

destinationInput.addEventListener('input', function () {
    const q = this.value.trim();

    clearTimeout(suggestionTimer);

    if (q.length < 2) {
        suggestionBox.innerHTML = '';
        countryInput.value = '';
        return;
    }

    renderSuggestionMessage('Searching locations...');

    suggestionTimer = setTimeout(async () => {
        try {
            const response = await fetch(`<?= site_url('api/destination-suggest') ?>?q=${encodeURIComponent(q)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            const results = Array.isArray(data.results) ? data.results : [];

            suggestionBox.innerHTML = '';

            if (!results.length) {
                renderSuggestionMessage('No matching locations found.');
                countryInput.value = '';
                return;
            }

            countryInput.value = results[0].country || '';

            results.forEach(item => {
                suggestionBox.appendChild(createSuggestionButton(item));
            });

        } catch (error) {
            console.error('Destination suggestion error:', error);
            renderSuggestionMessage('Unable to load suggestions. Check your API route.', 'danger');
        }
    }, 300);
});

document.addEventListener('click', function (e) {
    if (!suggestionBox.contains(e.target) && e.target !== destinationInput) {
        suggestionBox.innerHTML = '';
    }
});

document.addEventListener('click', function(e) {
    if (!suggestionBox.contains(e.target) && e.target !== destinationInput) {
        suggestionBox.innerHTML = '';
    }
});

btnCurrentLocation.addEventListener('click', function() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }

    btnCurrentLocation.disabled = true;
    btnCurrentLocation.textContent = 'Getting Location...';

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            ensureMarker(lat, lng);
            setLatLng(lat, lng, true);
            map.setView([lat, lng], 12);
            loadWeather();
            btnCurrentLocation.disabled = false;
            btnCurrentLocation.textContent = 'Use My Current Location';
        },
        function() {
            alert('Unable to fetch current location.');
            btnCurrentLocation.disabled = false;
            btnCurrentLocation.textContent = 'Use My Current Location';
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
});

btnLoadWeather.addEventListener('click', loadWeather);

async function loadWeather() {
    const lat = latitudeInput.value.trim();
    const lng = longitudeInput.value.trim();

    if (!lat || !lng) {
        alert('Please select a location on the map first.');
        return;
    }

    try {
        weatherCard.classList.remove('d-none');
        weatherCard.innerHTML = `
            <div class="panel-title mb-2">Current Weather</div>
            <div class="text-muted">Loading weather data...</div>
        `;

        const response = await fetch(`<?= site_url('api/weather') ?>?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}`);
        const data = await response.json();

        if (!response.ok || data.error) {
            weatherCard.innerHTML = `
                <div class="panel-title mb-2 text-danger">Weather Error</div>
                <div>${data.error ? escapeHtml(data.error) : 'Unknown error'}</div>
                ${data.details ? '<div class="small text-muted mt-2">' + escapeHtml(data.details) + '</div>' : ''}
            `;
            return;
        }

        const current = data.current || {};
        const weatherText = weatherCodeToText(current.weather_code);

        weatherCard.innerHTML = `
            <div class="panel-title">Current Weather</div>
            <div class="row g-3">
                <div class="col-sm-6 col-lg-3">
                    <div class="nearby-item">
                        <div class="small text-muted">Condition</div>
                        <div class="fw-semibold">${escapeHtml(weatherText)}</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="nearby-item">
                        <div class="small text-muted">Temperature</div>
                        <div class="fw-semibold">${current.temperature_2m ?? '-'} °C</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="nearby-item">
                        <div class="small text-muted">Feels Like</div>
                        <div class="fw-semibold">${current.apparent_temperature ?? '-'} °C</div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="nearby-item">
                        <div class="small text-muted">Wind Speed</div>
                        <div class="fw-semibold">${current.wind_speed_10m ?? '-'} km/h</div>
                    </div>
                </div>
            </div>
        `;
    } catch (error) {
        weatherCard.classList.remove('d-none');
        weatherCard.innerHTML = `
            <div class="panel-title mb-2 text-danger">Weather Error</div>
            <div>${escapeHtml(error.message || 'Unable to load weather.')}</div>
        `;
    }
}

btnNearbyTrips.addEventListener('click', async function() {
    const lat = latitudeInput.value.trim();
    const lng = longitudeInput.value.trim();

    if (!lat || !lng) {
        alert('Please select a location on the map first.');
        return;
    }

    try {
        nearbyTripsCard.classList.remove('d-none');
        nearbyTripsList.innerHTML = '<div class="text-muted">Loading nearby trips...</div>';

        const response = await fetch(`<?= site_url('api/nearby-trips') ?>?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lng)}&radius=100`);
        const data = await response.json();

        const trips = data.trips || [];

        if (!trips.length) {
            nearbyTripsList.innerHTML = '<div class="text-muted">No saved trips found nearby.</div>';
            return;
        }

        nearbyTripsList.innerHTML = trips.map(trip => `
            <div class="nearby-item">
                <div class="fw-semibold">${escapeHtml(trip.destination)}, ${escapeHtml(trip.country)}</div>
                <div class="small text-muted mt-1">
                    Distance: ${Number(trip.distance_km).toFixed(2)} km
                </div>
            </div>
        `).join('');
    } catch (error) {
        nearbyTripsList.innerHTML = '<div class="text-danger">Unable to load nearby trips.</div>';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initMap();

    if (latitudeInput.value.trim() && longitudeInput.value.trim()) {
        updateMapFromInputs();
    }
});
</script>