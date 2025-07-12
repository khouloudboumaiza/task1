<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $userModel;
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=localhost:3307;dbname=auth_db', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->userModel = new User($this->pdo);
        } catch (PDOException $e) {
            die('Erreur de connexion : ' . $e->getMessage());
        }
    }

    public function dashboard() {
        requireAuth();
        require __DIR__ . '/../../views/dashboard.php';
    }

    public function adminDashboard() {
        requireAuth();
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Accès non autorisé.';
            header('Location: /dashboard');
            exit;
        }
        // Since admin.php doesn't exist, redirect to dashboard with admin view
        $_SESSION['is_admin_view'] = true;
        require __DIR__ . '/../../views/dashboard.php';
    }

    public function profileUpdate() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Méthode non autorisée.';
            header('Location: /dashboard');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Jeton CSRF invalide.';
            header('Location: /dashboard');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $user_id = $_SESSION['user_id'];

        if (empty($username)) {
            $_SESSION['error'] = 'Le nom d\'utilisateur ne peut pas être vide.';
            header('Location: /dashboard');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'L\'email n\'est pas valide.';
            header('Location: /dashboard');
            exit;
        }

        try {
            $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Cet email est déjà utilisé par un autre utilisateur.';
                header('Location: /dashboard');
                exit;
            }

            $stmt = $this->pdo->prepare('UPDATE users SET username = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$username, $email, $user_id]);

            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;

            $_SESSION['success'] = 'Profil mis à jour avec succès.';
            header('Location: /dashboard');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du profil : ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    public function passwordUpdate() {
        requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Méthode non autorisée.';
            header('Location: /dashboard');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Jeton CSRF invalide.';
            header('Location: /dashboard');
            exit;
        }

        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $user_id = $_SESSION['user_id'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $_SESSION['error'] = 'Tous les champs de mot de passe sont requis.';
            header('Location: /dashboard');
            exit;
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Les nouveaux mots de passe ne correspondent pas.';
            header('Location: /dashboard');
            exit;
        }

        if (strlen($new_password) < 8) {
            $_SESSION['error'] = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
            header('Location: /dashboard');
            exit;
        }

        try {
            $stmt = $this->pdo->prepare('SELECT password FROM users WHERE id = ?');
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($current_password, $user['password'])) {
                $_SESSION['error'] = 'Le mot de passe actuel est incorrect.';
                header('Location: /dashboard');
                exit;
            }

            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare('UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$new_password_hash, $user_id]);

            $_SESSION['success'] = 'Mot de passe mis à jour avec succès.';
            header('Location: /dashboard');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Erreur lors de la mise à jour du mot de passe : ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }

    public function adminAction() {
        requireAuth();
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Accès non autorisé.';
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Méthode non autorisée.';
            header('Location: /dashboard');
            exit;
        }

        if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
            $_SESSION['error'] = 'Jeton CSRF invalide.';
            header('Location: /dashboard');
            exit;
        }

        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'list_users') {
                $stmt = $this->pdo->query('SELECT id, username, email, role FROM users');
                $_SESSION['admin_result'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $_SESSION['success'] = 'Liste des utilisateurs récupérée.';
                header('Location: /dashboard');
                exit;
            } elseif ($action === 'delete_user') {
                $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
                if (!$user_id || $user_id <= 0) {
                    $_SESSION['error'] = 'ID utilisateur invalide.';
                    header('Location: /dashboard');
                    exit;
                }
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['error'] = 'Vous ne pouvez pas supprimer votre propre compte.';
                    header('Location: /dashboard');
                    exit;
                }
                $stmt = $this->pdo->prepare('SELECT id, username FROM users WHERE id = ?');
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$user) {
                    $_SESSION['error'] = 'Utilisateur non trouvé.';
                    header('Location: /dashboard');
                    exit;
                }
                $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
                $stmt->execute([$user_id]);
                $_SESSION['success'] = 'Utilisateur "' . htmlspecialchars($user['username']) . '" supprimé avec succès.';
                header('Location: /dashboard');
                exit;
            } else {
                $_SESSION['error'] = 'Action non reconnue.';
                header('Location: /dashboard');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Erreur lors de l\'action : ' . $e->getMessage();
            header('Location: /dashboard');
            exit;
        }
    }
}
?>