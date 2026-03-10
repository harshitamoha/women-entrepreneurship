<?php
// Database Connection
$host     = "localhost";
$dbname   = "women_db";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all members from database
$search      = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_type = isset($_GET['type'])   ? $conn->real_escape_string($_GET['type'])   : '';
$filter_exp  = isset($_GET['exp'])    ? $conn->real_escape_string($_GET['exp'])    : '';

$sql = "SELECT * FROM members WHERE 1=1";

if ($search != '') {
    $sql .= " AND (full_name LIKE '%$search%' OR business_name LIKE '%$search%' OR city LIKE '%$search%')";
}
if ($filter_type != '') {
    $sql .= " AND business_type = '$filter_type'";
}
if ($filter_exp != '') {
    $sql .= " AND experience = '$filter_exp'";
}

$sql .= " ORDER BY registered_at DESC";
$result = $conn->query($sql);
$total  = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Entrepreneurs | ShaktiUdyam</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --rose:  #e8436a;
            --deep:  #1a0a14;
            --warm:  #fff5f0;
            --gold:  #f4a840;
            --muted: #8a6070;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--warm);
            color: var(--deep);
        }

        /* ── NAVBAR ── */
        nav {
            background: linear-gradient(135deg, #880e4f, #c2185b);
            padding: 16px 60px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 4px 15px rgba(136,14,79,0.3);
            position: sticky; top: 0; z-index: 100;
        }
        .nav-logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; font-weight: 900;
            color: white; text-decoration: none;
        }
        .nav-links { display: flex; gap: 28px; align-items: center; }
        .nav-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none; font-weight: 500; font-size: 0.9rem;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: white; }
        .nav-links .active {
            background: white; color: #880e4f;
            padding: 7px 18px; border-radius: 20px;
            font-weight: 700;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            background: linear-gradient(135deg, #880e4f 0%, #c2185b 50%, #e91e63 100%);
            color: white; padding: 60px;
            text-align: center;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.8rem; font-weight: 900; margin-bottom: 12px;
        }
        .page-header p { font-size: 1rem; opacity: 0.85; }
        .total-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 6px 20px; border-radius: 50px;
            font-size: 0.9rem; font-weight: 600;
            margin-top: 16px;
        }

        /* ── SEARCH & FILTER ── */
        .filter-section {
            background: white;
            padding: 28px 60px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            display: flex; gap: 16px; flex-wrap: wrap; align-items: center;
        }
        .search-box {
            flex: 1; min-width: 250px;
            display: flex; align-items: center;
            border: 2px solid #f0dde5; border-radius: 50px;
            padding: 10px 20px; gap: 10px;
            background: #fafafa;
        }
        .search-box input {
            border: none; outline: none; background: transparent;
            font-size: 0.95rem; width: 100%; color: var(--deep);
            font-family: 'DM Sans', sans-serif;
        }
        .filter-select {
            padding: 12px 20px;
            border: 2px solid #f0dde5; border-radius: 50px;
            font-size: 0.88rem; color: var(--deep);
            background: #fafafa; cursor: pointer; outline: none;
            font-family: 'DM Sans', sans-serif;
        }
        .filter-select:focus { border-color: #e91e63; }
        .btn-filter {
            background: var(--rose); color: white;
            padding: 12px 28px; border-radius: 50px; border: none;
            font-size: 0.9rem; font-weight: 600; cursor: pointer;
            transition: background 0.2s, transform 0.15s;
            font-family: 'DM Sans', sans-serif;
        }
        .btn-filter:hover { background: #c73059; transform: translateY(-1px); }
        .btn-reset {
            background: transparent; color: var(--muted);
            padding: 12px 20px; border-radius: 50px;
            border: 2px solid #e0d0d8;
            font-size: 0.9rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; font-family: 'DM Sans', sans-serif;
        }
        .btn-reset:hover { border-color: var(--rose); color: var(--rose); }

        /* ── MAIN CONTENT ── */
        .main { padding: 40px 60px; }

        /* ── CARDS GRID ── */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px; margin-bottom: 40px;
        }
        .entrepreneur-card {
            background: white; border-radius: 20px;
            border: 1px solid #f0dde5;
            padding: 28px;
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative; overflow: hidden;
        }
        .entrepreneur-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #880e4f, #e91e63, #f4a840);
        }
        .entrepreneur-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(232,67,106,0.12);
        }
        .card-top {
            display: flex; align-items: center; gap: 16px; margin-bottom: 20px;
        }
        .avatar {
            width: 56px; height: 56px; border-radius: 50%;
            background: linear-gradient(135deg, #fde8ee, #f8bbd0);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; font-weight: 700; color: #880e4f;
            flex-shrink: 0;
        }
        .card-name { font-size: 1.05rem; font-weight: 700; color: var(--deep); }
        .card-business {
            font-size: 0.82rem; color: var(--rose);
            font-weight: 600; margin-top: 2px;
        }
        .card-details { display: flex; flex-direction: column; gap: 8px; }
        .detail-row {
            display: flex; align-items: center; gap: 8px;
            font-size: 0.85rem; color: var(--muted);
        }
        .detail-icon { font-size: 1rem; width: 20px; }
        .card-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; }
        .tag {
            padding: 4px 12px; border-radius: 50px;
            font-size: 0.75rem; font-weight: 600;
        }
        .tag-type { background: #fde8ee; color: #880e4f; }
        .tag-exp  { background: #fef3e0; color: #b45309; }
        .tag-city { background: #e0f4f4; color: #0f766e; }
        .card-date {
            margin-top: 16px; padding-top: 14px;
            border-top: 1px solid #f0e8ec;
            font-size: 0.78rem; color: #bbb;
        }

        /* ── TABLE VIEW ── */
        .table-wrap {
            background: white; border-radius: 20px;
            border: 1px solid #f0dde5; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .table-header {
            padding: 20px 28px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid #f0e8ec;
        }
        .table-header h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem; font-weight: 700;
        }
        .view-toggle { display: flex; gap: 8px; }
        .toggle-btn {
            padding: 6px 16px; border-radius: 20px; border: none;
            font-size: 0.82rem; font-weight: 600; cursor: pointer;
            transition: all 0.2s; font-family: 'DM Sans', sans-serif;
        }
        .toggle-btn.active { background: var(--rose); color: white; }
        .toggle-btn:not(.active) { background: #f5eef1; color: var(--muted); }

        table {
            width: 100%; border-collapse: collapse;
        }
        thead {
            background: linear-gradient(135deg, #880e4f, #c2185b);
            color: white;
        }
        thead th {
            padding: 14px 20px; text-align: left;
            font-size: 0.82rem; font-weight: 600;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        tbody tr {
            border-bottom: 1px solid #f9f0f3;
            transition: background 0.15s;
        }
        tbody tr:hover { background: #fff5f7; }
        tbody tr:last-child { border-bottom: none; }
        tbody td {
            padding: 14px 20px; font-size: 0.88rem; color: var(--deep);
        }
        .td-name { font-weight: 600; }
        .td-business { color: var(--rose); font-weight: 500; }
        .td-badge {
            display: inline-block;
            padding: 3px 10px; border-radius: 50px;
            font-size: 0.75rem; font-weight: 600;
            background: #fde8ee; color: #880e4f;
        }

        /* ── NO DATA ── */
        .no-data {
            text-align: center; padding: 80px 40px;
            color: var(--muted);
        }
        .no-data .nd-icon { font-size: 4rem; margin-bottom: 16px; }
        .no-data h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; margin-bottom: 8px; color: var(--deep);
        }
        .no-data p { font-size: 0.9rem; }
        .no-data a {
            display: inline-block; margin-top: 20px;
            background: var(--rose); color: white;
            padding: 12px 30px; border-radius: 50px;
            text-decoration: none; font-weight: 600;
        }

        /* ── FOOTER ── */
        footer {
            background: #120710; color: rgba(255,255,255,0.5);
            padding: 30px 60px; text-align: center; font-size: 0.85rem;
            margin-top: 60px;
        }

        @media (max-width: 768px) {
            nav { padding: 14px 20px; }
            .nav-links { gap: 12px; }
            .nav-links a { font-size: 0.8rem; }
            .page-header { padding: 40px 24px; }
            .page-header h1 { font-size: 2rem; }
            .filter-section { padding: 20px 24px; }
            .main { padding: 24px; }
            .cards-grid { grid-template-columns: 1fr; }
            .table-wrap { overflow-x: auto; }
            footer { padding: 24px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a href="index.html" class="nav-logo">👩‍💼 ShaktiUdyam</a>
    <div class="nav-links">
        <a href="index.html">🏠 Home</a>
        <a href="schemes.html">📋 Schemes</a>
        <a href="stories.html">📖 Stories</a>
        <a href="register.php">📝 Register</a>
        <a href="entrepreneurs.php" class="active">💼 Entrepreneurs</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <h1>👩‍💼 Women Entrepreneurs</h1>
    <p>Meet the inspiring women building businesses across India</p>
    <div class="total-badge">
        <?= $total ?> Entrepreneur<?= $total != 1 ? 's' : '' ?> Registered
    </div>
</div>

<!-- SEARCH & FILTER -->
<div class="filter-section">
    <form method="GET" action="entrepreneurs.php" style="display:flex; gap:16px; flex-wrap:wrap; width:100%; align-items:center;">
        <div class="search-box">
            <span>🔍</span>
            <input type="text" name="search" placeholder="Search by name, business or city..."
                   value="<?= htmlspecialchars($search) ?>"/>
        </div>
        <select name="type" class="filter-select">
            <option value="">All Business Types</option>
            <option value="Technology"  <?= $filter_type==='Technology'  ?'selected':'' ?>>Technology / IT</option>
            <option value="Fashion"     <?= $filter_type==='Fashion'     ?'selected':'' ?>>Fashion & Apparel</option>
            <option value="Food"        <?= $filter_type==='Food'        ?'selected':'' ?>>Food & Beverages</option>
            <option value="Education"   <?= $filter_type==='Education'   ?'selected':'' ?>>Education & Training</option>
            <option value="Healthcare"  <?= $filter_type==='Healthcare'  ?'selected':'' ?>>Healthcare & Wellness</option>
            <option value="Finance"     <?= $filter_type==='Finance'     ?'selected':'' ?>>Finance & Banking</option>
            <option value="Agriculture" <?= $filter_type==='Agriculture' ?'selected':'' ?>>Agriculture</option>
            <option value="Arts"        <?= $filter_type==='Arts'        ?'selected':'' ?>>Arts & Crafts</option>
            <option value="Other"       <?= $filter_type==='Other'       ?'selected':'' ?>>Other</option>
        </select>
        <select name="exp" class="filter-select">
            <option value="">All Experience</option>
            <option value="0-1" <?= $filter_exp==='0-1'?'selected':'' ?>>0–1 year</option>
            <option value="1-3" <?= $filter_exp==='1-3'?'selected':'' ?>>1–3 years</option>
            <option value="3-5" <?= $filter_exp==='3-5'?'selected':'' ?>>3–5 years</option>
            <option value="5+"  <?= $filter_exp==='5+' ?'selected':'' ?>>5+ years</option>
        </select>
        <button type="submit" class="btn-filter">🔍 Search</button>
        <a href="entrepreneurs.php" class="btn-reset">✕ Reset</a>
    </form>
</div>

<!-- MAIN -->
<div class="main">

<?php if ($total == 0): ?>
    <!-- NO DATA -->
    <div class="no-data">
        <div class="nd-icon">🔍</div>
        <h3>No Entrepreneurs Found</h3>
        <p>
            <?= ($search || $filter_type || $filter_exp)
                ? "Try different search or filters."
                : "No one has registered yet. Be the first!" ?>
        </p>
        <a href="register.php">Register Now →</a>
    </div>

<?php else: ?>

    <!-- TABLE -->
    <div class="table-wrap">
        <div class="table-header">
            <h3>📋 All Registered Entrepreneurs</h3>
            <span style="font-size:0.85rem; color:var(--muted);">
                Showing <?= $total ?> result<?= $total != 1 ? 's' : '' ?>
            </span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Business</th>
                    <th>Type</th>
                    <th>City & State</th>
                    <th>Experience</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td class="td-name">
                        <?= htmlspecialchars($row['full_name']) ?>
                        <div style="font-size:0.78rem; color:var(--muted);">
                            <?= htmlspecialchars($row['email']) ?>
                        </div>
                    </td>
                    <td class="td-business"><?= htmlspecialchars($row['business_name']) ?></td>
                    <td><span class="td-badge"><?= htmlspecialchars($row['business_type']) ?></span></td>
                    <td><?= htmlspecialchars($row['city']) ?>, <?= htmlspecialchars($row['state']) ?></td>
                    <td><?= htmlspecialchars($row['experience']) ?> yrs</td>
                    <td><?= date('d M Y', strtotime($row['registered_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- CARDS GRID -->
    <h3 style="font-family:'Playfair Display',serif; font-size:1.4rem; margin: 40px 0 24px;">
        🌸 Entrepreneur Profiles
    </h3>
    <div class="cards-grid">
        <?php
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()):
            $initials = strtoupper(substr($row['full_name'], 0, 1));
        ?>
        <div class="entrepreneur-card">
            <div class="card-top">
                <div class="avatar"><?= $initials ?></div>
                <div>
                    <div class="card-name"><?= htmlspecialchars($row['full_name']) ?></div>
                    <div class="card-business">🏢 <?= htmlspecialchars($row['business_name']) ?></div>
                </div>
            </div>
            <div class="card-details">
                <div class="detail-row">
                    <span class="detail-icon">📧</span>
                    <?= htmlspecialchars($row['email']) ?>
                </div>
                <div class="detail-row">
                    <span class="detail-icon">📞</span>
                    <?= htmlspecialchars($row['phone']) ?>
                </div>
                <div class="detail-row">
                    <span class="detail-icon">📍</span>
                    <?= htmlspecialchars($row['city']) ?>, <?= htmlspecialchars($row['state']) ?>
                </div>
            </div>
            <div class="card-tags">
                <span class="tag tag-type"><?= htmlspecialchars($row['business_type']) ?></span>
                <span class="tag tag-exp">⏱ <?= htmlspecialchars($row['experience']) ?> yrs</span>
                <span class="tag tag-city">📍 <?= htmlspecialchars($row['city']) ?></span>
            </div>
            <div class="card-date">
                🗓 Joined <?= date('d M Y', strtotime($row['registered_at'])) ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

<?php endif; ?>
</div>

<!-- FOOTER -->
<footer>
    &copy; 2025 ShaktiUdyam – Women Entrepreneurship Portal 💪
</footer>

</body>
</html>
<?php $conn->close(); ?>