
   <?php
   require_once __DIR__ . '/../../config/database.php';

   class AuthController {
       private $user;

       public function __construct() {
           global $pdo;
           $this->user = new User($pdo);
       }

       public function showLogin() {
           require __DIR__ . '/../../views/auth/login.php';
       }

       public function showRegister() {
           require __DIR__ . '/../../views/auth/register.php';
       }

       public function login() {
           if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'])) {
               $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
               $password = $_POST['password'];
               
               if ($this->user->login($email, $password)) {
                   header('Location: /dashboard');
                   exit;
               } else {
                   $_SESSION['error'] = 'Invalid credentials';
                   header('Location: /login');
                   exit;
               }
           }
       }

       public function register() {
           if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCsrfToken($_POST['csrf_token'])) {
               $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
               $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
               $password = $_POST['password'];
               
               if (strlen($password) < 8) {
                   $_SESSION['error'] = 'Password must be at least 8 characters';
                   header('Location: /register');
                   exit;
               }
               
               if ($this->user->findByEmail($email)) {
                   $_SESSION['error'] = 'Email already exists';
                   header('Location: /register');
                   exit;
               }
               
               if ($this->user->register($email, $username, $password)) {
                   $this->user->login($email, $password);
                   header('Location: /dashboard');
                   exit;
               } else {
                   $_SESSION['error'] = 'Registration failed';
                   header('Location: /register');
                   exit;
               }
           }
       }

       public function logout() {
           $this->user->logout();
           header('Location: /login');
           exit;
       }
   }
   ?>
  