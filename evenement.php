<?php
session_start();
require_once __DIR__ . '/config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(404);
    require __DIR__ . '/404.html';
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, title, date, information, category, places, image
     FROM event
     WHERE id = :id'
);
$stmt->execute(['id' => $id]);
$evenement = $stmt->fetch();

if (!$evenement) {
    http_response_code(404);
    require __DIR__ . '/404.html';
    exit;
}

$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];

$nomCategorie = $categories[$evenement['category']] ?? 'Événement';
$stmtReservations = $pdo->prepare(
    'SELECT COUNT(*) FROM reservation WHERE event_id = :id'
);
$stmtReservations->execute(['id' => $id]);
$nombreInscrits = (int) $stmtReservations->fetchColumn();

$placesRestantes = max(0, (int) $evenement['places'] - $nombreInscrits);
$pourcentage = (int) round(
    $nombreInscrits * 100 / max(1, (int) $evenement['places'])
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Soirée jazz au Kiosque · Agenda</title>
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

    <a class="back-link" href="index.html">← Retour aux événements</a>

    <?php $dateEvenement = new DateTime($evenement['date']); ?>

<article class="cat-<?= htmlspecialchars($evenement['category']) ?>">
    <header class="detail-hero"
            style="background-image: url('<?= htmlspecialchars($evenement['image'] ?? 'images/concert.jpg', ENT_QUOTES) ?>')">
        <div class="detail-hero-content">
            <span class="badge"><?= htmlspecialchars($nomCategorie) ?></span>
            <h1><?= htmlspecialchars($evenement['title']) ?></h1>
            <p><?= $dateEvenement->format('d/m/Y · H\hi') ?></p>
        </div>
    </header>

    <div class="detail-grid">
        <section class="card">
            <h2>Informations</h2>
            <p><?= nl2br(htmlspecialchars($evenement['information'])) ?></p>
        </section>

        <aside class="card booking">
            <?php if ($placesRestantes === 0): ?>
                <p class="places"><span class="badge badge-full">Complet</span></p>
            <?php else: ?>
                <p class="places">
                    <strong><?= $placesRestantes ?></strong>
                    places restantes sur <?= (int) $evenement['places'] ?>
                </p>
            <?php endif; ?>

            <div class="progress">
                <span style="width: <?= min(100, $pourcentage) ?>%"></span>
            </div>
        </aside>
    </div>
</article>

    <!-- ===== Début du pied de page (à mettre dans un fichier footer.php) ===== -->
    </main>

    <footer class="site-footer">
        Agenda · Projet de formation PHP / MySQL
    </footer>
</body>
</html>
