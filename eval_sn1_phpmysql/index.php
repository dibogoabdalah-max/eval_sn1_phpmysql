<?php
session_start();session_start();
require_once __DIR__ . '/config.php';

$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];

$categorie = $_GET['categorie'] ?? '';
if (!array_key_exists($categorie, $categories)) {
    $categorie = '';
}

$sql = "SELECT id, title, date, category, image
        FROM event
        WHERE is_validated = 1 AND date > NOW()";
$params = [];

if ($categorie !== '') {
    $sql .= " AND category = :categorie";
    $params['categorie'] = $categorie;
}

$sql .= " ORDER BY date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$evenements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Événements à venir · Agenda</title>
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

    <section class="hero" style="background-image: url('images/rock.jpg')">
        <div class="hero-content">
            <span class="hero-kicker">Agenda local</span>
            <h1>Les événements près de chez vous</h1>
            <p>Concerts, conférences, ateliers et sport : trouvez votre prochaine sortie ou proposez la vôtre.</p>
            <a class="btn" href="ajouter.php">+ Ajouter un événement</a>
        </div>
    </section>

    <div class="section-head">
        <h2>À venir</h2>
        <!-- La catégorie sélectionnée porte la classe « active » -->
        <ul class="legend">
    <li>
        <a class="<?= $categorie === '' ? 'active' : '' ?>" href="index.php">Toutes</a>
    </li>
    <li class="cat-concert">
        <a class="<?= $categorie === 'concert' ? 'active' : '' ?>" href="index.php?categorie=concert">Concert</a>
    </li>
    <li class="cat-conference">
        <a class="<?= $categorie === 'conference' ? 'active' : '' ?>" href="index.php?categorie=conference">Conférence</a>
    </li>
    <li class="cat-atelier">
        <a class="<?= $categorie === 'atelier' ? 'active' : '' ?>" href="index.php?categorie=atelier">Atelier</a>
    </li>
    <li class="cat-sport">
        <a class="<?= $categorie === 'sport' ? 'active' : '' ?>" href="index.php?categorie=sport">Sport</a>
    </li>
</ul>

   <ul class="event-list">
    <?php foreach ($evenements as $evenement): ?>
        <?php
        $cat = $categories[$evenement['category']] ?? 'Autre';
        $date = new DateTime($evenement['date']);
        ?>
        <li class="event-card cat-<?= htmlspecialchars($evenement['category']) ?>">
            <div class="event-media">
                <?php if (!empty($evenement['image'])): ?>
                    <img class="event-image"
                         src="<?= htmlspecialchars($evenement['image']) ?>"
                         alt="">
                <?php endif; ?>
                <div class="event-date">
                    <span class="day"><?= $date->format('d') ?></span>
                    <span class="month"><?= $date->format('m') ?></span>
                </div>
                <span class="badge"><?= htmlspecialchars($cat) ?></span>
            </div>
            <h3 class="event-title">
                <a href="evenement.php?id=<?= (int) $evenement['id'] ?>">
                    <?= htmlspecialchars($evenement['title']) ?>
                </a>
            </h3>
            <span class="muted"><?= $date->format('d/m/Y · H\hi') ?></span>
        </li>
    <?php endforeach; ?>
</ul>

    <!-- Si aucun événement :
    <p class="empty">Aucun événement à venir pour le moment.</p>
    -->

    <!-- ===== Début du pied de page (à mettre dans un fichier footer.php) ===== -->
    </main>

    <footer class="site-footer">
        Agenda · Projet de formation PHP / MySQL
    </footer>
</body>
</html>
