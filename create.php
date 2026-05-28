<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db.php');
    
    $user_id = $_SESSION['user_id'];
    $date_input = $_POST['date'];
    $curses = $_POST['curses'];
    $payment = $_POST['payment'];
    $review = trim($_POST['review']);
    
    $date_obj = DateTime::createFromFormat('d.m.Y H:i', $date_input);
    if (!$date_obj) {
        $error = 'Неверный формат даты. Используйте ДД.ММ.ГГГГ ЧЧ:ММ';
    } else {
        $date_sql = $date_obj->format('Y-m-d H:i:s');
        
        $stmt = $con->prepare("INSERT INTO request (user_id, date, curses, payment, review, status) VALUES (?, ?, ?, ?, ?, 'Новая')");
        $stmt->bind_param("issss", $user_id, $date_sql, $curses, $payment, $review);
        
        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = 'Ошибка при создании заявки: ' . $con->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новая заявка - Банкетам.Нет</title>
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
        .main-content { flex: 1; position: relative; padding: 40px 20px; }
        .main-content::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('images/fon.jpg'); background-size: cover; background-position: center; background-attachment: fixed; opacity: 0.35; z-index: 0; }
        .main-content::after { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 30, 0, 0.3); z-index: 1; }
        .main-content > * { position: relative; z-index: 2; }
        .container { max-width: 550px; margin: 0 auto; background: rgba(255,255,255,0.9); padding: 35px; border-radius: 20px; backdrop-filter: blur(5px); }
        .nav-buttons-local { display: flex; gap: 15px; margin-bottom: 25px; }
        .nav-buttons-local a { flex: 1; text-align: center; padding: 10px; background: linear-gradient(135deg, var(--gold), var(--forest-green)); color: white; text-decoration: none; border-radius: 10px; transition: 0.3s; }
        .nav-buttons-local a:hover { transform: translateY(-2px); }
        h1 { text-align: center; color: var(--forest-green); margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; color: var(--forest-green); font-weight: 500; }
        input, select, textarea { width: 100%; padding: 12px; border: 2px solid var(--rose-gold); border-radius: 10px; font-family: 'Oswald', sans-serif; font-size: 16px; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--gold); }
        textarea { resize: vertical; min-height: 80px; }
        button { width: 100%; padding: 14px; background: linear-gradient(135deg, var(--gold), var(--forest-green)); color: white; border: none; border-radius: 10px; font-size: 18px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(218,165,32,0.4); }
        .success-message { background: #d4edda; color: var(--forest-green); padding: 15px; border-radius: 10px; text-align: center; margin-bottom: 20px; }
        .error-message { background: #f8d7da; color: var(--crimson); padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        .hint { font-size: 12px; color: #888; margin-top: 5px; display: block; }
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
            <a href="history.php">📋 Мои заявки</a>
            <a href="logout.php">🚪 Выход</a>
        </div>
    </div>
</header>
<div class="main-content">
    <div class="container">
        <h1>🎉 Бронирование площадки</h1>
        <?php if ($success): ?>
            <div class="success-message">✅ Заявка успешно создана!<br><a href="history.php" style="color: var(--forest-green);">📋 Посмотреть мои заявки</a></div>
        <?php elseif ($error): ?>
            <div class="error-message">⚠️ <?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!$success): ?>
        <form method="POST">
            <div class="form-group">
                <label>🍽️ Тип помещения</label>
                <select name="curses" required>
                    <option value="Банкетный зал">🏛️ Банкетный зал</option>
                    <option value="Ресторан">🍷 Ресторан</option>
                    <option value="Летняя веранда">🌞 Летняя веранда</option>
                    <option value="Закрытая веранда">🏠 Закрытая веранда</option>
                </select>
            </div>
            <div class="form-group">
                <label>📅 Дата и время банкета</label>
                <input type="text" name="date" placeholder="ДД.ММ.ГГГГ ЧЧ:ММ" required>
                <span class="hint">Пример: 25.12.2026 18:00</span>
            </div>
            <div class="form-group">
                <label>💳 Способ оплаты</label>
                <select name="payment" required>
                    <option value="Наличные">💵 Наличные</option>
                    <option value="Перевод">🏦 Банковский перевод</option>
                    <option value="Карта">💳 Банковская карта</option>
                </select>
            </div>
            <div class="form-group">
                <label>📝 Дополнительные пожелания</label>
                <textarea name="review" placeholder="Особые пожелания по меню, декору, музыке..."></textarea>
            </div>
            <button type="submit">🎉 Забронировать</button>
        </form>
        <?php endif; ?>
    </div>
</div>
<footer><p>© 2026 Банкетам.Нет - выберите идеальное место для вашего праздника</p></footer>
</body>
</html>