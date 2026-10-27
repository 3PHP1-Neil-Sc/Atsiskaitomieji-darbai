
<?php
date_default_timezone_set('Europe/Vilnius');

require_once 'db.php';

// Naujo darbo pridėjimas arba pažymėjimas kaip atlikto.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $darbas = trim($_POST['darbas'] ?? '');
        $laikas = trim($_POST['laikas'] ?? '');

        if ($darbas !== '' && mb_strlen($darbas) <= 255) {
            // Tuščias termino laukas saugomas kaip SQL NULL.
            if ($laikas === '') {
                $laikas = null;
            } else {
                $date = DateTime::createFromFormat(
                    '!Y-m-d\TH:i',
                    $laikas
                );

                $errors = DateTime::getLastErrors();

                if (
                    !$date ||
                    ($errors !== false &&
                        ($errors['warning_count'] > 0 ||
                         $errors['error_count'] > 0)) ||
                    $date->format('Y-m-d\TH:i') !== $laikas
                ) {
                    http_response_code(400);
                    exit('Neteisingas darbo termino formatas.');
                }

                $laikas = $date->format('Y-m-d H:i:s');
            }

            $stmt = $pdo->prepare(
                'INSERT INTO darbai (darbas, laikas)
                 VALUES (:darbas, :laikas)'
            );

            $stmt->execute([
                'darbas' => $darbas,
                'laikas' => $laikas,
            ]);
        }

        header('Location: index.php');
        exit;
    }

    if ($action === 'complete') {
        $id = filter_input(
            INPUT_POST,
            'id',
            FILTER_VALIDATE_INT
        );

        if ($id !== false && $id !== null && $id > 0) {
            $stmt = $pdo->prepare(
                'UPDATE darbai
                 SET atliktas = 1
                 WHERE id = :id AND atliktas = 0'
            );

            $stmt->execute(['id' => $id]);
        }

        header('Location: index.php');
        exit;
    }
}

// Pirmiausia neatlikti darbai, po jų – atlikti.
// Kiekvienoje grupėje darbai su terminu rodomi pirmiau.
$stmt = $pdo->query(
    'SELECT id, darbas, laikas, atliktas
     FROM darbai
     ORDER BY
        atliktas ASC,
        (laikas IS NULL) ASC,
        laikas ASC,
        id DESC'
);

$darbai = $stmt->fetchAll();

$now = new DateTimeImmutable('now');

function h($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>
<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Darbų sąrašas</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 32px 16px;
            background: #f3f5f9;
            color: #1f2937;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 850px;
            margin: 0 auto;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 28px;
        }

        .panel {
            background: white;
            padding: 24px;
            margin-bottom: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 14px #0000000a;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            min-width: 0;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font: inherit;
        }

        button {
            padding: 11px 15px;
            border: 0;
            border-radius: 7px;
            background: #2563eb;
            color: white;
            font: inherit;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .task {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 17px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .task:last-child {
            border-bottom: 0;
        }

        .task-name {
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .task-meta {
            margin-top: 7px;
            font-size: 13px;
            color: #64748b;
        }

        .overdue {
            background: #fff1f2;
            border-left: 4px solid #dc2626;
            padding-left: 12px;
            padding-right: 12px;
            border-radius: 5px;
        }

        .overdue .task-meta {
            color: #b91c1c;
        }

        .done .task-name {
            text-decoration: line-through;
            color: #64748b;
        }

        .badge {
            display: inline-block;
            margin-top: 7px;
            padding: 4px 8px;
            border-radius: 5px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: bold;
        }

        .completed-badge {
            background: #dcfce7;
            color: #166534;
        }

        .complete-button {
            flex-shrink: 0;
            background: #15803d;
            font-size: 13px;
        }

        .complete-button:hover {
            background: #166534;
        }

        .empty {
            color: #64748b;
            padding: 12px 0;
        }

        @media (max-width: 650px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 18px;
            }

            .task {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
<div class="container">

    <h1>Mano darbai</h1>
    <p class="subtitle">
        Planuokite užduotis ir stebėkite jų atlikimo terminus.
    </p>

    <section class="panel">
        <h2>Pridėti naują darbą</h2>

        <form method="post" action="index.php">
            <input type="hidden" name="action" value="add">

            <div class="form-grid">
                <div>
                    <label for="darbas">Darbas</label>
                    <input
                        type="text"
                        id="darbas"
                        name="darbas"
                        maxlength="255"
                        required
                        placeholder="Pvz., paruošti ataskaitą"
                    >
                </div>

                <div>
                    <label for="laikas">Laikas (neprivaloma)</label>
                    <input
                        type="datetime-local"
                        id="laikas"
                        name="laikas"
                    >
                </div>

                <button type="submit">Pridėti</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2>Darbų sąrašas</h2>

        <?php if (count($darbai) === 0): ?>
            <p class="empty">
                Darbų nėra. Pridėkite savo pirmąjį darbą!
            </p>
        <?php else: ?>

            <?php foreach ($darbai as $darbas): ?>
                <?php
                    $isDone = (bool) $darbas['atliktas'];

                    $deadline = $darbas['laikas'] !== null
                        ? new DateTimeImmutable($darbas['laikas'])
                        : null;

                    $isOverdue = !$isDone
                        && $deadline !== null
                        && $deadline < $now;
                ?>

                <div class="task
                    <?= $isDone ? 'done' : '' ?>
                    <?= $isOverdue ? 'overdue' : '' ?>">

                    <div>
                        <div class="task-name">
                            <?= h($darbas['darbas']) ?>
                        </div>

                        <div class="task-meta">
                            <?php if ($deadline !== null): ?>
                                Terminas:
                                <?= h($deadline->format('Y-m-d H:i')) ?>
                            <?php else: ?>
                                Terminas nenustatytas
                            <?php endif; ?>
                        </div>

                        <?php if ($isOverdue): ?>
                            <span class="badge">
                                VĖLUOJAMA
                            </span>
                        <?php elseif ($isDone): ?>
                            <span class="badge completed-badge">
                                Atlikta
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if (!$isDone): ?>
                        <form method="post" action="index.php">
                            <input
                                type="hidden"
                                name="action"
                                value="complete"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $darbas['id'] ?>"
                            >

                            <button
                                type="submit"
                                class="complete-button"
                            >
                                Atlikta
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </section>

</div>
</body>
</html>
