<?php
  session_start();
  include '../config/conn.php';
  $message = null;
  if(isset($_SESSION['user'])){
    header('Location: ../'.$_SESSION['user']['role']);
    exit();
  }
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $rememberMe = isset($_POST['rememberme']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user;
        if ($rememberMe) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (7 * 24 * 60 * 60);
            $stmt = $conn->prepare("INSERT INTO remember_me_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
            $hashedToken = hash('sha256', $token);
            $expiryDate = date('Y-m-d H:i:s', $expiry);
            $stmt->bind_param('iss', $user['id'], $hashedToken, $expiryDate);
            $stmt->execute();
            setcookie('rememberme', $token, $expiry, "/", "", true, true);
        }
        header('Location: ../'.$user['role']);
        exit();
    } else {
        $message = "Invalid username or password.";
    }
  }
  if (!isset($_SESSION['user']) && isset($_COOKIE['rememberme'])) {
    $token = $_COOKIE['rememberme'];
    $hashedToken = hash('sha256', $token);

    $stmt = $conn->prepare("SELECT user_id FROM remember_me_tokens WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param('s', $hashedToken);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $_SESSION['user'] = $row;
        header('Location: ../'.$row['role']);
        exit();
    } else {
        setcookie('rememberme', '', time() - 3600, "/", "", true, true);
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Login</title>

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">


  <!-- Custom styles for this template-->
  <link href="../css/sb-admin-2.min.css" rel="stylesheet">

</head>

<body style="background: linear-gradient(90deg, #FC466B 0%, #3F5EFB 100%);">

  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-5 col-lg-6 col-md-4">

        <div class="card o-hidden border-0 shadow-lg my-5">
            <!-- Nested Row within Card Body -->

                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">Sistem Informasi Inventori Material</h1>
                    <hr>
                  </div>
                  <?php if(isset($message)): ?>
                    <div style="margin:10px 0;color:red;font-weight:bold;"><?= $message ?></div>
                  <?php endif ?>
                  <form class="user" method="post">
                    <div class="form-group">
                      <input type="text" name="username" class="form-control form-control-user" id="exampleInputUsername" aria-describedby="usernameHelp" placeholder="Username...">
                    </div>
                    <div class="form-group">
                      <input type="password" name="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password..">
                    </div>
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small">
                        <input type="checkbox" class="custom-control-input" id="customCheck" name="rememberme">
                        <label class="custom-control-label" for="customCheck">Ingat akun</label>
                      </div>
                    </div>
                    <input type="submit" name="submit" class="btn btn-primary btn-user btn-block" value="Masuk">
                  </form>
                  <hr>
                  <p align="center">&copy; PT.Denis Putra Jaya Elektrik</p>
                </div>

          </div>
      </div>

    </div>

  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
