<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Smart Travel Planner') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body>
<?php
    $tripCount = count($trips ?? []);
    $mappedTrips = array_filter($trips ?? [], static function ($trip) {
        return !empty($trip['latitude']) && !empty($trip['longitude']);
    });
    $mappedCount = count($mappedTrips);

    $countries = [];
    $budgets = [];

    foreach (($trips ?? []) as $trip) {
        if (!empty($trip['country'])) {
            $countries[] = trim((string) $trip['country']);
        }
        if (isset($trip['budget']) && $trip['budget'] !== '' && $trip['budget'] !== null) {
            $budgets[] = (float) $trip['budget'];
        }
    }

    $uniqueCountryCount = count(array_unique($countries));
    $totalBudget = array_sum($budgets);
?>

<div class="page-shell">
    <div class="page-header">
        <div>
            <h1 class="page-title">Smart Travel Planner Dashboard</h1>
            <p class="page-subtitle">Manage your saved trips, view them on the map, and search them easily.</p>
        </div>

        <a href="<?= site_url('trips/create') ?>" class="btn-add-trip">Add New Trip</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="flash-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="card-ui stat-card">
            <div class="stat-label">Total Trips</div>
            <div class="stat-value"><?= esc($tripCount) ?></div>
            <p class="stat-note">All saved entries</p>
        </div>

        <div class="card-ui stat-card">
            <div class="stat-label">Mapped Trips</div>
            <div class="stat-value"><?= esc($mappedCount) ?></div>
            <p class="stat-note">Trips with coordinates</p>
        </div>

        <div class="card-ui stat-card">
            <div class="stat-label">Countries</div>
            <div class="stat-value"><?= esc($uniqueCountryCount) ?></div>
            <p class="stat-note">Unique countries visited</p>
        </div>

        <div class="card-ui stat-card">
            <div class="stat-label">Total Budget</div>
            <div class="stat-value"><?= esc(number_format($totalBudget, 2)) ?></div>
            <p class="stat-note">Combined budget amount</p>
        </div>
    </div>

    <div class="layout-grid">
        <div class="card-ui panel">
            <h2 class="panel-title">Search Trips</h2>
            <p class="panel-subtitle">Filter trips by destination, country, or notes.</p>

            <div class="search-wrap">
                <span class="search-icon">⌕</span>
                <input
                    type="text"
                    id="liveSearchInput"
                    class="form-control"
                    placeholder="Search trips..."
                    value="<?= esc($search ?? '') ?>"
                >
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
                <tbody id="tripTableBody">
                    <?php if (!empty($trips)): ?>
                        <?php $i = 1; ?>
                        <?php foreach ($trips as $trip): ?>
                            <tr class="trip-row"
                                data-lat="<?= esc($trip['latitude']) ?>"
                                data-lng="<?= esc($trip['longitude']) ?>"
                                data-destination="<?= esc($trip['destination']) ?>">
                                
                                <td>#<?= $i++ ?></td>

                                <td>
                                    <div class="destination-cell">
                                        <strong><?= esc($trip['destination']) ?></strong>
                                        <small><?= esc($trip['country'] ?: 'No country set') ?></small>
                                    </div>
                                </td>

                                <td><span class="pill"><?= esc($trip['travel_date'] ?: 'Not set') ?></span></td>
                                <td><span class="pill"><?= esc($trip['budget'] ?: 'Not set') ?></span></td>
                                <td>
                                    <span class="pill">
                                        <?= esc($trip['latitude'] ?: '-') ?>, <?= esc($trip['longitude'] ?: '-') ?>
                                    </span>
                                </td>
                                <td class="notes-cell"><?= esc($trip['notes'] ?: '-') ?></td>
                                <td>
                                    <div class="action-group">
                                        <a href="<?= site_url('trips/edit/' . $trip['id']) ?>" class="btn-table btn-edit">Edit</a>
                                        <a href="<?= site_url('trips/delete/' . $trip['id']) ?>" class="btn-table btn-delete" onclick="return confirm('Are you sure you want to delete this trip?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state">No trips found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    window.tripPageConfig = {
        searchUrl: "<?= site_url('api/trips/search') ?>",
        editBaseUrl: "<?= site_url('trips/edit') ?>",
        deleteBaseUrl: "<?= site_url('trips/delete') ?>",
        initialTrips: <?= json_encode($trips, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };
</script>
<script src="<?= base_url('js/script.js') ?>"></script>
</body>
</html>