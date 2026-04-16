<?php
require_once __DIR__ . '/setup.php';

$conn = getDbConnection();

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    }

    if ($price === '' || !is_numeric($price) || (float)$price < 0) {
        $errors[] = 'Price must be a valid non-negative number.';
    }

    if ($location === '') {
        $errors[] = 'Location is required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO properties (title, price, location) VALUES (?, ?, ?)');

        if (!$stmt) {
            $errors[] = 'Failed to prepare insert query.';
        } else {
            $numericPrice = (float)$price;
            $stmt->bind_param('sds', $title, $numericPrice, $location);

            if ($stmt->execute()) {
                $successMessage = 'Property added successfully.';
                $title = '';
                $price = '';
                $location = '';
            } else {
                $errors[] = 'Failed to add property.';
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap');

        :root {
            --bg-1: #fef7ed;
            --bg-2: #ffedd5;
            --ink: #1f2937;
            --muted: #6b7280;
            --primary: #0f766e;
            --primary-2: #115e59;
            --card: rgba(255, 255, 255, 0.86);
            --line: #e5e7eb;
            --danger-bg: #fef2f2;
            --danger-text: #991b1b;
            --danger-line: #fecaca;
            --ok-bg: #ecfdf5;
            --ok-text: #065f46;
            --ok-line: #a7f3d0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Manrope, "Trebuchet MS", "Segoe UI", sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at 20% 10%, rgba(15, 118, 110, 0.18), transparent 34%),
                radial-gradient(circle at 90% 0%, rgba(249, 115, 22, 0.16), transparent 28%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2));
            min-height: 100vh;
            padding: 30px 16px;
        }

        .page {
            max-width: 980px;
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 18px;
        }

        .title-wrap h1 {
            margin: 0;
            font-size: clamp(1.8rem, 3vw, 2.5rem);
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
        }

        .panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.55), rgba(255, 255, 255, 0.2));
        }

        .panel-head h2 {
            margin: 0;
            font-size: 1.1rem;
        }

        .panel-body {
            padding: 20px;
        }

        .errors,
        .success {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 14px;
            font-weight: 600;
        }

        .errors {
            background: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid var(--danger-line);
        }

        .errors ul {
            margin: 0;
            padding-left: 18px;
        }

        .success {
            background: var(--ok-bg);
            color: var(--ok-text);
            border: 1px solid var(--ok-line);
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 700;
            font-size: 0.95rem;
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

        .helper {
            margin-top: 10px;
            color: var(--muted);
            font-size: 0.92rem;
        }

        @media (max-width: 760px) {
            form {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div class="title-wrap">
                <h1>Add Property</h1>
                <p>Capture a listing with title, price, and location.</p>
            </div>
            <a class="link-btn" href="index.php">View All Properties</a>
        </header>

        <section class="panel">
            <div class="panel-head">
                <h2>Property Details</h2>
            </div>
            <div class="panel-body">
                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($successMessage !== ''): ?>
                    <div class="success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <form method="POST" action="add_property.php">
                    <div class="field field-full">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" maxlength="255" required value="<?php echo htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="field">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required value="<?php echo htmlspecialchars($price ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="field">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" maxlength="255" required value="<?php echo htmlspecialchars($location ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="field field-full">
                        <button type="submit">Add Property</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>

<?php
$conn->close();
