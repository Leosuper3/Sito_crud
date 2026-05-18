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

$messaggio = "";

if (isset($_POST["register"])) {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {

        $check = $pdo->prepare("
            SELECT id
            FROM utenti
            WHERE email = :email
        ");

        $check->execute([
            "email" => $email
        ]);

        if (!$check->fetch()) {

            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare("
                INSERT INTO utenti(email, password, ruolo)
                VALUES(:email, :password, 'utente')
            ");

            $stmt->execute([
                "email" => $email,
                "password" => $hash
            ]);

            $oggetto = "Registrazione completata";

            $testo = "
            Benvenuto nel sito di volontariato!

            La tua registrazione è stata completata correttamente.
            ";

            @mail($email, $oggetto, $testo);

            $messaggio = "Registrazione completata!";

        } else {

            $messaggio = "Email già registrata!";
        }
    }
}

if (isset($_POST["login"])) {

    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $pdo->prepare("
        SELECT *
        FROM utenti
        WHERE email = :email
    ");

    $stmt->execute([
        "email" => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {

        $_SESSION["utente"] = $user["email"];
        $_SESSION["ruolo"] = $user["ruolo"];
        $_SESSION["idUtente"] = $user["id"];

        header("Location: home.php");
        exit();

    } else {

        $messaggio = "Login errato!";
    }
}

?>

<!DOCTYPE html>
<html lang="it">

<head>

    <meta charset="UTF-8">

    <title>
        Login / Registrazione
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

    </div>

</nav>

<div class="container mt-5">

    <?php if ($messaggio): ?>

        <div class="alert alert-info">

            <?= $messaggio ?>

        </div>

    <?php endif; ?>

    <div class="row">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-body">

                    <h4 class="mb-4">
                        Login
                    </h4>

                    <form method="POST">

                        <div class="mb-3">

                            <label>Email</label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="mb-3">

                            <label>Password</label>

                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>

                        </div>

                        <button type="submit"
                                name="login"
                                class="btn btn-success w-100">

                            Accedi

                        </button>

                    </form>

                </div>

            </div>

        </div>

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-body">

                    <h4 class="mb-4">
                        Registrazione
                    </h4>

                    <form method="POST">

                        <div class="mb-3">

                            <label>Email</label>

                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>

                        </div>

                        <div class="mb-3">

                            <label>Password</label>

                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   required>

                        </div>

                        <button type="submit"
                                name="register"
                                class="btn btn-primary w-100">

                            Registrati

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
