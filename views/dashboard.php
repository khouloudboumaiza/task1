<?php
requireAuth();
$page_title = 'Dashboard';
require  'includes/header.php';

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost:3307;dbname=auth_db', 'root', ''); // Ajuste le mot de passe si nécessaire
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de l'utilisateur connecté
    $stmt = $pdo->prepare('SELECT username, email, role FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Définir les valeurs par défaut si aucune donnée n'est trouvée
    $username = htmlspecialchars($user['username'] ?? 'User');
    $email = htmlspecialchars($user['email'] ?? 'email');
    $role = htmlspecialchars($user['role'] ?? 'user');
} catch (PDOException $e) {
    $_SESSION['error'] = 'Erreur de connexion à la base de données : ' . $e->getMessage();
    $username = htmlspecialchars($_SESSION['username'] ?? 'User');
    $email = htmlspecialchars($_SESSION['email'] ?? 'email');
    $role = htmlspecialchars($_SESSION['role'] ?? 'user');
}
?>

<div class="dash-min-vh-100 dash-bg-light">
    <header class="dash-bg-white">
        <div class="dash-container dash-py-4">
            <div class="dash-d-flex dash-justify-content-between dash-align-items-center">
                <div>
                    <h1 class="dash-h3 dash-mb-1 dash-fw-bold dash-text-dark">Tableau de Bord</h1>
                    <p class="dash-text-muted dash-mb-0">
                        Bienvenue, <?php echo $username; ?>!
                    </p>
                </div>
                <div class="dash-d-flex dash-align-items-center dash-gap-3">
                    <?php if ($role === 'admin'): ?>
                        <span class="dash-badge dash-bg-primary dash-text-white">Administrateur</span>
                    <?php else: ?>
                        <span class="dash-badge dash-bg-secondary dash-text-white">Utilisateur</span>
                    <?php endif; ?>
                    <form action="/logout" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <button type="submit" class="dash-btn dash-btn-outline-danger">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="dash-container dash-py-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="dash-alert dash-alert-success dash-alert-dismissible dash-fade dash-show" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="dash-btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="dash-alert dash-alert-danger dash-alert-dismissible dash-fade dash-show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="dash-btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_result'])): ?>
            <div class="dash-alert dash-alert-info">
                <h6>Liste des utilisateurs :</h6>
                <ul>
                    <?php foreach ($_SESSION['admin_result'] as $user): ?>
                        <li>ID: <?php echo htmlspecialchars($user['id']); ?>, Nom: <?php echo htmlspecialchars($user['username']); ?>, Email: <?php echo htmlspecialchars($user['email']); ?>, Rôle: <?php echo htmlspecialchars($user['role']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php unset($_SESSION['admin_result']); ?>
            </div>
        <?php endif; ?>

        <div class="dash-row dash-g-4 dash-justify-content-center">
            <!-- Informations du profil -->
            <div class="dash-col-md-6 dash-col-lg-4">
                <div class="dash-card dash-h-100">
                    <div class="dash-card-header">Informations du Profil</div>
                    <div class="dash-card-body">
                        <h5 class="dash-card-title">Vos informations personnelles</h5>
                        <div class="dash-mt-3">
                            <p><strong>Nom :</strong> <?php echo $username; ?></p>
                            <p><strong>Email :</strong> <?php echo $email; ?></p>
                            <p><strong>Rôle :</strong> <?php echo $role; ?></p>
                        </div>
                    </div>
                   
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="dash-col-md-6 dash-col-lg-4">
                <div class="dash-card dash-h-100">
                    <div class="dash-card-header">Actions rapides</div>
                    <div class="dash-card-body">
                        <h5 class="dash-card-title">Gérez votre compte</h5>
                        <div class="dash-d-flex dash-flex-column dash-gap-3">
                            <button type="button" class="dash-btn dash-btn-outline-primary dash-w-100 dash-text-start" onclick="openPopup('editProfilePopup')">
                                <svg class="dash-bi dash-me-2" width="16" height="16" fill="currentColor"><use xlink:href="/public/icons/bootstrap-icons.svg#gear"/></svg>
                                Modifier le profil
                            </button>
                            <button type="button" class="dash-btn dash-btn-outline-primary dash-w-100 dash-text-start" onclick="openPopup('changePasswordPopup')">
                                <svg class="dash-bi dash-me-2" width="16" height="16" fill="currentColor"><use xlink:href="/public/icons/bootstrap-icons.svg#shield-lock"/></svg>
                                Changer le mot de passe
                            </button>
                            <?php if ($role === 'admin'): ?>
                                <button type="button" class="dash-btn dash-btn-primary dash-w-100 dash-text-start" onclick="openPopup('adminPanelPopup')">
                                    <svg class="dash-bi dash-me-2" width="16" height="16" fill="currentColor"><use xlink:href="/public/icons/bootstrap-icons.svg#shield-lock"/></svg>
                                    Panel d'administration
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administration (pour les admins uniquement) -->
           
        </div>

        <!-- Popup pour modifier le profil -->
        <div class="dash-popup" id="editProfilePopup">
            <div class="dash-popup-content">
                <div class="dash-popup-header">
                    <h5 class="dash-popup-title">Modifier le Profil</h5>
                    <button type="button" class="dash-popup-close" onclick="closePopup('editProfilePopup')">×</button>
                </div>
                <form action="/profile-update" method="POST">
                    <div class="dash-popup-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="dash-row dash-justify-content-center">
                            <div class="dash-col-md-6 dash-mb-3">
                                <label for="username" class="dash-form-label">Nom d'utilisateur</label>
                                <input type="text" class="dash-form-control" id="username" name="username" value="<?php echo $username; ?>" required>
                            </div>
                            <div class="dash-col-md-6 dash-mb-3">
                                <label for="email" class="dash-form-label">Email</label>
                                <input type="email" class="dash-form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="dash-popup-footer">
                        <button type="button" class="dash-btn dash-btn-secondary" onclick="closePopup('editProfilePopup')">Annuler</button>
                        <button type="submit" class="dash-btn dash-btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup pour changer le mot de passe -->
        <div class="dash-popup" id="changePasswordPopup">
            <div class="dash-popup-content">
                <div class="dash-popup-header">
                    <h5 class="dash-popup-title">Changer le Mot de Passe</h5>
                    <button type="button" class="dash-popup-close" onclick="closePopup('changePasswordPopup')">×</button>
                </div>
                <form action="/password-update" method="POST">
                    <div class="dash-popup-body">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                        <div class="dash-row dash-justify-content-center">
                            <div class="dash-col-md-12 dash-mb-3">
                                <label for="current_password" class="dash-form-label">Mot de passe actuel</label>
                                <input type="password" class="dash-form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="dash-col-md-6 dash-mb-3">
                                <label for="new_password" class="dash-form-label">Nouveau mot de passe</label>
                                <input type="password" class="dash-form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="dash-col-md-6 dash-mb-3">
                                <label for="confirm_password" class="dash-form-label">Confirmer le mot de passe</label>
                                <input type="password" class="dash-form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>
                    <div class="dash-popup-footer">
                        <button type="button" class="dash-btn dash-btn-secondary" onclick="closePopup('changePasswordPopup')">Annuler</button>
                        <button type="submit" class="dash-btn dash-btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Popup pour le panel d'administration -->
        <div class="dash-popup" id="adminPanelPopup">
            <div class="dash-popup-content">
                <div class="dash-popup-header">
                    <h5 class="dash-popup-title">Panel d'Administration</h5>
                    <button type="button" class="dash-popup-close" onclick="closePopup('adminPanelPopup')">×</button>
                </div>
                <div class="dash-popup-body">
                    <div class="dash-row dash-justify-content-center">
                        <div class="dash-col-md-12 dash-mb-3">
                            <h6>Gérer les utilisateurs</h6>
                            <p>Fonctions administratives pour gérer les comptes.</p>
                            <form action="/admin-action" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                <div class="dash-mb-3">
                                    <label for="action" class="dash-form-label">Action</label>
                                    <select class="dash-form-control" id="action" name="action" required>
                                        <option value="" disabled selected>Choisir une action</option>
                                        <option value="list_users">Lister les utilisateurs</option>
                                        <option value="delete_user">Supprimer un utilisateur</option>
                                    </select>
                                </div>
                                <div class="dash-mb-3" id="user_id_field" style="display: none;">
                                    <label for="user_id" class="dash-form-label">ID utilisateur</label>
                                    <input type="number" class="dash-form-control" id="user_id" name="user_id">
                                </div>
                                <button type="submit" class="dash-btn dash-btn-primary dash-w-100">Exécuter</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="dash-popup-footer">
                    <button type="button" class="dash-btn dash-btn-secondary" onclick="closePopup('adminPanelPopup')">Fermer</button>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    function openPopup(popupId) {
        const popup = document.getElementById(popupId);
        if (popup) {
            popup.style.display = 'flex';
            setTimeout(() => {
                popup.querySelector('.dash-popup-content').style.opacity = '1';
            }, 10);
        }
    }

    function closePopup(popupId) {
        const popup = document.getElementById(popupId);
        if (popup) {
            popup.querySelector('.dash-popup-content').style.opacity = '0';
            setTimeout(() => {
                popup.style.display = 'none';
            }, 200);
        }
    }

    document.querySelector('#action')?.addEventListener('change', function() {
        const userIdField = document.querySelector('#user_id_field');
        userIdField.style.display = this.value === 'delete_user' ? 'block' : 'none';
    });

    document.querySelectorAll('.dash-popup').forEach(popup => {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                closePopup(this.id);
            }
        });
    });
</script>

<?php require  'includes/footer.php'; ?>