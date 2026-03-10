<?php
// Database connection
$host = "localhost";
$dbname = "women_db";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name     = $conn->real_escape_string(trim($_POST['full_name']));
    $email         = $conn->real_escape_string(trim($_POST['email']));
    $phone         = $conn->real_escape_string(trim($_POST['phone']));
    $dob           = $conn->real_escape_string($_POST['dob']);
    $city          = $conn->real_escape_string(trim($_POST['city']));
    $state         = $conn->real_escape_string(trim($_POST['state']));
    $business_name = $conn->real_escape_string(trim($_POST['business_name']));
    $business_type = $conn->real_escape_string($_POST['business_type']);
    $experience    = $conn->real_escape_string($_POST['experience']);
    $password_raw  = $_POST['password'];
    $confirm_pass  = $_POST['confirm_password'];

    if ($password_raw !== $confirm_pass) {
        $error = "Passwords do not match!";
    } else {
        $hashed_password = password_hash($password_raw, PASSWORD_BCRYPT);

        $check = $conn->query("SELECT id FROM members WHERE email='$email'");
        if ($check->num_rows > 0) {
            $error = "This email is already registered.";
        } else {
            $sql = "INSERT INTO members 
                    (full_name, email, phone, dob, city, state, business_name, business_type, experience, password)
                    VALUES 
                    ('$full_name','$email','$phone','$dob','$city','$state','$business_name','$business_type','$experience','$hashed_password')";

            if ($conn->query($sql) === TRUE) {
                $success = "Registration successful! Welcome, $full_name 🎉";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Register | Women Entrepreneurship Portal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fce4ec 0%, #f8bbd0 30%, #e1bee7 70%, #f3e5f5 100%);
            min-height: 100vh;
            padding: 20px;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, #880e4f, #c2185b);
            padding: 14px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(136,14,79,0.3);
            margin: -20px -20px 20px -20px;
        }

        .navbar .logo {
            color: white;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .navbar .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .navbar .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: opacity 0.2s;
        }

        .navbar .nav-links a:hover { opacity: 0.8; }

        .navbar .nav-links .active-btn {
            background: white;
            color: #880e4f;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .navbar .nav-links .active-btn:hover { opacity: 1; background: #f8bbd0; }

        /* BACK BUTTON */
        .back-btn {
            max-width: 780px;
            margin: 0 auto 10px;
            display: block;
        }

        .back-btn a {
            color: #880e4f;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: gap 0.2s;
        }

        .back-btn a:hover { gap: 10px; }

        /* HEADER */
        .header {
            text-align: center;
            padding: 20px 20px 10px;
        }

        .header .logo-icon { font-size: 42px; margin-bottom: 8px; }

        .header h1 {
            font-size: 2.2rem;
            color: #880e4f;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .header p { color: #ad1457; font-size: 1rem; margin-top: 6px; }

        /* CARD */
        .card {
            background: #fff;
            max-width: 780px;
            margin: 20px auto;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(136, 14, 79, 0.15);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #880e4f, #c2185b, #e91e63);
            color: white;
            padding: 28px 40px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .card-header .icon { font-size: 2.2rem; }
        .card-header h2 { font-size: 1.6rem; font-weight: 700; }
        .card-header p { font-size: 0.9rem; opacity: 0.85; margin-top: 3px; }

        .form-body { padding: 40px; }

        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: #880e4f;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin: 28px 0 16px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f8bbd0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title:first-of-type { margin-top: 0; }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        @media (max-width: 580px) {
            .grid-2 { grid-template-columns: 1fr; }
            .form-body { padding: 25px 20px; }
            .navbar { padding: 14px 20px; }
            .navbar .nav-links { gap: 12px; }
            .navbar .nav-links a { font-size: 0.8rem; }
        }

        .form-group { display: flex; flex-direction: column; }

        .form-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
        }

        .form-group label span.req { color: #e91e63; }

        .form-group input,
        .form-group select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #333;
            transition: border-color 0.3s, box-shadow 0.3s;
            background: #fafafa;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #e91e63;
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
            background: #fff;
        }

        .pw-wrap { position: relative; }
        .pw-wrap input { width: 100%; padding-right: 44px; }

        .pw-toggle {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.1rem;
            color: #aaa;
            user-select: none;
        }

        .pw-toggle:hover { color: #e91e63; }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1.5px solid #a5d6a7;
        }

        .alert-error {
            background: #fce4ec;
            color: #c62828;
            border: 1.5px solid #ef9a9a;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            margin-top: 30px;
            background: linear-gradient(135deg, #880e4f, #c2185b, #e91e63);
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            letter-spacing: 1px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 6px 20px rgba(233, 30, 99, 0.35);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(233, 30, 99, 0.45);
        }

        .btn-submit:active { transform: translateY(0); }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #888;
        }

        .form-footer a {
            color: #e91e63;
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover { text-decoration: underline; }

        .bottom-strip {
            background: linear-gradient(135deg, #880e4f, #e91e63);
            color: white;
            text-align: center;
            padding: 14px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<!-- ✅ NAVBAR -->
<nav class="navbar">
    <a href="index.html" class="logo">👩‍💼 ShaktiUdyam</a>
    <div class="nav-links">
        <a href="index.html">🏠 Home</a>
        <a href="schemes.html">📋 Schemes</a>
        <a href="entrepreneurs.html">💼 Entrepreneurs</a>
        <a href="stories.html">📖 Stories</a>
        <a href="register.php" class="active-btn">📝 Register</a>
    </div>
</nav>

<!-- ✅ BACK BUTTON -->
<div class="back-btn">
    <a href="index.html">← Back to Home</a>
</div>

<!-- ✅ PAGE HEADER -->
<div class="header">
    <div class="logo-icon">👩‍💼</div>
    <h1>Women Entrepreneurship Portal</h1>
    <p>Empowering Women · Building Futures · Breaking Barriers</p>
</div>

<!-- ✅ REGISTRATION CARD -->
<div class="card">
    <div class="card-header">
        <span class="icon">📋</span>
        <div>
            <h2>Create Your Account</h2>
            <p>Fill in your details to join our community of women entrepreneurs</p>
        </div>
    </div>

    <div class="form-body">

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="alert alert-error">❌ <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" onsubmit="return validateForm()">

            <!-- PERSONAL INFO -->
            <div class="section-title">👤 Personal Information</div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="full_name" placeholder="e.g. Priya Sharma" required
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"/>
                </div>
                <div class="form-group">
                    <label>Date of Birth <span class="req">*</span></label>
                    <input type="date" name="dob" required
                           value="<?= htmlspecialchars($_POST['dob'] ?? '') ?>"/>
                </div>
                <div class="form-group">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" placeholder="you@example.com" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
                </div>
                <div class="form-group">
                    <label>Phone Number <span class="req">*</span></label>
                    <input type="tel" name="phone" placeholder="+91 9876543210" required
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"/>
                </div>
                <div class="form-group">
                    <label>City <span class="req">*</span></label>
                    <input type="text" name="city" placeholder="e.g. Mumbai" required
                           value="<?= htmlspecialchars($_POST['city'] ?? '') ?>"/>
                </div>
                <div class="form-group">
                    <label>State <span class="req">*</span></label>
                    <select name="state" required>
                        <option value="">-- Select State --</option>
                        <?php
                        $states = ["Andhra Pradesh","Assam","Bihar","Chhattisgarh","Delhi","Goa",
                                   "Gujarat","Haryana","Himachal Pradesh","Jharkhand","Karnataka",
                                   "Kerala","Madhya Pradesh","Maharashtra","Manipur","Meghalaya",
                                   "Mizoram","Nagaland","Odisha","Punjab","Rajasthan","Sikkim",
                                   "Tamil Nadu","Telangana","Tripura","Uttar Pradesh","Uttarakhand",
                                   "West Bengal","Other"];
                        foreach ($states as $s) {
                            $sel = (($_POST['state'] ?? '') === $s) ? 'selected' : '';
                            echo "<option value='$s' $sel>$s</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- BUSINESS INFO -->
            <div class="section-title">💼 Business Information</div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Business / Startup Name <span class="req">*</span></label>
                    <input type="text" name="business_name" placeholder="e.g. SheBuilds Tech" required
                           value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>"/>
                </div>
                <div class="form-group">
                    <label>Business Type <span class="req">*</span></label>
                    <select name="business_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="Technology"  <?= (($_POST['business_type']??'')==='Technology') ?'selected':'' ?>>Technology / IT</option>
                        <option value="Fashion"     <?= (($_POST['business_type']??'')==='Fashion')    ?'selected':'' ?>>Fashion & Apparel</option>
                        <option value="Food"        <?= (($_POST['business_type']??'')==='Food')       ?'selected':'' ?>>Food & Beverages</option>
                        <option value="Education"   <?= (($_POST['business_type']??'')==='Education')  ?'selected':'' ?>>Education & Training</option>
                        <option value="Healthcare"  <?= (($_POST['business_type']??'')==='Healthcare') ?'selected':'' ?>>Healthcare & Wellness</option>
                        <option value="Finance"     <?= (($_POST['business_type']??'')==='Finance')    ?'selected':'' ?>>Finance & Banking</option>
                        <option value="Agriculture" <?= (($_POST['business_type']??'')==='Agriculture')?'selected':'' ?>>Agriculture</option>
                        <option value="Arts"        <?= (($_POST['business_type']??'')==='Arts')       ?'selected':'' ?>>Arts & Crafts</option>
                        <option value="Other"       <?= (($_POST['business_type']??'')==='Other')      ?'selected':'' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Years of Experience <span class="req">*</span></label>
                    <select name="experience" required>
                        <option value="">-- Select --</option>
                        <option value="0-1" <?= (($_POST['experience']??'')==='0-1')?'selected':'' ?>>0 – 1 year (Beginner)</option>
                        <option value="1-3" <?= (($_POST['experience']??'')==='1-3')?'selected':'' ?>>1 – 3 years</option>
                        <option value="3-5" <?= (($_POST['experience']??'')==='3-5')?'selected':'' ?>>3 – 5 years</option>
                        <option value="5+"  <?= (($_POST['experience']??'')==='5+') ?'selected':'' ?>>5+ years (Expert)</option>
                    </select>
                </div>
            </div>

            <!-- ACCOUNT SECURITY -->
            <div class="section-title">🔒 Account Security</div>
            <div class="grid-2">
                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="pw1"
                               placeholder="Min. 6 characters" required minlength="6"/>
                        <span class="pw-toggle" onclick="togglePw('pw1', this)">👁️</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password <span class="req">*</span></label>
                    <div class="pw-wrap">
                        <input type="password" name="confirm_password" id="pw2"
                               placeholder="Re-enter password" required/>
                        <span class="pw-toggle" onclick="togglePw('pw2', this)">👁️</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">🚀 Register Now</button>

        </form>

        <div class="form-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <div class="bottom-strip">
        © 2025 Women Entrepreneurship Portal &nbsp;|&nbsp; Empowering Women Since Day One 💪
    </div>
</div>

<script>
    function togglePw(id, el) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
            el.textContent = "🙈";
        } else {
            input.type = "password";
            el.textContent = "👁️";
        }
    }

    function validateForm() {
        const pw1 = document.getElementById('pw1').value;
        const pw2 = document.getElementById('pw2').value;
        if (pw1 !== pw2) {
            alert("Passwords do not match!");
            return false;
        }
        if (pw1.length < 6) {
            alert("Password must be at least 6 characters.");
            return false;
        }
        return true;
    }
</script>

</body>
</html>