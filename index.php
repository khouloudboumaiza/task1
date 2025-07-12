
   <?php
   session_start();
   require_once 'config/database.php';
   require_once 'includes/auth.php';

   // Autoloader
   spl_autoload_register(function ($class_name) {
       $class_map = [
           'AuthController' => 'src/contollers/AuthController.php',
           'UserController' => 'src/contollers/UserController.php',
           'User' => 'src/models/User.php',
       ];

       if (isset($class_map[$class_name])) {
           $file = __DIR__ . '/' . $class_map[$class_name];
           if (file_exists($file)) {
               require_once $file;
           } else {
               echo "Debug: File not found for class $class_name at $file<br>";
           }
       } else {
           echo "Debug: Class $class_name not mapped in autoloader<br>";
       }
   });

   require_once 'src/routes/routes.php';

   // Route the request
   $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
   route($path);
   ?>
