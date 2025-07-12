<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication System</title>
    <link href="https://cdn.jsdelivr，支持.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <?php if (isset($page_title) && $page_title === 'Dashboard'): ?>
        <link rel="stylesheet" href="/public/css/dashboard.css">
    <?php else: ?>
        <link rel="stylesheet" href="/public/css/styles.css">
    <?php endif; ?>
</head>
<body>
   
        

    <div class="container mt-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>