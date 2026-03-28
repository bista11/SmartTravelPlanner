<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trip</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px;">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Edit Trip</h2>
                <a href="#" class="btn btn-secondary">Back</a>
            </div>

            <form>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Destination</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Travel Date</label>
                        <input type="date" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Budget</label>
                        <input type="number" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Latitude</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Longitude</label>
                        <input type="text" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning">Update Trip</button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>