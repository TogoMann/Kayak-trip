<?php
session_start();
require_once('../includes/header.php');
$cats = $pdo->query("SELECT id,nom,description FROM service_categorie WHERE actif=1 ORDER BY ordre ASC, nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$servicesByCat = [];
if ($cats) {
    $ids = array_column($cats,'id');
    $in = implode(',', array_fill(0,count($ids),'?'));
    $stm = $pdo->prepare("SELECT id,nom,description,prix,categorie_id FROM service WHERE actif=1 AND categorie_id IN ($in) ORDER BY nom ASC");
    $stm->execute($ids);
    while ($s = $stm->fetch(PDO::FETCH_ASSOC)) { $servicesByCat[$s['categorie_id']][] = $s; }
}
$orphans = $pdo->query("SELECT id,nom,description,prix FROM service WHERE actif=1 AND (categorie_id IS NULL OR categorie_id=0) ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="text-white text-center" style="background: url('../img/horo.jpg') center/cover no-repeat; min-height: 50vh; display: flex; align-items: center; justify-content: center;">
    <div style="background-color: rgba(0, 0, 0, 0.4); padding: 20px; border-radius: 8px;">
        <h1 class="display-4 fw-bold">Services complémentaires</h1>
    </div>
</div>

<div class="container py-5">
    <div class="row gy-4">
        <?php foreach ($cats as $c): $list = $servicesByCat[$c['id']] ?? []; if (!$list) continue; ?>
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="card-title text-primary"><?php echo htmlspecialchars($c['nom']); ?></h4>
                    <p class="card-text"><?php echo htmlspecialchars($c['description'] ?? ''); ?></p>
                    <ul>
                        <?php foreach ($list as $s):
                            $unit = $s['description'] ? ' / '.mb_strtolower($s['description'],'UTF-8') : '';
                            $price = rtrim(rtrim(number_format((float)$s['prix'],2,',',' '),'0'),',');
                        ?>
                        <li><?php echo htmlspecialchars($s['nom']); ?> : <?php echo $price; ?> €<?php echo $unit; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if ($orphans): ?>
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="card-title text-primary">Autres services</h4>
                    <p class="card-text"></p>
                    <ul>
                        <?php foreach ($orphans as $s):
                            $unit = $s['description'] ? ' / '.mb_strtolower($s['description'],'UTF-8') : '';
                            $price = rtrim(rtrim(number_format((float)$s['prix'],2,',',' '),'0'),',');
                        ?>
                        <li><?php echo htmlspecialchars($s['nom']); ?> : <?php echo $price; ?> €<?php echo $unit; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
