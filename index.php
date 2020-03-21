<?php
  date_default_timezone_set('America/New_York');
  require_once('config.php');

  $query = "";
  if (isset($_GET["query"]) && strlen($_GET["query"]) > 0) {
    $query = strtolower($_GET["query"]);
  }

  $hasQuery = $query !== "";

  $folders = array_diff(scandir($contentDir), array('..', '.', '.DS_Store'));

  $classes = array();
  foreach ($folders as $folder) {
    $folderInfo = array_map('trim', explode('_', $folder));
    $currentDir = $contentDir . $folder . '/';
    $contents = array_diff(scandir($currentDir), array('..', '.', '.DS_Store'));

    $works = array();
    $classMatch = false;
    foreach ($contents as $content) {
      $contentInfo = array_map('trim', explode('_', $content));
      $urlBase = array_map('trim', explode(' ', $contentInfo[3]))[0];
      $workUrl = '//' . str_replace(':', '/', $urlBase);

      $work = array(
        "name" => $contentInfo[1],
        "addendum" => $contentInfo[2],
        "url" => $workUrl,
        "imgSrc" => htmlentities($currentDir . $content)
      );

      $workMatch = false;
      if ($hasQuery && (
          strpos(strtolower($work['name']), $query) !== false ||
          strpos(strtolower($work['addendum']), $query) !== false ||
          strpos(strtolower($work['url']), $query) !== false
        )) {
        $workMatch = true;
        $classMatch = true;
      }

      if (!$hasQuery || ($hasQuery && $workMatch)) {
        $works []= $work;
      }
    }

    $class = array(
      "title" => $folderInfo[1],
      "maxWidth" => $folderInfo[2],
      "maxHeight" => $folderInfo[3],
      "works" => $works
    );

    if (!$hasQuery || ($hasQuery && $classMatch)) {
      $classes []= $class;
    }
  }
?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $siteName; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="/assets/main.css?v=<?= rand(); ?>">

  <!-- Global site tag (gtag.js) - Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=UA-109489967-9"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', '<?= $ga; ?>');
  </script>
</head>
<body style="--background-color: <?= $backgroundColor ?>; --text-color: <?= $textColor; ?>; --link-color: <?= $linkColor; ?>;">
  <div class="layout-wrapper">
    <div class="layout-block heading">
      <div class="header">
        <div class="header-left">
          <p><a class="break-word" href="<?= $siteURL ?>"><?= $siteName ?></a></p>
          <p class="balance-text">Student work from <a href="<?= $nameURL ?>"><?= $name; ?></a>’s classes & workshops:</p>
        </div>
        <div class="header-right">
          <div class="search">
            <form method="get" action="/">
              <input autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" type="text" name="query" placeholder="..." value="<?php if (isset($_GET["query"])) { echo htmlspecialchars($_GET["query"]); }?>">
              <input type="submit" value="Submit" id="submit-button">
            </form>
          </div>
        </div>
      </div>
    </div>
    <?php foreach($classes as $class): ?>
      <div class="layout-block class" style="--max-width: <?= $class['maxWidth'] ?>px; --max-height: <?= $class['maxHeight'] ?>px;">
        <div class="heading">
          <p class="balance-text"><?= $class['title'] ?></p>
        </div>
        <ul class="works">
          <?php foreach ($class['works'] as $work): ?>
            <li class="work">
              <div class="work-image">
                <a href="<?= $work['url'] ?>" target="_blank">
                  <img src="<?= $work['imgSrc'] ?>" loading="lazy">
                </a>
              </div>
              <p>
                <a href="<?= $work['url'] ?>" target="_blank"><?= $work['name'] ?></a>, <?= $work['addendum'] ?>
              </p>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>

  <script src="/assets/balancetext.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      balanceText(document.getElementsByClassName('balance-text'));
    });
  </script>
</body>
</html>
