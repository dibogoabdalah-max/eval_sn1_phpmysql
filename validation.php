<?php
session_start();
require_once __DIR__ . '/config.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE event SET is_validated = 1
             WHERE id = :id AND is_validated = 0'
        );
        $stmt->execute(['id' => $id]);
    }

    header('Location: validation.php');
    exit;
}

$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];

$stmt = $pdo->query(
    'SELECT id, title, date, category, places
     FROM event
     WHERE is_validated = 0
     ORDER BY date ASC'
);
$evenements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Validation des événements · Agenda</title>
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
                <a href="index.html">Événements</a>

                <!-- Lien visible UNIQUEMENT par les administrateurs -->
                <a href="validation.html">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                    Validation
                </a>

                <!-- Lien visible UNIQUEMENT par les administrateurs -->
                <a href="inscrits.html">
                    <svg class="icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v1"/><path d="M16 4a4 4 0 0 1 0 8"/><path d="M19 14a5 5 0 0 1 3 5v2"/></svg>
                    Inscrits
                </a>

                <!-- Utilisateur NON connecté -->
                <a href="connexion.html">Connexion</a>
                <a class="btn btn-small" href="inscription.html">Créer un compte</a>

                <!-- Variante quand l'utilisateur est connecté (remplace « Connexion » et « Créer un compte ») :
                <a class="btn btn-small" href="deconnexion.php">Se déconnecter</a>
                -->
            </nav>
        </div>
    </header>

    <main class="container">
        <!-- Exemples de messages :
        <div class="flash flash-success">Votre événement a bien été ajouté.</div>
        <div class="flash flash-error">Une erreur est survenue.</div>
        -->
        <!-- ===== Fin de l'en-tête ===== -->

    <div class="section-head">
        <div>
            <h1>Événements à valider</h1>
            <p class="muted"><?= count($evenements) ?> événement(s) en attente de validation.</p>
        </div>
    </div>

    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Événement</th>
                    <th>Date</th>
                    <th>Catégorie</th>
                    <th>Places</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
        <tbody>
    <?php if ($evenements): ?>
        <?php foreach ($evenements as $evenement): ?>
            <?php
            $date = new DateTime($evenement['date']);
            $categorie = $categories[$evenement['category']] ?? 'Autre';
            ?>
            <tr>
                <td>
                    <a href="evenement.php?id=<?= (int) $evenement['id'] ?>">
                        <?= htmlspecialchars($evenement['title']) ?>
                    </a>
                </td>
                <td><?= $date->format('d/m/Y H\hi') ?></td>
                <td>
                    <span class="badge cat-<?= htmlspecialchars($evenement['category']) ?>">
                        <?= htmlspecialchars($categorie) ?>
                    </span>
                </td>
                <td><?= (int) $evenement['places'] ?></td>
                <td><span class="badge badge-pending">Non validé</span></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id" value="<?= (int) $evenement['id'] ?>">
                        <button type="submit" class="btn btn-small btn-success">Valider</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6">Aucun événement en attente 🎉</td>
        </tr>
    <?php endif; ?>
</tbody>
        </table>
    </div>

    <!-- Si aucun événement à valider :
    <p class="empty">Aucun événement en attente 🎉</p>
    -->

    <!-- ===== Début du pied de page (à mettre dans un fichier footer.php) ===== -->
    </main>

    <footer class="site-footer">
        Agenda · Projet de formation PHP / MySQL
    </footer>
</body>
</html>
