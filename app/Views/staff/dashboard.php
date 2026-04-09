<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; padding: 50px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .logout { color: red; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome to the Staff Dashboard, <?= $username ?>!</h1>
        <p>This is where staff members can manage AI chat logs and user inquiries.</p>
        <hr>
        <a href="/logout" class="logout">Logout</a>
    </div>
</body>
</html>