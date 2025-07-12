
   <?php
   class User {
       private $pdo;

       public function __construct(PDO $pdo) {
           $this->pdo = $pdo;
       }

       public function findByEmail($email) {
           $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
           $stmt->execute([$email]);
           return $stmt->fetch();
       }

       public function register($email, $username, $password) {
           $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
           $stmt = $this->pdo->prepare('INSERT INTO users (email, username, password) VALUES (?, ?, ?)');
           return $stmt->execute([$email, $username, $hashedPassword]);
       }

       public function login($email, $password) {
           $user = $this->findByEmail($email);
           if ($user && password_verify($password, $user['password'])) {
               $_SESSION['user_id'] = $user['id'];
               $_SESSION['username'] = $user['username'];
               $_SESSION['role'] = $user['role'];
               $stmt = $this->pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
               $stmt->execute([$user['id']]);
               session_regenerate_id(true);
               return true;
           }
           return false;
       }

       public function logout() {
           session_unset();
           session_destroy();
           session_start();
           session_regenerate_id(true);
       }
   }
   ?>
