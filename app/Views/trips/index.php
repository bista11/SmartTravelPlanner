<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Travel Planner</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="page-shell">
    <div class="page-header">
        <div>
            <h1 class="page-title">Smart Travel Planner Dashboard</h1>
            <p class="page-subtitle">Manage your saved trips, view them on the map, and search them easily.</p>
        </div>
        <a href="#" class="btn-add-trip">Add New Trip</a>
    </div>

    <div class="stats-grid">
        <div class="card-ui stat-card">
            <div class="stat-label">Total Trips</div>
            <div class="stat-value"></div>
            <p class="stat-note">All saved entries</p>
        </div>

        <div class="card-ui stat-card">
            <div class="stat-label">Mapped Trips</div>
            <div class="stat-value"></div>
            <p class="stat-note">Trips with coordinates</p>
        </div>

        <div class="card-ui stat-card">
            <div class="stat-label">Countries</div>
            <div class="stat-value"></div>
            <p class="stat-note">Unique countries visited</p>
        </div>

        <div class="card-ui stat-card">
            <div class="stat-label">Total Budget</div>
            <div class="stat-value"></div>
            <p class="stat-note">Combined budget amount</p>
        </div>
    </div>

    <div class="layout-grid">
        <div class="card-ui panel">
            <h2 class="panel-title">Search Trips</h2>
            <p class="panel-subtitle">Filter trips by destination, country, or notes.</p>

            <div class="search-wrap">
                <span class="search-icon">⌕</span>
                <input type="text" id="liveSearchInput" class="form-control" placeholder="Search trips...">
            </div>

            <button class="btn btn-refresh" id="btnRefreshTrips" type="button">
                Refresh Trips
            </button>

            <div class="helper-list">
                <div class="helper-item">
                    <strong>Tip</strong>
                    <span>Click a table row to focus that trip on the map.</span>
                </div>

                <div class="helper-item">
                    <strong>Live Search</strong>
                    <span>Results update automatically as you type.</span>
                </div>
            </div>
        </div>

        <div class="card-ui panel">
            <h2 class="panel-title">Trips Map</h2>
            <p class="panel-subtitle">See all saved trip locations visually.</p>
            <div id="allTripsMap"></div>
        </div>
    </div>

    <div class="card-ui table-card">
        <div class="table-topbar">
            <h5>All Trips</h5>
            <p>Browse, edit, or delete your saved trips.</p>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Destination</th>
                        <th>Travel Date</th>
                        <th>Budget</th>
                        <th>Coordinates</th>
                        <th>Notes</th>
                        <th width="190">Actions</th>
                    </tr>
                </thead>
                <tbody id="tripTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="js/script.js"></script>

</body>
</html>