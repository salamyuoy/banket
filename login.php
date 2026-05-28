<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db.php');
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $stmt = $con->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_name'] = $user['fullname'];
            if ($user['is_admin'] == 1 || $user['login'] == 'Admin26') { $_SESSION['admin'] = true; header('Location: admin.php'); }
            else { header('Location: create.php'); }
            exit;
        } else $error = 'Неверный логин или пароль';
    } else $error = 'Неверный логин или пароль';
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --gold: #DAA520; --rose-gold: #FFDAB9; --forest-green: #006400; --crimson: #DC143C; --dark-green: #003300; }
        body { font-family: 'Oswald', sans-serif; background: linear-gradient(135deg, var(--forest-green) 0%, var(--dark-green) 100%); display: flex; flex-direction: column; min-height: 100vh; }
        .header { background: rgba(0, 40, 0, 0.95); padding: 15px 20px; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(10px); }
        .nav { display: flex; justify-content: space-between; align-items: center; max-width: 1200px; margin: 0 auto; flex-wrap: wrap; gap: 15px; }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo img { height: 40px; width: auto; }
        .logo span { color: var(--gold); font-size: 24px; font-weight: 700; letter-spacing: 2px; }
        .nav-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav-buttons a { padding: 8px 20px; border: 2px solid var(--gold); border-radius: 25px; color: var(--gold); text-decoration: none; transition: all 0.3s ease; font-weight: 500; }
        .nav-buttons a:hover { background: var(--gold); color: var(--forest-green); transform: translateY(-2px); }
        .main-content { flex: 1; position: relative; padding: 40px 20px; display: flex; justify-content: center; align-items: center; }
        .main-content::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('images/fon.jpg'); background-size: cover; background-position: center; background-attachment: fixed; opacity: 0.35; z-index: 0; }
        .main-content::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 30, 0, 0.3); z-index: 1; }
        .main-content > * { position: relative; z-index: 2; }
        .container { max-width: 450px; width: 100%; background: rgba(255,255,255,0.9); padding: 40px; border-radius: 20px; backdrop-filter: blur(5px); }
        h1 { text-align: center; color: var(--forest-green); font-size: 32px; margin-bottom: 10px; }
        h2 { text-align: center; color: var(--forest-green); margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--forest-green); font-weight: 500; }
        input { width: 100%; padding: 14px; border: 2px solid var(--rose-gold); border-radius: 12px; font-family: 'Oswald', sans-serif; font-size: 16px; }
        input:focus { outline: none; border-color: var(--gold); }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--gold), var(--forest-green)); color: white; border: none; border-radius: 12px; font-size: 18px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(218,165,32,0.4); }
        .error-message { background: #f8d7da; color: var(--crimson); padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .footer-links { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--rose-gold); }
        .footer-links a { color: var(--gold); text-decoration: none; }
        footer { text-align: center; padding: 25px; background: var(--forest-green); color: var(--gold); border-top: 2px solid var(--gold); }
        @media (max-width: 550px) { .container { padding: 25px; } }
    </style>
</head>
<body>
<header class="header">
    <div class="nav">
        <a href="index.php" class="logo"><img src="images/logo.png" alt="Логотип"><span>Банкетам.Нет</span></a>
        <div class="nav-buttons">
            <a href="index.php">🏠 Главная</a>
            <a href="register.php">📝 Регистрация</a>
        </div>
    </div>
</header>
<div class="main-content">
    <div class="container">
        <h1>🍽️ Банкетам.Нет</h1>
        <h2>🔐 Вход в аккаунт</h2>
        <?php if ($error): ?><div class="error-message">⚠️ <?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>👤 Логин</label><input type="text" name="login" required autofocus></div>
            <div class="form-group"><label>🔒 Пароль</label><input type="password" name="password" required></div>
            <button type="submit">🎉 Войти</button>
        </form>
        <div class="footer-links"><p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p><p><a href="index.php">← На главную</a></p></div>
    </div>
</div>
<footer><p>© 2026 Банкетам.Нет - выберите идеальное место для вашего праздника</p></footer>
</body>
</html>