<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

include('db.php');

$feedback_success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['feedback'])) {
    $feedback = trim($_POST['feedback']);
    $request_id = (int)$_POST['request_id'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $con->prepare("SELECT status FROM request WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();
    
    if ($request && $request['status'] == 'Банкет завершен') {
        $stmt = $con->prepare("UPDATE request SET feedback = ? WHERE id = ?");
        $stmt->bind_param("si", $feedback, $request_id);
        if ($stmt->execute()) {
            $feedback_success = 'Отзыв успешно сохранён!';
        }
    }
}

$user_id = $_SESSION['user_id'];
$query = $con->prepare("SELECT * FROM request WHERE user_id = ? ORDER BY date DESC");
$query->bind_param("i", $user_id);
$query->execute();
$requests = $query->get_result();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки - Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { 
            --gold: #DAA520; 
            --rose-gold: #FFDAB9; 
            --cream: #FFFDD0;
            --forest-green: #006400; 
            --crimson: #DC143C;
            --dark-green: #003300;
        }
        body {
            font-family: 'Oswald', sans-serif;
            background: linear-gradient(135deg, var(--forest-green) 0%, var(--dark-green) 100%);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background: rgba(0, 40, 0, 0.95);
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }
        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
            gap: 15px;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .logo img {
            height: 40px;
            width: auto;
        }
        .logo span {
            color: var(--gold);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
        }
        .nav-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-buttons a {
            padding: 8px 20px;
            border: 2px solid var(--gold);
            border-radius: 25px;
            color: var(--gold);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .nav-buttons a:hover {
            background: var(--gold);
            color: var(--forest-green);
            transform: translateY(-2px);
        }
        .main-content {
            flex: 1;
            position: relative;
            padding: 40px 20px;
        }
        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('images/fon.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            opacity: 0.35;
            z-index: 0;
        }
        .main-content::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 30, 0, 0.3);
            z-index: 1;
        }
        .main-content > * {
            position: relative;
            z-index: 2;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.9);
            padding: 35px;
            border-radius: 20px;
            backdrop-filter: blur(5px);
        }
        .btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--gold), var(--forest-green));
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: 0.3s;
            display: inline-block;
        }
        .btn:hover { transform: translateY(-2px); }
        h1 { text-align: center; color: var(--forest-green); margin-bottom: 25px; }
        .slideshow-container {
            max-width: 100%;
            position: relative;
            margin: 20px 0 30px;
            border-radius: 15px;
            overflow: hidden;
        }
        .slide-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .slide-text {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.6);
            padding: 5px 15px;
            border-radius: 20px;
            color: var(--gold);
        }
        .slider-prev, .slider-next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            padding: 8px 15px;
            border-radius: 50%;
            cursor: pointer;
        }
        .slider-prev { left: 10px; }
        .slider-next { right: 10px; }
        .request-card {
            border: 2px solid var(--rose-gold);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            transition: 0.3s;
            background: white;
        }
        .request-card:hover { border-color: var(--gold); box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .request-id { font-size: 18px; font-weight: 600; color: var(--gold); }
        .status {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .status-new { background: #fff3cd; color: #856404; }
        .status-assigned { background: #d4edda; color: var(--forest-green); }
        .status-completed { background: #cce5ff; color: #004085; }
        .request-details { margin: 15px 0; }
        .detail-row { margin: 8px 0; }
        .wishes-section { margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--rose-gold); }
        .wishes-text { background: var(--cream); padding: 12px; border-radius: 8px; margin-top: 10px; }
        .feedback-section { margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--rose-gold); }
        .feedback-form { display: flex; gap: 10px; flex-wrap: wrap; }
        .feedback-form input { flex: 1; padding: 10px; border: 2px solid var(--rose-gold); border-radius: 8px; }
        .feedback-form button { padding: 10px 20px; background: var(--gold); border: none; border-radius: 8px; cursor: pointer; }
        .feedback-text { background: #e8f4e8; padding: 12px; border-radius: 8px; margin-top: 10px; border-left: 3px solid var(--gold); }
        .empty-state { text-align: center; padding: 50px; color: #888; }
        .success-msg { background: #d4edda; color: var(--forest-green); padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        footer {
            text-align: center;
            padding: 25px;
            background: var(--forest-green);
            color: var(--gold);
            border-top: 2px solid var(--gold);
        }
        @media (max-width: 600px) {
            .container { padding: 20px; }
            .request-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        }
    </style>
</head>
<body>
<header class="header">
    <div class="nav">
        <a href="index.php" class="logo">
            <img src="images/logo.png" alt="Логотип">
            <span>Банкетам.Нет</span>
        </a>
        <div class="nav-buttons">
            <a href="index.php">🏠 Главная</a>
            <a href="create.php" class="btn">🎉 Новая заявка</a>
            <a href="logout.php" class="btn">🚪 Выход</a>
        </div>
    </div>
</header>
<div class="main-content">
    <div class="container">
        <h1>📋 Мои заявки на банкет</h1>
        <div class="slideshow-container" id="historySlider">
            <div class="slide"><img class="slide-img" src="images/zal7.jpg" alt="Банкет"><div class="slide-text">🏛️ Роскошные банкеты</div></div>
            <div class="slide"><img class="slide-img" src="images/food.jpg" alt="Кухня"><div class="slide-text">🍷 Изысканная кухня</div></div>
            <div class="slide"><img class="slide-img" src="images/zal5.jpg" alt="Веранда"><div class="slide-text">🌞 Уютные веранды</div></div>
            <div class="slide"><img class="slide-img" src="images/zal6.jpg" alt="Зал"><div class="slide-text">🏠 Закрытые веранды</div></div>
            <a class="slider-prev" onclick="changeHistorySlide(-1)">❮</a>
            <a class="slider-next" onclick="changeHistorySlide(1)">❯</a>
        </div>
        <?php if ($feedback_success): ?><div class="success-msg">⭐ <?php echo $feedback_success; ?></div><?php endif; ?>
        <?php if ($requests->num_rows == 0): ?>
            <div class="empty-state">🎉 У вас пока нет заявок<br><br><a href="create.php" style="color: var(--gold);">Создать новую заявку →</a></div>
        <?php else: ?>
            <?php while ($req = $requests->fetch_assoc()):
                $status_class = '';
                if ($req['status'] == 'Новая') $status_class = 'status-new';
                elseif ($req['status'] == 'Банкет назначен') $status_class = 'status-assigned';
                elseif ($req['status'] == 'Банкет завершен') $status_class = 'status-completed';
                $date_formatted = date('d.m.Y H:i', strtotime($req['date']));
                $curses_value = isset($req['curses']) && !empty($req['curses']) ? htmlspecialchars($req['curses']) : 'Не указано';
                $payment_value = isset($req['payment']) && !empty($req['payment']) ? htmlspecialchars($req['payment']) : 'Не указано';
                $wishes_value = isset($req['review']) && !empty($req['review']) ? htmlspecialchars($req['review']) : '';
                $feedback_value = isset($req['feedback']) && !empty($req['feedback']) ? htmlspecialchars($req['feedback']) : '';
            ?>
            <div class="request-card">
                <div class="request-header">
                    <span class="request-id">🎯 Заявка #<?php echo $req['id']; ?></span>
                    <span class="status <?php echo $status_class; ?>"><?php echo $req['status']; ?></span>
                </div>
                <div class="request-details">
                    <div class="detail-row"><strong>📅 Дата:</strong> <?php echo $date_formatted; ?></div>
                    <div class="detail-row"><strong>🍽️ Площадка:</strong> <?php echo $curses_value; ?></div>
                    <div class="detail-row"><strong>💳 Оплата:</strong> <?php echo $payment_value; ?></div>
                </div>
                
                <!-- Пожелания (из поля review) -->
                <?php if (!empty($wishes_value)): ?>
                    <div class="wishes-section">
                        <strong>📝 Ваши пожелания:</strong>
                        <div class="wishes-text"><?php echo $wishes_value; ?></div>
                    </div>
                <?php endif; ?>
                
                <!-- Отзыв (из поля feedback) -->
                <?php if (!empty($feedback_value)): ?>
                    <div class="feedback-section">
                        <strong>⭐ Ваш отзыв:</strong>
                        <div class="feedback-text"><?php echo $feedback_value; ?></div>
                    </div>
                <?php endif; ?>
                
                <!-- Форма для отзыва - только для статуса "Банкет завершен" -->
                <?php if ($req['status'] == 'Банкет завершен'): ?>
                    <div class="feedback-section">
                        <form method="POST" class="feedback-form">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <input type="text" name="feedback" placeholder="✍️ Напишите отзыв о проведённом банкете..." value="<?php echo htmlspecialchars($req['feedback'] ?? ''); ?>">
                            <button type="submit">⭐ Отправить отзыв</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
<footer><p>© 2026 Банкетам.Нет - выберите идеальное место для вашего праздника</p></footer>
<script>
    let historySlideIndex = 1, historyInterval;
    let historySlides = document.querySelectorAll('#historySlider .slide');
    function showHistorySlides(n) {
        if (!historySlides.length) return;
        if (n > historySlides.length) historySlideIndex = 1;
        if (n < 1) historySlideIndex = historySlides.length;
        for (let i = 0; i < historySlides.length; i++) historySlides[i].style.display = "none";
        historySlides[historySlideIndex-1].style.display = "block";
    }
    function changeHistorySlide(n) {
        clearInterval(historyInterval);
        historySlideIndex += n;
        showHistorySlides(historySlideIndex);
        startHistoryAutoSlide();
    }
    function startHistoryAutoSlide() {
        historyInterval = setInterval(() => { historySlideIndex++; showHistorySlides(historySlideIndex); }, 3000);
    }
    if (historySlides.length) { showHistorySlides(1); startHistoryAutoSlide(); }
    const historyContainer = document.querySelector('#historySlider');
    if (historyContainer) {
        historyContainer.addEventListener('mouseenter', () => clearInterval(historyInterval));
        historyContainer.addEventListener('mouseleave', () => startHistoryAutoSlide());
    }
</script>
</body>
</html>