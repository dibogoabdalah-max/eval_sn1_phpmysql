<?php
session_start();
require_once __DIR__ . '/config.php';

$erreurs = [];
$titre = '';
$dateSaisie = '';
$information = '';
$categorie = 'concert';
$places = 20;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['title'] ?? '');
    $dateSaisie = trim($_POST['date'] ?? '');
    $information = trim($_POST['information'] ?? '');
    $categorie = $_POST['category'] ?? '';
    $places = filter_var($_POST['places'] ?? null, FILTER_VALIDATE_INT);

    $categoriesAutorisees = ['concert', 'conference', 'atelier', 'sport'];

    if ($titre === '') {
        $erreurs[] = 'Le titre est obligatoire.';
    }
    if ($dateSaisie === '') {
        $erreurs[] = 'La date est obligatoire.';
    }
    if ($information === '') {
        $erreurs[] = 'Les informations sont obligatoires.';
    }
    if ($places === false || $places < 1) {
        $erreurs[] = 'Le nombre de places doit être supérieur à 0.';
    }
    if (!in_array($categorie, $categoriesAutorisees, true)) {
        $erreurs[] = 'Catégorie invalide.';
    }

    $date = DateTime::createFromFormat('Y-m-d\TH:i', $dateSaisie);
    if (!$date) {
        $erreurs[] = 'La date est invalide.';
    }

    $image = null;
    $fichierImage = $_FILES['image'] ?? null;

    if ($fichierImage && $fichierImage['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($fichierImage['name'], PATHINFO_EXTENSION));
        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'webp'];
        $typesAutorises = ['image/jpeg', 'image/png', 'image/webp'];
        $typeImage = mime_content_type($fichierImage['tmp_name']);

        if (!in_array($extension, $extensionsAutorisees, true)
            || !in_array($typeImage, $typesAutorises, true)) {
            $erreurs[] = 'L’image doit être au format JPG, PNG ou WebP.';
        } elseif ($fichierImage['size'] > 5 * 1024 * 1024) {
            $erreurs[] = 'L’image ne doit pas dépasser 5 Mo.';
        }
    } elseif ($fichierImage && $fichierImage['error'] !== UPLOAD_ERR_NO_FILE) {
        $erreurs[] = 'Le transfert de l’image a échoué.';
    }

    if (!$erreurs) {
        if ($fichierImage && $fichierImage['error'] === UPLOAD_ERR_OK) {
            $nomFichier = bin2hex(random_bytes(16)) . '.' . $extension;
            $cheminImage = __DIR__ . '/uploads/' . $nomFichier;

            if (move_uploaded_file($fichierImage['tmp_name'], $cheminImage)) {
                $image = 'uploads/' . $nomFichier;
            } else {
                $erreurs[] = 'Impossible d’enregistrer l’image.';
            }
        }

        if (!$erreurs) {
            $stmt = $pdo->prepare(
                'INSERT INTO event (title, date, information, category, places, image, is_validated)
                 VALUES (:title, :date, :information, :category, :places, :image, 0)'
            );
            $stmt->execute([
                'title' => $titre,
                'date' => $date->format('Y-m-d H:i:s'),
                'information' => $information,
                'category' => $categorie,
                'places' => $places,
                'image' => $image,
            ]);

            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ajouter un événement · Agenda</title>
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

    <div class="narrow">
        <a class="back-link" href="index.html">← Retour aux événements</a>

        <div class="card">
            <h1>Ajouter un événement</h1>
            <?php foreach ($erreurs as $erreur): ?>
    <div class="flash flash-error"><?= htmlspecialchars($erreur) ?></div>
<?php endforeach; ?>
            <p class="muted">Votre événement sera visible après validation par un administrateur.</p>

            <!-- Exemple de message d'erreur :
            <div class="flash flash-error">Le titre est obligatoire.</div>
            -->

            <form class="form" method="post" enctype="multipart/form-data">
                <div>
                    <label for="title" class="required">Titre</label>
                    <input type="text" id="title" name="title"
       value="<?= htmlspecialchars($titre) ?>"
       placeholder="Ex. : Soirée jazz au Kiosque" required>
                </div>

                <div>
                    <label for="date" class="required">Date</label>
                    <input type="datetime-local" id="date" name="date"
       value="<?= htmlspecialchars($dateSaisie) ?>" required>
                </div>

                <div>
                    <label for="information" class="required">Informations</label>
                    <textarea id="information" name="information"
          placeholder="Lieu, programme, tarif..." required><?= htmlspecialchars($information) ?></textarea>
                </div>

                <div>
                    <label for="category">Catégorie</label>
                    <option value="concert" <?= $categorie === 'concert' ? 'selected' : '' ?>>Concert</option>
<option value="conference" <?= $categorie === 'conference' ? 'selected' : '' ?>>Conférence</option>
<option value="atelier" <?= $categorie === 'atelier' ? 'selected' : '' ?>>Atelier</option>
<option value="sport" <?= $categorie === 'sport' ? 'selected' : '' ?>>Sport</option>
                </div>

                <div>
                    <label for="places">Nombre de places</label>
                    <input type="number" id="places" name="places" min="1"
       value="<?= htmlspecialchars((string) $places) ?>">
                </div>

                <div>
                    <label for="image">Image à la une</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div class="help-text">JPG, PNG ou WebP.</div>
                </div>

                <button type="submit">Enregistrer l'événement</button>
            </form>
        </div>
    </div>

    <!-- ===== Début du pied de page (à mettre dans un fichier footer.php) ===== -->
    </main>

    <footer class="site-footer">
        Agenda · Projet de formation PHP / MySQL
    </footer>
</body>
</html>
