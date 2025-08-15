<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct POST Test</title>
    <!-- Include Bootstrap CSS for basic styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { padding: 50px; background-color: #f8f9fa; }
        .container { max-width: 600px; }
        .card { border-radius: 1rem; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); }
    </style>
</head>
<body>
    <div class="container">
        <div class="card p-4">
            <h2 class="mb-4 text-center">Direct POST Test for User Deletion</h2>
            <p class="text-muted text-center">
                This form will directly send a POST request to `index.php` to simulate a user deletion.
                Check your Apache/PHP error logs after submission.
            </p>
            <form action="index.php" method="POST" class="mt-4">
                <div class="mb-3">
                    <label for="userId" class="form-label">User ID to Delete:</label>
                    <input type="number" class="form-control" id="userId" name="id" value="5" required>
                    <small class="form-text text-muted">Change this to the ID of a user you want to attempt to delete (e.g., 5, 2, etc. - NOT your own admin ID).</small>
                </div>
                <input type="hidden" name="page" value="users">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger w-100 rounded-pill mt-3">Attempt Direct Delete (POST)</button>
            </form>

            <hr class="my-4">

            <h3 class="mb-3 text-center">Test Disable/Enable</h3>
            <form action="index.php" method="POST">
                <div class="mb-3">
                    <label for="userIdToggle" class="form-label">User ID to Toggle Status:</label>
                    <input type="number" class="form-control" id="userIdToggle" name="id" value="5" required>
                    <small class="form-text text-muted">Change this to the ID of a user you want to toggle status for.</small>
                </div>
                <input type="hidden" name="page" value="users">
                <input type="hidden" name="action" value="toggle_status">
                <button type="submit" class="btn btn-warning w-100 rounded-pill mt-3">Attempt Direct Toggle Status (POST)</button>
            </form>
        </div>
    </div>
</body>
</html>
