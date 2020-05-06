<?php
  date_default_timezone_set('America/New_York');
  require_once('config.php');

  $query = "";
  if (isset($_GET["query"]) && strlen($_GET["query"]) > 0) {
    $query = strtolower($_GET["query"]);
  }

  $hasQuery = $query !== "";

  function sort_by_key($a, $b) {
    return $a['key'] <=> $b['key'];
  }

  $folders = array_diff(scandir($contentDir), array('..', '.', '.DS_Store'));

  $classes = array();
  foreach ($folders as $folder) {
    $folderInfo = array_map('trim', explode(' _ ', $folder));
    $currentDir = $contentDir . $folder . '/';
    $contents = array_diff(scandir($currentDir), array('..', '.', '.DS_Store'));

    $works = array();
    $classMatch = false;
    foreach ($contents as $content) {
      $contentInfo = array_map('trim', explode(' _ ', $content));
      $urlBase = array_map('trim', explode(' ', $contentInfo[3]))[0];
      $urlBase = str_replace(':', '/', $urlBase);
      $workUrl = 'http://' . $urlBase;
      if (strpos($urlBase, '@') !== false) {
        $workUrl = 'mailto:' . $urlBase;
      }


      $contentDisplayInfo = explode(' ', $contentInfo[0]);
      $key = $contentDisplayInfo[0];
      $show_if_excerpted = false;

      if (sizeof($contentDisplayInfo) > 1 && $contentDisplayInfo[1] == '+') {
        $show_if_excerpted = true;
      }

      if (!is_numeric($key) || floatval($key) <= 0) {
        continue;
      }

      $mime = mime_content_type($currentDir . $content);
      $type = 'image';
      if (strpos($mime, 'video') !== false) {
        $type = 'video';
      }

      $work = array(
        "key" => floatval($key),
        "name" => $contentInfo[1],
        "addendum" => $contentInfo[2],
        "url" => $workUrl,
        "src" => htmlentities($currentDir . $content),
        "type" => $type,
        "show_if_excerpted" => $show_if_excerpted,
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

    usort($works, "sort_by_key");

    $displayInfo = explode(' ', $folderInfo[0]);
    $key = $displayInfo[0];
    $excerpted = false;

    if (sizeof($displayInfo) > 1 && $displayInfo[1] == '+') {
      $excerpted = true;
    }

    if (!is_numeric($key) || floatval($key) <= 0) {
      continue;
    }

    $class = array(
      "key" => floatval($key),
      "title" => $folderInfo[1],
      "maxWidth" => $folderInfo[2],
      "maxHeight" => $folderInfo[3],
      "works" => $works,
      "excerpted" => $excerpted,
    );

    if (!$hasQuery || ($hasQuery && $classMatch)) {
      $classes []= $class;
    }
  }

  usort($classes, "sort_by_key");
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
    <nav class="layout-block pre-heading">
      <p class="heading balance-text">
        Experimentation and play.
      </p>
    </nav>
    <nav class="layout-block heading">
      <div class="header">
        <div class="header-left">
          <p><a class="break-word" href="<?= $siteURL ?>"><?= $siteName ?></a></p>
          <p class="balance-text">Some student work from <a href="<?= $nameURL ?>" target="_blank"><?= $name; ?></a>’s classes & workshops:</p>
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
    </nav>
    <?php foreach($classes as $class): ?>
      <div class="layout-block class" style="--max-width: <?= $class['maxWidth'] ?>px; --max-height: <?= $class['maxHeight'] ?>px; --ratio: <?= $class['maxWidth'] / $class['maxHeight']; ?>">
        <div class="heading">
          <p class="balance-text"><?= $class['title'] ?></p>
        </div>
        <ul class="works">
          <?php foreach ($class['works'] as $work): ?>
              <li
                class="
                  work
                  <?php if (($class['excerpted'] && $work['show_if_excerpted']) || !$class['excerpted'] || $hasQuery): ?>
                    active
                  <?php else: ?>
                    hidden
                  <?php endif; ?>
                  "
                >
                <a href="<?= $work['url'] ?>" target="_blank">
                  <?php if($work['type'] == 'image'): ?>
                    <img class="media lazy" data-src="<?= $work['src'] ?>">
                  <?php elseif($work['type'] == 'video'): ?>
                    <video class="media" src="<?= $work['src'] ?>" autoplay muted playsinline loop>
                    </video>
                  <?php endif; ?>
                </a>
              <p>
                <a href="<?= $work['url'] ?>" target="_blank"><?= $work['name'] ?></a>, <?= $work['addendum'] ?>
              </p>
            </li>
          <?php endforeach; ?>
          <?php if ($class['excerpted'] && !$hasQuery): ?>
            <li class="work more heading">
              <p>
                <a href="javascript:;" class="show-more-link">
                  More...
                </a>
              </p>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>

  <script src="/assets/balancetext.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      balanceText(document.getElementsByClassName('balance-text'),{watch: true});
    });

    document.addEventListener('DOMContentLoaded', () => {
      [].forEach.call(document.getElementsByClassName('show-more-link'), (el) => {
        el.addEventListener('click', (e) => {
          let hidden = el.parentElement.parentElement.parentElement.querySelectorAll('.hidden');
          [].forEach.call(hidden, (hiddenEl) => hiddenEl.classList.toggle('active'));
          console.log(el.innerHTML);
          if (el.dataset.active) {
            el.innerHTML = 'More...';
            el.parentElement.parentElement.parentElement.parentElement.scrollIntoView(true);
            delete el.dataset.active;
          } else {
            el.innerHTML = 'Less...';
            el.dataset.active = true;
          }
        });
      });
    });

    document.addEventListener("DOMContentLoaded", function() {
      let lazyImages = [].slice.call(document.querySelectorAll("img.lazy"));


      let lazyImageObserver = new IntersectionObserver(function(entries, observer) {
        entries.forEach(function(entry) {
          if (entry.isIntersecting) {
            let lazyImage = entry.target;
            lazyImage.src = lazyImage.dataset.src;
            lazyImage.classList.remove("lazy");
            lazyImageObserver.unobserve(lazyImage);
          }
        });
      });

      lazyImages.forEach(function(lazyImage) {
        lazyImageObserver.observe(lazyImage);
      }, {rootMargin: "0px 0px 256px 0px"});
    });
  </script>
</body>
</html>
