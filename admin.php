<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: login.php');
    exit;
}
include('db.php');
$status_updated = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'])) {
    $request_id = (int)$_POST['request_id'];
    $status = $_POST['status'];
    
    $valid_statuses = ['Новая', 'Банкет назначен', 'Банкет завершен'];
    if (in_array($status, $valid_statuses)) {
        $stmt = $con->prepare("UPDATE request SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        if ($stmt->execute()) {
            $status_updated = true;
        }
        $stmt->close();
    }
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$status_filter = $_GET['status'] ?? '';
$where = '';
if ($status_filter && in_array($status_filter, ['Новая', 'Банкет назначен', 'Банкет завершен'])) {
    $where = "WHERE r.status = '$status_filter'";
}
$count_result = $con->query("SELECT COUNT(*) as total FROM request r $where");
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$query = "SELECT r.*, u.login, u.fullname, u.phone, u.email 
          FROM request r 
          JOIN users u ON r.user_id = u.id 
          $where 
          ORDER BY r.date DESC 
          LIMIT $limit OFFSET $offset";
$requests = $con->query($query);

$stats_query = $con->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Новая' THEN 1 ELSE 0 END) as new,
        SUM(CASE WHEN status = 'Банкет назначен' THEN 1 ELSE 0 END) as assigned,
        SUM(CASE WHEN status = 'Банкет завершен' THEN 1 ELSE 0 END) as completed
    FROM request
");
$stats = $stats_query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Банкетам.Нет</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root { --gold: #DAA520; --rose-gold: #FFDAB9; --cream: #FFFDD0; --forest-green: #006400; --crimson: #DC143C; --dark-green: #003300; }
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
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255,255,255,0.9);
            border-radius: 20px;
            overflow: hidden;
            backdrop-filter: blur(5px);
        }
        .admin-header {
            background: linear-gradient(135deg, var(--forest-green), #003300);
            padding: 25px 30px;
            color: white;
        }
        .admin-header h1 { font-size: 28px; }
        .nav-bar {
            display: flex;
            justify-content: space-between;
            padding: 15px 30px;
            background: var(--cream);
            border-bottom: 2px solid var(--gold);
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn {
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-outline {
            border: 2px solid var(--gold);
            color: var(--forest-green);
        }
        .btn-outline:hover { background: var(--gold); color: white; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 25px 30px;
            background: #fafafa;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border-left: 4px solid var(--gold);
        }
        .stat-number { font-size: 32px; font-weight: 600; color: var(--gold); }
        .filter-bar {
            padding: 15px 30px;
            background: white;
            border-bottom: 1px solid var(--rose-gold);
        }
        .filter-bar a {
            display: inline-block;
            padding: 5px 15px;
            margin-right: 10px;
            background: var(--cream);
            color: var(--forest-green);
            text-decoration: none;
            border-radius: 20px;
        }
        .filter-bar a.active { background: var(--gold); color: white; }
        .requests-container { padding: 30px; }
        .request-card {
            background: white;
            border: 2px solid var(--rose-gold);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        .user-info h3 { color: var(--forest-green); }
        .request-id { background: var(--cream); padding: 5px 15px; border-radius: 20px; }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        .status-new { background: #fff3cd; color: #856404; }
        .status-assigned { background: #d4edda; color: var(--forest-green); }
        .status-completed { background: #cce5ff; color: #004085; }
        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .detail-item { background: var(--cream); padding: 10px; border-radius: 10px; }
        .detail-label { font-size: 12px; color: var(--forest-green); text-transform: uppercase; }
        .status-form { margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--rose-gold); }
        .status-form select {
            padding: 10px;
            border: 2px solid var(--rose-gold);
            border-radius: 10px;
            margin-right: 10px;
        }
        .status-form button {
            padding: 10px 25px;
            background: var(--gold);
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 20px;
        }
        .page-link {
            padding: 8px 15px;
            border: 2px solid var(--rose-gold);
            border-radius: 10px;
            text-decoration: none;
            color: var(--forest-green);
        }
        .page-link.active, .page-link:hover { background: var(--gold); color: white; border-color: var(--gold); }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--forest-green);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            animation: fadeInOut 3s forwards;
        }
        @keyframes fadeInOut {
            0% { opacity: 0; transform: translateX(100px); }
            15% { opacity: 1; transform: translateX(0); }
            85% { opacity: 1; transform: translateX(0); }
            100% { opacity: 0; transform: translateX(100px); visibility: hidden; }
        }
        footer {
            text-align: center;
            padding: 25px;
            background: var(--forest-green);
            color: var(--gold);
            border-top: 2px solid var(--gold);
        }
        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: 1fr; }
            .request-details { grid-template-columns: 1fr; }
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
            <a href="logout.php">🚪 Выход</a>
        </div>
    </div>
</header>
<div class="main-content">
    <div class="container">
        <div class="admin-header">
            <h1>👑 Панель администратора</h1>
            <p>Управление заявками на банкет</p>
        </div>
        
        <div class="nav-bar">
            <a href="index.php" class="btn btn-outline">🏠 На сайт</a>
            <a href="logout.php" class="btn btn-outline">🚪 Выход</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number"><?php echo $stats['total']; ?></div><div>Всего заявок</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $stats['new']; ?></div><div>🆕 Новые</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $stats['assigned']; ?></div><div>🍽️ Банкет назначен</div></div>
            <div class="stat-card"><div class="stat-number"><?php echo $stats['completed']; ?></div><div>✅ Банкет завершен</div></div>
        </div>
        
        <div class="filter-bar">
            <a href="?<?php echo $status_filter ? '' : 'class=active'; ?>">Все</a>
            <a href="?status=Новая" class="<?php echo $status_filter == 'Новая' ? 'active' : ''; ?>">🆕 Новые</a>
            <a href="?status=Банкет назначен" class="<?php echo $status_filter == 'Банкет назначен' ? 'active' : ''; ?>">🍽️ Назначенные</a>
            <a href="?status=Банкет завершен" class="<?php echo $status_filter == 'Банкет завершен' ? 'active' : ''; ?>">✅ Завершенные</a>
        </div>
        
        <div class="requests-container">
            <?php if ($requests && $requests->num_rows > 0): ?>
                <?php while ($req = $requests->fetch_assoc()): ?>
                    <?php
                        $status_class = '';
                        if ($req['status'] == 'Новая') $status_class = 'status-new';
                        elseif ($req['status'] == 'Банкет назначен') $status_class = 'status-assigned';
                        else $status_class = 'status-completed';
                    ?>
                    <div class="request-card">
                        <div class="request-header">
                            <div class="user-info">
                                <h3><?php echo htmlspecialchars($req['login']); ?></h3>
                                <p><?php echo htmlspecialchars($req['fullname']); ?> | <?php echo htmlspecialchars($req['phone']); ?></p>
                            </div>
                            <div>
                                <span class="request-id">Заявка #<?php echo $req['id']; ?></span>
                                <span class="status-badge <?php echo $status_class; ?>"><?php echo $req['status']; ?></span>
                            </div>
                        </div>
                        
                        <div class="request-details">
                            <div class="detail-item"><div class="detail-label">📅 Дата</div><div><?php echo date('d.m.Y H:i', strtotime($req['date'])); ?></div></div>
                            <div class="detail-item"><div class="detail-label">🍽️ Площадка</div><div><?php echo isset($req['curses']) && !empty($req['curses']) ? htmlspecialchars($req['curses']) : 'Не указано'; ?></div></div>
                            <div class="detail-item"><div class="detail-label">💳 Оплата</div><div><?php echo isset($req['payment']) && !empty($req['payment']) ? htmlspecialchars($req['payment']) : 'Не указано'; ?></div></div>
                            <div class="detail-item"><div class="detail-label">📧 Email</div><div><?php echo htmlspecialchars($req['email']); ?></div></div>
                        </div>
                        
                        <?php if (!empty($req['review'])): ?>
                            <div class="detail-item" style="margin-bottom: 15px;"><div class="detail-label">⭐ Отзыв</div><div><?php echo htmlspecialchars($req['review']); ?></div></div>
                        <?php endif; ?>
                        
                        <div class="status-form">
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <select name="status">
                                    <option value="Новая" <?php echo $req['status'] == 'Новая' ? 'selected' : ''; ?>>🆕 Новая</option>
                                    <option value="Банкет назначен" <?php echo $req['status'] == 'Банкет назначен' ? 'selected' : ''; ?>>🍽️ Банкет назначен</option>
                                    <option value="Банкет завершен" <?php echo $req['status'] == 'Банкет завершен' ? 'selected' : ''; ?>>✅ Банкет завершен</option>
                                </select>
                                <button type="submit">💾 Сохранить</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; color: #888;">📭 Заявок не найдено</div>
            <?php endif; ?>
        </div>
        
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" class="page-link <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<footer>
    <p>© 2026 Банкетам.Нет - выберите идеальное место для вашего праздника</p>
</footer>
<?php if ($status_updated): ?>
    <div class="notification">✅ Статус заявки успешно обновлён!</div>
<?php endif; ?>
</body>
</html>