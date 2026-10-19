<?php
session_start();
require_once __DIR__ . '/config.php';

$erreur = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, email, password, role FROM user WHERE email = :email'
    );
    $stmt->execute(['email' => $email]);
    $utilisateur = $stmt->fetch();

    if (!$utilisateur || !password_verify($password, $utilisateur['password'])) {
        $erreur = 'Email ou mot de passe incorrect.';
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $utilisateur['id'];
        $_SESSION['user_email'] = $utilisateur['email'];
        $_SESSION['role'] = $utilisateur['role'];

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · Agenda</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 128 128%22><text y=%221.2em%22 font-size=%2296%22>📅</text></svg>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- ===== Début de l'en-tête (à mettre dans un fichier header.php) ===== -->
    <header class="site-header">
        <div class="container">
            <a class="logo" href="index.html">Agenda<span>.</span></a>
         <nav class="nav">
    <a href="index.php">Événements</a>

    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <a href="validation.php">Validation</a>
        <a href="inscrits.php">Inscrits</a>
    <?php endif; ?>

    <?php if (!empty($_SESSION['user_id'])): ?>
        <a class="btn btn-small" href="deconnexion.php">Se déconnecter</a>
    <?php else: ?>
        <a href="connexion.php">Connexion</a>
        <a class="btn btn-small" href="inscription.php">Créer un compte</a>
    <?php endif; ?>
</nav>   
        </div>
    </header>

    <main class="container">
        <!-- Exemples de messages :
        <div class="flash flash-success">Votre événement a bien été ajouté.</div>
        <div class="flash flash-error">Une erreur est survenue.</div>
        -->
        <!-- ===== Fin de l'en-tête ===== -->

    <div class="narrow">
        <div class="card">
            <h1>Connexion</h1>
<?php if ($erreur !== ''): ?>
    <div class="flash flash-error"><?= htmlspecialchars($erreur) ?></div>
<?php endif; ?>
            <!-- Exemple de message d'erreur :
            <div class="flash flash-error">Identifiants invalides.</div>
            -->

            <form class="form" method="post">
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
       value="<?= htmlspecialchars($email) ?>"
       autocomplete="email" required autofocus>
                </div>

                <div>
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <button type="submit">Se connecter</button>
            </form>

            <p class="form-footer">Pas encore de compte ? <a href="inscription.html">Créer un compte</a></p>
        </div>
    </div>

    <!-- ===== Début du pied de page (à mettre dans un fichier footer.php) ===== -->
    </main>

    <footer class="site-footer">
        Agenda · Projet de formation PHP / MySQL
    </footer>
</body>
</html>
