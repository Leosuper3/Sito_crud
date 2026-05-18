<?php
session_start();

try {

    $pdo = new PDO(
        "mysql:host=localhost;dbname=volontariato;charset=utf8",
        "root",
        ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {

    die("Errore connessione: " . $e->getMessage());
}

if (!isset($_SESSION["utente"])) {

    header("Location: index.php");
    exit();
}

$messaggio = "";
$editUser = null;

if (isset($_GET["delete"]) && $_SESSION["ruolo"] == "admin") {

    $stmt = $pdo->prepare("
        DELETE FROM utenti
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $_GET["delete"]
    ]);

    $messaggio = "Utente eliminato!";
}

if (isset($_GET["edit"])) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM utenti
        WHERE id = :id
    ");

    $stmt->execute([
        "id" => $_GET["edit"]
    ]);

    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST["create"])) {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $ruolo = $_POST["ruolo"];

    $hash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = $pdo->prepare("
        INSERT INTO utenti(email, password, ruolo)
        VALUES(:email, :password, :ruolo)
    ");

    $stmt->execute([
        "email" => $email,
        "password" => $hash,
        "ruolo" => $ruolo
    ]);

    $oggetto = "Nuovo account creato";

    $testo = "
    Benvenuto nel sito di volontariato!
    ";

    @mail($email, $oggetto, $testo);

    $messaggio = "Utente creato!";
}

if (isset($_POST["update"])) {

    $id = $_POST["id"];
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $ruolo = $_POST["ruolo"];

    if (!empty($password)) {

        $hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $stmt = $pdo->prepare("
            UPDATE utenti
            SET email = :email,
                password = :password,
                ruolo = :ruolo
            WHERE id = :id
        ");

        $stmt->execute([
            "email" => $email,
            "password" => $hash,
            "ruolo" => $ruolo,
            "id" => $id
        ]);

    } else {

        $stmt = $pdo->prepare("
            UPDATE utenti
            SET email = :email,
                ruolo = :ruolo
            WHERE id = :id
        ");

        $stmt->execute([
            "email" => $email,
            "ruolo" => $ruolo,
            "id" => $id
        ]);
    }

    $messaggio = "Utente aggiornato!";
}

$utenti = $pdo->query("
    SELECT *
    FROM utenti
    ORDER BY id DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="UTF-8">

    <title>
        Gestione Utenti
    </title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<nav class="navbar navbar-dark bg-success">

    <div class="container">

        <span class="navbar-brand">
            Sito Volontariato
        </span>

        <div class="text-white">

            <?= $_SESSION["utente"] ?>

            |

            <?= $_SESSION["ruolo"] ?>

            |

            <a href="logout.php" class="text-white">
                Logout
            </a>

        </div>

    </div>

</nav>

<div class="container mt-4">

    <?php if ($messaggio): ?>

        <div class="alert alert-info">

            <?= $messaggio ?>

        </div>

    <?php endif; ?>

    <div class="row">

        <div class="col-md-4">

            <div class="card">

                <div class="card-body">

                    <h4>

                        <?= $editUser ? "Modifica Utente" : "Nuovo Utente" ?>

                    </h4>

                    <form method="POST">

                        <input type="hidden"
                               name="id"
                               value="<?= $editUser["id"] ?? "" ?>">

                        <div class="mb-3">

                            <label>Email</label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="<?= $editUser["email"] ?? "" ?>"
                                   required>

                        </div>

                        <div class="mb-3">

                            <label>Password</label>

                            <input type="password"
                                   name="password"
                                   class="form-control">

                        </div>

                        <div class="mb-3">

                            <label>Ruolo</label>

                            <select name="ruolo" class="form-control">

                                <option value="utente">
                                    Utente
                                </option>

                                <option value="admin">
                                    Admin
                                </option>

                            </select>

                        </div>

                        <button type="submit"
                                name="<?= $editUser ? "update" : "create" ?>"
                                class="btn btn-success w-100">

                            Salva

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-md-8">

            <div class="card">

                <div class="card-body">

                    <h4>
                        Utenti
                    </h4>

                    <table class="table table-bordered mt-3">

                        <thead>

                        <tr>

                            <th>ID</th>
                            <th>Email</th>
                            <th>Ruolo</th>
                            <th>Azioni</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($utenti as $u): ?>

                            <tr>

                                <td><?= $u["id"] ?></td>

                                <td><?= $u["email"] ?></td>

                                <td><?= $u["ruolo"] ?></td>

                                <td>

                                    <a href="?edit=<?= $u["id"] ?>"
                                       class="btn btn-warning btn-sm">

                                        Modifica

                                    </a>

                                    <?php if ($_SESSION["ruolo"] == "admin"): ?>

                                        <a href="?delete=<?= $u["id"] ?>"
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Eliminare utente?')">

                                            Elimina

                                        </a>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
```
