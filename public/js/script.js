document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('liveSearchInput');
    const tripTableBody = document.getElementById('tripTableBody');
    const btnRefreshTrips = document.getElementById('btnRefreshTrips');
    const allTripsMapEl = document.getElementById('allTripsMap');

    if (!searchInput || !tripTableBody || !btnRefreshTrips || !allTripsMapEl) {
        return;
    }

    let searchTimer = null;
    let map = null;
    let markersLayer = null;

    const config = window.tripPageConfig || {};
    const searchUrl = config.searchUrl || '';
    const editBaseUrl = config.editBaseUrl || '';
    const deleteBaseUrl = config.deleteBaseUrl || '';
    const initialTrips = Array.isArray(config.initialTrips) ? config.initialTrips : [];

    if (allTripsMapEl._leaflet_id) {
        allTripsMapEl._leaflet_id = null;
    }

    map = L.map(allTripsMapEl).setView([20, 0], 2);
    markersLayer = L.layerGroup().addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function renderTrips(trips) {
        if (!tripTableBody) return;

        if (!trips.length) {
            tripTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="empty-state">No trips found.</td>
                </tr>
            `;
            renderMapMarkers([]);
            return;
        }

        tripTableBody.innerHTML = trips.map(trip => `
            <tr class="trip-row"
                data-lat="${trip.latitude ?? ''}"
                data-lng="${trip.longitude ?? ''}"
                data-destination="${escapeHtml(trip.destination)}">
                <td>#${trip.id ?? '-'}</td>
                <td>
                    <div class="destination-cell">
                        <strong>${escapeHtml(trip.destination || '-')}</strong>
                        <small>${escapeHtml(trip.country || 'No country set')}</small>
                    </div>
                </td>
                <td><span class="pill">${escapeHtml(trip.travel_date || 'Not set')}</span></td>
                <td><span class="pill">${escapeHtml(trip.budget || 'Not set')}</span></td>
                <td><span class="pill">${escapeHtml(trip.latitude || '-')} , ${escapeHtml(trip.longitude || '-')}</span></td>
                <td class="notes-cell">${escapeHtml(trip.notes || '-')}</td>
                <td>
                    <div class="action-group">
                        <a href="${editBaseUrl}/${trip.id}" class="btn-table btn-edit">Edit</a>
                        <a href="${deleteBaseUrl}/${trip.id}" class="btn-table btn-delete" onclick="return confirm('Are you sure you want to delete this trip?')">Delete</a>
                    </div>
                </td>
            </tr>
        `).join('');

        renderMapMarkers(trips);
        bindRowClicks();
    }

    function renderMapMarkers(trips) {
        if (!map || !markersLayer) return;

        markersLayer.clearLayers();

        const validTrips = trips.filter(trip => {
            const lat = parseFloat(trip.latitude);
            const lng = parseFloat(trip.longitude);
            return !isNaN(lat) && !isNaN(lng);
        });

        if (!validTrips.length) {
            map.setView([20, 0], 2);
            return;
        }

        const bounds = [];

        validTrips.forEach(trip => {
            const lat = parseFloat(trip.latitude);
            const lng = parseFloat(trip.longitude);

            const marker = L.marker([lat, lng]).bindPopup(`
                <div>
                    <strong>${escapeHtml(trip.destination || 'Trip')}</strong><br>
                    <span>${escapeHtml(trip.country || 'Unknown country')}</span>
                </div>
            `);

            markersLayer.addLayer(marker);
            bounds.push([lat, lng]);
        });

        if (bounds.length) {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
    }

    function bindRowClicks() {
        document.querySelectorAll('.trip-row').forEach(row => {
            row.addEventListener('click', function (e) {
                if (e.target.closest('a')) return;

                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                const destination = this.dataset.destination || 'Trip';

                if (!isNaN(lat) && !isNaN(lng) && map) {
                    map.setView([lat, lng], 10);
                    L.popup()
                        .setLatLng([lat, lng])
                        .setContent(`<strong>${escapeHtml(destination)}</strong>`)
                        .openOn(map);
                }
            });
        });
    }

    async function loadTrips(query = '') {
        if (!searchUrl) {
            console.error('Trip search URL is missing.');
            return;
        }

        try {
            btnRefreshTrips.disabled = true;
            btnRefreshTrips.textContent = 'Loading...';

            const response = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            renderTrips(data.trips || []);
        } catch (error) {
            console.error('Unable to load trips:', error);
        } finally {
            btnRefreshTrips.disabled = false;
            btnRefreshTrips.textContent = 'Refresh Trips';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const query = this.value.trim();

            searchTimer = setTimeout(() => {
                loadTrips(query);
            }, 250);
        });
    }

    if (btnRefreshTrips) {
        btnRefreshTrips.addEventListener('click', function () {
            loadTrips(searchInput ? searchInput.value.trim() : '');
        });
    }

    renderMapMarkers(initialTrips);
    bindRowClicks();
});