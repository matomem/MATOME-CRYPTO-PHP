<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
<h1>Register</h1>
<?php if (!empty($error)): ?><p style="color:red;"> <?php echo Utils::sanitize($error); ?> </p><?php endif; ?>
<form method="post">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Register</button>
</form>
<p>Already have an account? <a href="/login">Login here</a></p>
</body>
</html> 