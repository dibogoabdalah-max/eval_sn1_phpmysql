<?php
session_start();
require_once __DIR__ . '/config.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query(
    'SELECT e.id, e.title, e.date, e.category, e.places,
            (SELECT COUNT(*) FROM reservation r WHERE r.event_id = e.id) AS nombre_inscrits
     FROM event e
     WHERE e.is_validated = 1
     ORDER BY e.date ASC'
);
$evenements = $stmt->fetchAll();

$stmt = $pdo->query(
    'SELECT r.event_id, u.email, r.created_at
     FROM reservation r
     INNER JOIN user u ON u.id = r.user_id
     INNER JOIN event e ON e.id = r.event_id
     WHERE e.is_validated = 1
     ORDER BY r.event_id, r.created_at'
);

$inscritsParEvenement = [];
foreach ($stmt->fetchAll() as $inscrit) {
    $inscritsParEvenement[$inscrit['event_id']][] = $inscrit;
}

$categories = [
    'concert' => 'Concert',
    'conference' => 'Conférence',
    'atelier' => 'Atelier',
    'sport' => 'Sport',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscrits aux événements · Agenda</title>
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
            <h1>Inscrits aux événements</h1>
            <p class="muted">Liste des participants pour chaque événement validé.</p>
        </div>
    </div>

    <!-- Page réservée aux administrateurs -->
    <!-- Un bloc par événement validé -->
    <section class="card attendees">
        <div class="attendees-head">
            <div>
                <h2><a href="evenement.html?id=1">Soirée jazz au Kiosque</a></h2>
                <span class="badge cat-concert">Concert</span>
                <span class="muted">07/11/2026 · 20h30</span>
            </div>
            <p class="places"><strong>3</strong> inscrits sur 80 places</p>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>user@agenda.fr</td>
                        <td>18/10/2026 10h12</td>
                    </tr>
                    <tr>
                        <td>lea.martin@test.fr</td>
                        <td>19/10/2026 08h45</td>
                    </tr>
                    <tr>
                        <td>tom.durand@test.fr</td>
                        <td>20/10/2026 17h30</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card attendees">
        <div class="attendees-head">
            <div>
                <h2><a href="evenement.html?id=2">L'IA expliquée simplement</a></h2>
                <span class="badge cat-conference">Conférence</span>
                <span class="muted">12/11/2026 · 18h00</span>
            </div>
            <p class="places"><strong>0</strong> inscrit sur 60 places</p>
        </div>

        <!-- Si aucun inscrit pour cet événement -->
        <p class="empty">Aucun inscrit pour le moment.</p>
    </section>

    <section class="card attendees">
        <div class="attendees-head">
            <div>
                <h2><a href="evenement.html?id=7">Atelier tricot pour débutants</a></h2>
                <span class="badge cat-atelier">Atelier</span>
                <span class="muted">17/12/2026 · 18h30</span>
            </div>
            <!-- Quand l'événement est complet -->
            <p class="places"><span class="badge badge-full">Complet</span> <strong>2</strong> inscrits sur 2 places</p>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Inscrit le</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>user@agenda.fr</td>
                        <td>18/10/2026 10h20</td>
                    </tr>
                    <tr>
                        <td>lea.martin@test.fr</td>
                        <td>21/10/2026 14h05</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Si aucun événement validé :
    <p class="empty">Aucun événement validé pour le moment.</p>
    -->

    <!-- ===== Début du pied de page (à mettre dans un fichier footer.php) ===== -->
    </main>

    <footer class="site-footer">
        Agenda · Projet de formation PHP / MySQL
    </footer>
</body>
</html>
