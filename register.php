<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error = ''; $success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db.php');
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $fullname = trim($_POST['fullname']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $errors = [];
    if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) $errors[] = 'Логин должен содержать только латинские буквы и цифры, минимум 6 символов';
    if (strlen($password) < 8) $errors[] = 'Пароль должен содержать минимум 8 символов';
    if (empty($fullname)) $errors[] = 'Введите ФИО';
    if (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) $errors[] = 'Телефон должен быть в формате +7(XXX)XXX-XX-XX';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Введите корректный email';
    if (empty($errors)) {
        $stmt = $con->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors[] = 'Пользователь с таким логином уже существует';
        $stmt->close();
        if (empty($errors)) {
            $stmt = $con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) $errors[] = 'Пользователь с таким email уже существует';
            $stmt->close();
        }
    }
    if (empty($errors)) {
        $stmt = $con->prepare("INSERT INTO users (login, password, fullname, phone, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $login, $password, $fullname, $phone, $email);
        if ($stmt->execute()) $success = true;
        else $error = 'Ошибка регистрации';
        $stmt->close();
    } else $error = implode('<br>', $errors);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Банкетам.Нет</title>
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
        .container { max-width: 500px; width: 100%; background: rgba(255,255,255,0.9); padding: 35px; border-radius: 20px; backdrop-filter: blur(5px); }
        h1 { text-align: center; color: var(--forest-green); font-size: 32px; margin-bottom: 10px; }
        h2 { text-align: center; color: var(--forest-green); margin-bottom: 25px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 5px; color: var(--forest-green); font-weight: 500; }
        input { width: 100%; padding: 12px; border: 2px solid var(--rose-gold); border-radius: 10px; font-family: 'Oswald', sans-serif; transition: 0.3s; }
        input:focus { outline: none; border-color: var(--gold); }
        .hint { font-size: 12px; color: #888; margin-top: 5px; display: block; }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--gold), var(--forest-green)); color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(218,165,32,0.4); }
        .error-message { background: #f8d7da; color: var(--crimson); padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .success-message { background: #d4edda; color: var(--forest-green); padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .footer-links { text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--rose-gold); }
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
            <a href="login.php">🔐 Войти</a>
        </div>
    </div>
</header>
<div class="main-content">
    <div class="container">
        <h1>🍽️ Банкетам.Нет</h1>
        <h2>📝 Регистрация</h2>
        <?php if ($error): ?><div class="error-message">⚠️ <?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success-message">✅ Регистрация успешна!<br><a href="login.php">Войти →</a></div>
        <?php else: ?>
        <form method="POST">
            <div class="form-group"><label>👤 ФИО</label><input type="text" name="fullname" required value="<?php echo $_POST['fullname'] ?? ''; ?>"></div>
            <div class="form-group"><label>📱 Телефон</label><input type="tel" name="phone" placeholder="+7(XXX)XXX-XX-XX" pattern="\+7\(\d{3}\)\d{3}-\d{2}-\d{2}" required><span class="hint">Формат: +7(XXX)XXX-XX-XX</span></div>
            <div class="form-group"><label>📧 Email</label><input type="email" name="email" required value="<?php echo $_POST['email'] ?? ''; ?>"><span class="hint">Введите действующий email</span></div>
            <div class="form-group"><label>🔑 Логин</label><input type="text" name="login" pattern="[a-zA-Z0-9]{6,}" required><span class="hint">Только латиница и цифры, мин. 6 символов</span></div>
            <div class="form-group"><label>🔒 Пароль</label><input type="password" name="password" minlength="8" required><span class="hint">Минимум 8 символов</span></div>
            <button type="submit">🎉 Зарегистрироваться</button>
        </form>
        <?php endif; ?>
        <div class="footer-links"><p>Уже есть аккаунт? <a href="login.php">Войти</a></p><p><a href="index.php">← На главную</a></p></div>
    </div>
</div>
<footer><p>© 2026 Банкетам.Нет - выберите идеальное место для вашего праздника</p></footer>
</body>
</html>