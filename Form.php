<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new PDO("sqlite:" . __DIR__ . "/From.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE,
    email TEXT UNIQUE,
    password TEXT
)");

$notice = "";
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$_POST['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user'] = $user['username'];
            header("Location: Form.php");
            $_SESSION['notice'] = '✅ Login berhasil!';
            exit;
        } else {
            $notice = "❌ Username atau password salah!";
        }
    } elseif ($action === 'register') {
        $check = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $check->execute([$_POST['username'], $_POST['email']]);
        if ($check->fetch()) {
            $notice = "⚠️ Username atau email sudah digunakan!";
        } else {
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([
                $_POST['username'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT)
            ]);
            $notice = "✅ Registrasi berhasil! Silakan login.";
        }
    } elseif ($action === 'forgot') {
        $notice = "📧 Link reset password dikirim ke email (simulasi).";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: Form.php");
    exit;
}

$user = $_SESSION['user'] ?? null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>TUGAS DERIANSYAH WINATA</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #eee;
            background: 
                linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('DERI.jpg') center center / cover no-repeat fixed;
            transition: background-position 0.2s ease; /* SMOOTH background movement */
        }
        .container {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2em;
            width: 360px;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fadeInUp {
            animation: fadeInUp 0.5s ease;
        }
        h2, p.text-title {
            text-align: center;
            color: #fff;
        }
        input, button {
            width: 100%;
            padding: 0.8em;
            margin-top: 0.5em;
            border: none;
            border-radius: 8px;
            font-size: 1em;
        }
        input[type="checkbox"] {
            width: auto;
            margin-right: 0.5em;
        }
        input {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        input:focus {
            outline: none;
            background: rgba(255,255,255,0.15);
        }
        button {
            background: linear-gradient(135deg,rgb(59, 61, 61), #0072ff);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: scale(1.03);
            background: linear-gradient(135deg,rgb(245, 241, 3),rgb(192, 7, 7));
        }
        .tabs {
            display: flex;
            justify-content: space-between;
            margin-top: 1em;
        }
        .tabs button {
            width: 32%;
            background: rgba(255,255,255,0.1);
        }
        a {
            color: #aaf;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        label.checkbox-label {
            display: flex;
            align-items: center;
            margin-top: 1em;
        }
        .input-group {
            position: relative;
        }
        .toggle-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #ccc;
            width: 24px;
            height: 24px;
        }
        .toggle-eye svg {
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body>

<div class="container">
    <?php if ($user): ?>
        <h2>👋 Hai, <?= htmlspecialchars($user) ?></h2>
        <p style="text-align:center"><a href="?logout=1">🔓 Logout</a></p>
    <?php else: ?>
        <p class="text-title" id="formTitle">LOGIN ACCOUNT</p>

        <?php if ($notice): ?>
            <script>
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: '<?= addslashes($notice) ?>',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            </script>
        <?php endif; ?>

        <!-- FORM LOGIN -->
        <form id="login" method="POST" class="fadeInUp" style="display:block">
            <input type="hidden" name="action" value="login">
            <input name="username" placeholder="Username" required>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" id="login-password" required>
                <span class="toggle-eye" onclick="togglePassword('login-password', this)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                            -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </span>
            </div>
            <label class="checkbox-label"><input type="checkbox"> Ingat saya</label>
            <button>🔐 Login</button>
        </form>

        <!-- FORM REGISTER -->
        <form id="register" method="POST" style="display:none">
            <input type="hidden" name="action" value="register">
            <input name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" id="reg-password" required>
                <span class="toggle-eye" onclick="togglePassword('reg-password', this)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                            -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </span>
            </div>
            <div class="input-group">
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password" id="reg-confirm" required>
                <span class="toggle-eye" onclick="togglePassword('reg-confirm', this)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                            -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </span>
            </div>
            <input type="text" name="asal_kota" placeholder="Asal Kota" required>
            <label for="dob">Tanggal Lahir:</label>
            <input type="date" id="dob" name="dob" required>
            <label class="checkbox-label">
                <input type="checkbox" id="agree-terms" required>
                Saya menyetujui syarat & ketentuan
            </label>
            <button>📝 Daftar</button>
        </form>

        <!-- FORM FORGOT -->
        <form id="forgot" method="POST" style="display:none">
            <input type="hidden" name="action" value="forgot">
            <input name="email" type="email" placeholder="Email kamu" required>
            <button>📩 Kirim Link</button>
        </form>

        <div class="tabs">
            <button onclick="showForm('login')">Login</button>
            <button onclick="showForm('register')">Daftar</button>
            <button onclick="showForm('forgot')">Lupa?</button>
        </div>
    <?php endif; ?>
</div>

<script>
function showForm(id) {
    ['login', 'register', 'forgot'].forEach(f => {
        const form = document.getElementById(f);
        form.style.display = (f === id) ? 'block' : 'none';
        if (f === id) {
            form.classList.remove('fadeInUp');
            void form.offsetWidth;
            form.classList.add('fadeInUp');
        }
    });

    const formTitle = document.getElementById('formTitle');
    if (id === 'login') formTitle.textContent = 'LOGIN ACCOUNT';
    else if (id === 'register') formTitle.textContent = 'DAFTAR ACCOUNT';
    else if (id === 'forgot') formTitle.textContent = 'RESET PASSWORD';
}

function togglePassword(inputId, iconContainer) {
    const input = document.getElementById(inputId);
    input.type = input.type === "password" ? "text" : "password";
}

// PARALLAX EFFECT
document.addEventListener('mousemove', function(e) {
    const moveX = (e.clientX - window.innerWidth / 2) * 0.02;
    const moveY = (e.clientY - window.innerHeight / 2) * 0.02;
    document.body.style.backgroundPosition = `calc(50% + ${moveX}px) calc(50% + ${moveY}px)`;
});
</script>

</body>
</html>
