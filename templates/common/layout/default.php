<!DOCTYPE HTML>
<html>
  <head>
      <title><?= $page_title ?? '' ?></title>
      <meta charset="UTF-8" />

      <link rel="shortcut icon" href="<?= $TPL_BASE ?>/img/icon.png" type="image/png">
      <link rel="stylesheet" type="text/css" href="<?= $TPL_BASE ?>/css/reset.css" />
      <link rel="stylesheet" type="text/css" href="<?= $TPL_BASE ?>/css/basic.css" />
      <link rel="stylesheet" type="text/css" href="<?= "$TPL_BASE/$AREA/css/$PAGE" ?>.css" />

      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
      <script src="<?= $TPL_BASE ?>/script/jquery.maskedinput.min.js"></script>
      <script>let LEAD_STATUSES = <?= json_encode(\Model\Lead::STATUSES) ?></script>
      <script src="<?= $TPL_BASE ?>/script/basic.js"></script>
      <script src="<?= "$TPL_BASE/$AREA/script/$PAGE" ?>.js"></script>
  </head>
  <body>
    <aside id="page-menu">
        <div class="logo">СпецТехника</div>
        <?php if ($USER) : ?>
            <nav class="menu">
                <?php if ($USER->role === 'admin') : ?>
                    <a href="/cp"<?= $PAGE === 'home' ? ' class="current"' : '' ?>>Статистика</a>
                    <a href="/cp/dealers"<?= $PAGE === 'dealers' ? ' class="current"' : '' ?>>Дилеры</a>
                    <a href="/cp/leads"<?= $PAGE === 'leads' ? ' class="current"' : '' ?>>Лиды</a>
                    <a href="/cp/settings"<?= $PAGE === 'settings' ? ' class="current"' : '' ?>>Настройки</a>
                <?php else : ?>
                    <a href="/"<?= $PAGE === '' ? ' class="current"' : '' ?>>Статистика</a>
                    <a href="/leads"<?= $PAGE === 'leads' ? ' class="current"' : '' ?>>Лиды</a>
                    <a href="/notification"<?= $PAGE === 'notification' ? ' class="current"' : '' ?>>Оповещения</a>
                    <a href="/profile"<?= $PAGE === 'profile' ? ' class="current"' : '' ?>>Профиль</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </aside>

    <header id="page-header">
        <?php if ($USER) : ?>
            <div class="profile-menu">
                <a class="name"><?= htmlspecialchars($USER->name) ?></a>
                <nav class="menu">
                    <a href="/logout">Выйти</a>
                </nav>
            </div>
        <?php endif; ?>

        <?php if (isset($H1)): ?><h1><?= htmlspecialchars($H1) ?></h1><?php endif ?>
    </header>
    
    <section id="page-content">
        <?php if (isset($PAGE_TEMPLATE)) echo $this->render($PAGE_TEMPLATE) ?>
    </section>
  </body>
</html>