<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= html_escape($title); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.34.1/dist/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <style>
        body{font-family:Nunito,sans-serif;background:#0e1831;color:#fff;min-height:100vh}.shell{max-width:1050px;margin:auto;padding:26px 18px}.back{color:#dfe7ff;text-decoration:none}.hero{margin:18px 0 28px;padding:32px;background:radial-gradient(circle at 85% 10%,#f4b94266,transparent 30%),linear-gradient(135deg,#49358f,#167495);border-radius:24px;box-shadow:0 14px 40px #0005}.hero h1{font-size:clamp(32px,5vw,52px);margin:5px 0}.hero p{max-width:700px;color:#e9edff;line-height:1.7}.season-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.season{position:relative;display:block;min-height:250px;padding:25px;border-radius:22px;text-decoration:none;color:#17233d;background:linear-gradient(145deg,#fff,#f1ecff);border:2px solid transparent;box-shadow:0 14px 28px #0005;transition:.22s}.season:hover{color:#17233d;transform:translateY(-6px);border-color:#f4c95d}.season .icon{font-size:48px}.season h2{margin:12px 0 5px;font-size:28px}.season p{color:#5b6680;line-height:1.55}.season .meta{position:absolute;bottom:22px;left:25px;right:25px;display:flex;justify-content:space-between;font-weight:800;color:#6a4cb5}.badgex{display:inline-block;padding:5px 10px;border-radius:99px;background:#e8e0ff;color:#6747ae;font-size:12px;font-weight:900}@media(max-width:700px){.season-grid{grid-template-columns:1fr}.hero{padding:25px 21px}.season{min-height:220px}}
    </style>
</head>
<body>
<main class="shell">
    <a class="back" href="<?= base_url('belajar'); ?>"><i class="ti ti-arrow-left"></i> Learning Arena</a>
    <section class="hero"><small>ENGLISH QUEST · CAMPAIGN SELECT</small><h1>Choose Your Adventure</h1><p>Each season is a world with its own story, chapters, and learning challenges. Choose a season to begin.</p></section>
    <?php if($this->session->flashdata('error')): ?><div class="alert alert-warning"><?= html_escape($this->session->flashdata('error')); ?></div><?php endif; ?>
    <section class="season-grid">
        <?php foreach($seasons as $s): $is_world=($s['code']??'')==='season_2'; ?>
            <a class="season" style="border-color:<?= html_escape($s['color']); ?>44" href="<?= base_url('belajar/english-quest/season/'.$s['id']); ?>">
                <span class="icon"><?= $s['cover_emoji']==='book'?'📚':($s['cover_emoji']==='map'?'🗺️':($s['cover_emoji']==='castle'?'🏰':'✨')); ?></span>
                <span class="badgex d-block mt-2">SEASON <?= (int)$s['sort_order']; ?></span>
                <h2><?= html_escape($s['title']); ?></h2>
                <p><?= html_escape($s['description']); ?></p>
                <div class="meta"><span><?= $is_world?'Interactive world':(int)$s['episode_count'].' chapters'; ?></span><span><?= $is_world?'Explore now':(int)$s['completed_count'].' completed'; ?> <i class="ti ti-arrow-right"></i></span></div>
            </a>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
