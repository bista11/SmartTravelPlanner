<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Add Trip') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Add Trip</h2>
                    <a href="<?= site_url('trips') ?>" class="btn btn-secondary">Back</a>
                </div>

                <form action="<?= site_url('trips/store') ?>" method="post">
                    <?= csrf_field() ?>
                    <?= $this->include('trips/_form') ?>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Save Trip</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>