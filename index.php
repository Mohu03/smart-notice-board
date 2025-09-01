<?php
// index.php — single-file PHP page that mimics the UI/behavior in the screenshot
// Stores the current message + speed in data.json (same folder). No database needed.

$dataFile = DIR . '/data.json';

// Defaults
$data = [
  'message' => 'ECE Department Welcomes BoS Members.',
  'speed'   => 22,
  'updated_at' => null
];

// Load saved values if present
if (file_exists($dataFile)) {
  $raw = @file_get_contents($dataFile);
  $saved = json_decode($raw, true);
  if (is_array($saved)) {
    $data = array_merge($data, $saved);
  }
}

// Handle form post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $msg   = isset($_POST['message']) ? trim($_POST['message']) : $data['message'];
  $speed = isset($_POST['speed']) ? intval($_POST['speed']) : $data['speed'];

  // simple sanitization & bounds
  if ($msg === '') { $msg = $data['message']; }
  if (!is_numeric($speed)) { $speed = $data['speed']; }
  $speed = max(1, min(100, $speed)); // keep between 1–100

  $data = [
    'message' => $msg,
    'speed'   => $speed,
    'updated_at' => date('c')
  ];

  @file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

  // PRG pattern to avoid resubmits on refresh
  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?saved=1');
  exit;
}

$animSpeed = max(1, min(100, (int)$data['speed']));
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>SREC • Display Data Update</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&family=Marcellus&display=swap" rel="stylesheet">
  <style>
    :root {
      --card: #ffffff;
      --bg: #eef5fa;
      --accent: #5c6cff;
      --muted: #6b7a86;
      --ring: rgba(92,108,255,.35);
      --pill: #f2f7fb;
      --speed: <?php echo $animSpeed; ?>;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      background: var(--bg);
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      color: #1f2a37;
    }
    .wrap {
      min-height: 100svh;
      display: grid;
      place-items: start center;
      padding: clamp(16px, 4vw, 48px);
    }
    .card {
      width: min(720px, 92vw);
      background: var(--card);
      border-radius: 20px;
      box-shadow: 0 8px 30px rgba(16,24,40,.08);
      padding: clamp(20px, 4vw, 36px);
      border: 1px solid rgba(2, 6, 23, 0.06);
    }
    .title {
      font-family: 'Marcellus', serif;
      font-size: clamp(28px, 4.5vw, 40px);
      text-align: center;
      margin: 4px 0 2px;
    }
    .subtitle {
      text-align: center;
      font-size: 14px;
      color: var(--accent);
      font-weight: 500;
      margin-bottom: 18px;
    }
    .pill {
      background: var(--pill);
      border-radius: 18px;
      padding: 18px 20px;
      color: #1f2a37;
      line-height: 1.5;
      border: 1px solid rgba(2, 6, 23, 0.06);
    }
    .row { display: grid; grid-template-columns: 1fr; gap: 10px; margin: 12px 0 18px; }
    label {
      text-align: center;
      color: #2b2f36;
      letter-spacing: .05em;
    }
    .field {
      background: #f7fbff;
      border: 1px solid rgba(2, 6, 23, 0.08);
      border-radius: 18px;
      padding: 16px 18px;
      width: 100%;
      outline: none;
      font: inherit;
      transition: box-shadow .15s ease, border-color .15s ease;
    }
    .field:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px var(--ring);
    }
    textarea.field {
      min-height: 88px;
      resize: vertical;
    }
    .btn {
      border: 0;
      border-radius: 22px;
      padding: 14px 22px;
      font-weight: 600;
      cursor: pointer;
      background: linear-gradient(135deg, #6875F5, #7C3AED);
      color: white;
      box-shadow: 0 10px 20px rgba(56, 69, 255, .22);
    }
    .btn:active { transform: translateY(1px); }
.divider { display: grid; grid-template-columns: 1fr auto 1fr; gap: 12px; align-items: center; color: #697586; margin: 6px 0; }
    .divider::before, .divider::after { content: ""; height: 1px; background: rgba(2, 6, 23, 0.08); }

    /* Live ticker */
    .live-label { text-align: center; color: #697586; letter-spacing: .08em; }
    .ticker {
      position: relative; overflow: hidden; border: 1px dashed rgba(2, 6, 23, 0.12);
      border-radius: 30px; padding: 16px; margin-top: 8px;
      background: #fff;
    }
    .ticker-line {
      position: absolute; white-space: nowrap; will-change: transform;
      animation-name: slide;
      animation-duration: calc(120s / var(--speed)); /* higher speed -> faster */
      animation-iteration-count: infinite;
      animation-timing-function: linear;
    }
    @keyframes slide {
      from { transform: translateX(100%); }
      to   { transform: translateX(-100%); }
    }
    .note { text-align: center; font-size: 12px; color: #8b97a3; margin-top: 10px; }
    .saved { text-align: center; color: #16803c; background: #e6f4ea; border: 1px solid #b7e0c2; padding: 8px 12px; border-radius: 12px; margin: 0 auto 8px; width: fit-content; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <?php if (isset($_GET['saved'])): ?>
        <div class="saved">Updated successfully.</div>
      <?php endif; ?>

      <h1 class="title">Welcome</h1>
      <div class="subtitle">Display Data Update...!</div>

      <div class="pill"><?php echo htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'); ?></div>

      <form method="post" class="row" autocomplete="off">
        <div class="divider"><span>speed Data</span></div>
        <input class="field" type="number" name="speed" min="1" max="100" value="<?php echo (int)$data['speed']; ?>" />

        <textarea class="field" name="message" placeholder="Type your message here..."><?php echo htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>

        <button class="btn" type="submit">Send Speed</button>
      </form>

      <div class="divider"><span>live</span></div>
      <div class="ticker">
        <div class="ticker-line">
          <strong style="margin-right: 16px;">&nbsp;<?php echo htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8'); ?>&nbsp;</strong>
          <span style="opacity:.6">• speed: <?php echo (int)$data['speed']; ?></span>
        </div>
      </div>

      <div class="note">Tip: Increase <em>speed</em> to make the ticker move faster (1–100).</div>
    </div>
  </div>
</body>
</html>
