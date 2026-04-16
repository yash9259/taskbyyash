<?php
require_once __DIR__ . '/setup.php';

$conn = getDbConnection();

$minPrice = trim($_GET['min_price'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');
$location = trim($_GET['location'] ?? '');

$conditions = [];
$params = [];
$types = '';

if ($minPrice !== '' && is_numeric($minPrice)) {
    $conditions[] = 'price >= ?';
    $params[] = (float)$minPrice;
    $types .= 'd';
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $conditions[] = 'price <= ?';
    $params[] = (float)$maxPrice;
    $types .= 'd';
}

if ($location !== '') {
    $conditions[] = 'location LIKE ?';
    $params[] = '%' . $location . '%';
    $types .= 's';
}

$sql = 'SELECT id, title, price, location, created_at FROM properties';
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Failed to prepare list query.');
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property List</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap');

        :root {
            --bg-1: #fef7ed;
            --bg-2: #ffedd5;
            --ink: #1f2937;
            --muted: #6b7280;
            --primary: #0f766e;
            --primary-2: #115e59;
            --card: rgba(255, 255, 255, 0.88);
            --line: #e5e7eb;
            --soft: #f8fafc;
            --warn-bg: #fffbeb;
            --warn-line: #fde68a;
            --warn-text: #92400e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Manrope, "Trebuchet MS", "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 12% 0%, rgba(15, 118, 110, 0.16), transparent 36%),
                radial-gradient(circle at 90% 5%, rgba(249, 115, 22, 0.14), transparent 30%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2));
            min-height: 100vh;
            padding: 30px 16px;
        }

        .page {
            max-width: 1080px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .title-wrap h1 {
            margin: 0;
            font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .title-wrap p {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .link-btn {
            text-decoration: none;
            color: #ffffff;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 10px 14px;
            border-radius: 10px;
            font-weight: 700;
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.22);
            white-space: nowrap;
        }

        .panel {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 18px;
            box-shadow: 0 16px 40px rgba(31, 41, 55, 0.12);
            backdrop-filter: blur(4px);
            overflow: hidden;
            margin-bottom: 16px;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.2));
        }

        .panel-head h2 {
            margin: 0;
            font-size: 1.08rem;
        }

        .panel-body {
            padding: 18px 20px;
        }

        .filter-box {
            margin: 0;
        }

        .row {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(170px, 1fr)) auto;
            align-items: end;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        label {
            font-weight: 700;
            font-size: 0.93rem;
        }

        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            font: inherit;
            background: #ffffff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: #0d9488;
            box-shadow: 0 0 0 4px rgba(13, 148, 136, 0.15);
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        button {
            border: 0;
            border-radius: 10px;
            padding: 11px 16px;
            cursor: pointer;
            color: #ffffff;
            font: inherit;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            box-shadow: 0 10px 22px rgba(15, 118, 110, 0.25);
            transition: transform 0.16s ease, box-shadow 0.16s ease;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(15, 118, 110, 0.3);
        }

        .clear-link {
            text-decoration: none;
            color: #0f766e;
            font-weight: 700;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #99f6e4;
            background: #f0fdfa;
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #ffffff;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        th {
            background: var(--soft);
            font-size: 0.86rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
        }

        tr:hover td {
            background: #fffbf5;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .price {
            font-weight: 800;
            color: #0f766e;
        }

        .empty {
            margin-top: 16px;
            padding: 12px;
            border-radius: 10px;
            background: var(--warn-bg);
            border: 1px solid var(--warn-line);
            color: var(--warn-text);
            font-weight: 600;
        }

        @media (max-width: 900px) {
            .row {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 720px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .row {
                grid-template-columns: 1fr;
            }

            .actions {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div class="title-wrap">
                <h1>Property List</h1>
                <p>Browse all listings and quickly filter by budget or area.</p>
            </div>
            <a class="link-btn" href="add_property.php">Add Property</a>
        </header>

        <section class="panel">
            <div class="panel-head">
                <h2>Filter Listings</h2>
            </div>
            <div class="panel-body">
                <form class="filter-box" method="GET" action="index.php">
                    <div class="row">
                        <div class="field">
                            <label for="min_price">Min Price</label>
                            <input type="number" id="min_price" name="min_price" step="0.01" min="0" value="<?php echo htmlspecialchars($minPrice, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="field">
                            <label for="max_price">Max Price</label>
                            <input type="number" id="max_price" name="max_price" step="0.01" min="0" value="<?php echo htmlspecialchars($maxPrice, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="field">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="actions">
                            <button type="submit">Apply Filter</button>
                            <a class="clear-link" href="index.php">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <?php if (empty($properties)): ?>
            <div class="empty">No properties found for the selected filter.</div>
        <?php else: ?>
            <section class="panel">
                <div class="panel-head">
                    <h2>Saved Properties (<?php echo count($properties); ?>)</h2>
                </div>
                <div class="panel-body">
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Price</th>
                                    <th>Location</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($properties as $property): ?>
                                    <tr>
                                        <td><?php echo (int)$property['id']; ?></td>
                                        <td><?php echo htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="price"><?php echo number_format((float)$property['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($property['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($property['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
