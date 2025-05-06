<!DOCTYPE html>


<html lang="en" class="h-full bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and signup page</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="h-full">
    <div class="container">
      <div id="form box">
        <h2 id="form-title">Login</h2>
        <input type="text" id="email" name="email" placeholder="Email" required> <br>
        <input type="password" id="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p id="toggle-form">Don't have an account? <span onclick="toggleForm()">signup</span></p>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>
