<?php
require_once __DIR__ . '/_guard.php';
$q = $_GET['q'] ?? '';
$imported = isset($_GET['imported']) ? (int)$_GET['imported'] : null;
$skipped = isset($_GET['skipped']) ? (int)$_GET['skipped'] : null;
$params = [];
$sql = "SELECT p.id,p.nom,p.description,p.latitude,p.longitude,COUNT(h.id) AS nb_hebergements FROM point_arret p LEFT JOIN hebergement h ON h.point_arret_id=p.id";
if ($q !== '') { $sql .= " WHERE p.nom LIKE ? OR p.description LIKE ?"; $like = "%$q%"; $params = [$like,$like]; }
$sql .= " GROUP BY p.id ORDER BY p.nom ASC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$page_title = 'Points d’arrêt';
$active = 'points';
require_once __DIR__ . '/_layout_start.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
.mapbox{height:280px;border-radius:.5rem;overflow:hidden}
.mapfull{height:500px;border-radius:.5rem;overflow:hidden}
.suggest{position:absolute;z-index:1100;width:100%;background:#fff;border:1px solid #ddd;border-top:0;max-height:220px;overflow:auto}
.suggest-item{padding:.5rem .75rem;cursor:pointer}
.suggest-item:hover{background:#f2f2f2}
</style>

<?php if (isset($_GET['ok'])): ?><div class="alert alert-success">Opération effectuée.</div><?php endif; ?>
<?php if (isset($_GET['err']) && $_GET['err']==='constraint'): ?><div class="alert alert-warning">Impossible de supprimer ce point d’arrêt car il est référencé.</div><?php endif; ?>
<?php if (!is_null($imported)): ?><div class="alert alert-info">Import terminé: <?php echo $imported; ?> ajouté(s)<?php echo (!is_null($skipped) ? ", $skipped ignoré(s)" : ""); ?>.</div><?php endif; ?>

<div class="card p-2 mb-3"><div id="adminMap" class="mapfull"></div></div>

<div class="card p-3 mb-3">
  <form class="row g-2 align-items-center" method="get">
    <div class="col-12 col-md-6">
      <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control" placeholder="Rechercher par nom ou description">
    </div>
    <div class="col-6 col-md-auto"><button class="btn btn-primary"><i class="bi bi-search me-1"></i>Rechercher</button></div>
    <div class="col-6 col-md-auto ms-auto d-flex gap-2 justify-content-end">
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="bi bi-plus-lg me-1"></i>Ajouter un point</button>
      <button type="button" class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-filetype-csv me-1"></i>Importer CSV</button>
    </div>
  </form>
</div>

<div class="card p-0">
  <div class="table-responsive">
    <table class="table table-dark table-hover align-middle mb-0">
      <thead>
        <tr>
          <th style="width:70px">ID</th>
          <th>Nom</th>
          <th>Description</th>
          <th style="width:140px">Latitude</th>
          <th style="width:140px">Longitude</th>
          <th style="width:150px">Hébergements</th>
          <th style="width:220px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r): ?>
        <tr>
          <td><?php echo (int)$r['id']; ?></td>
          <td><?php echo htmlspecialchars($r['nom']); ?></td>
          <td class="text-truncate" style="max-width:380px"><?php echo htmlspecialchars($r['description']??''); ?></td>
          <td><?php echo htmlspecialchars((string)$r['latitude']); ?></td>
          <td><?php echo htmlspecialchars((string)$r['longitude']); ?></td>
          <td><span class="badge text-bg-secondary"><?php echo (int)$r['nb_hebergements']; ?></span></td>
          <td class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm btn-edit"
              data-id="<?php echo (int)$r['id']; ?>"
              data-nom="<?php echo htmlspecialchars($r['nom']); ?>"
              data-description="<?php echo htmlspecialchars($r['description']??''); ?>"
              data-lat="<?php echo htmlspecialchars((string)$r['latitude']); ?>"
              data-lng="<?php echo htmlspecialchars((string)$r['longitude']); ?>"
              data-bs-toggle="modal" data-bs-target="#editModal"><i class="bi bi-pencil-square me-1"></i>Modifier</button>
            <form action="/admin/points_actions.php" method="post" onsubmit="return confirm('Supprimer ce point d’arrêt ?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
              <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>Supprimer</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(count($rows)===0): ?>
        <tr><td colspan="7" class="text-center text-secondary py-4">Aucun point d’arrêt</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="/admin/points_actions.php" method="post">
        <input type="hidden" name="action" value="create">
        <div class="modal-header"><h5 class="modal-title">Ajouter un point d’arrêt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"></textarea></div>
          <div class="row g-2">
            <div class="col-6"><label class="form-label">Latitude</label><input type="number" step="0.00001" name="latitude" id="clat" class="form-control" required></div>
            <div class="col-6"><label class="form-label">Longitude</label><input type="number" step="0.00001" name="longitude" id="clng" class="form-control" required></div>
          </div>
          <div class="mt-3 position-relative">
            <div class="input-group">
              <input type="text" id="csearch" class="form-control" placeholder="Rechercher une adresse ou un lieu">
              <button class="btn btn-outline-secondary" type="button" id="csearchBtn"><i class="bi bi-search"></i></button>
            </div>
            <div id="csuggest" class="suggest d-none"></div>
          </div>
          <div class="mt-3 mapbox"><div id="createMap" style="height:100%"></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="/admin/points_actions.php" method="post">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="eid">
        <div class="modal-header"><h5 class="modal-title">Modifier le point d’arrêt</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nom</label><input type="text" name="nom" id="enom" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Description</label><textarea name="description" id="edesc" class="form-control" rows="4"></textarea></div>
          <div class="row g-2">
            <div class="col-6"><label class="form-label">Latitude</label><input type="number" step="0.00001" name="latitude" id="elat" class="form-control" required></div>
            <div class="col-6"><label class="form-label">Longitude</label><input type="number" step="0.00001" name="longitude" id="elng" class="form-control" required></div>
          </div>
          <div class="mt-3 position-relative">
            <div class="input-group">
              <input type="text" id="esearch" class="form-control" placeholder="Rechercher une adresse ou un lieu">
              <button class="btn btn-outline-secondary" type="button" id="esearchBtn"><i class="bi bi-search"></i></button>
            </div>
            <div id="esuggest" class="suggest d-none"></div>
          </div>
          <div class="mt-3 mapbox"><div id="editMap" style="height:100%"></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Enregistrer</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="/admin/points_import.php" method="post" enctype="multipart/form-data">
        <div class="modal-header"><h5 class="modal-title">Importer des points (CSV)</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><input type="file" name="csv" accept=".csv,text/csv" class="form-control" required></div>
          <div class="text-secondary small">Colonnes requises: nom;description;latitude;longitude. Exemple: <a href="/admin/points.php#" onclick="window.open('sandbox:/mnt/data/points_import_sample.csv','_blank');return false;">télécharger</a></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary">Importer</button></div>
      </form>
    </div>
  </div>
</div>

<script>
var POINTS_DATA = <?php echo json_encode(array_map(function($r){return ['id'=>(int)$r['id'],'nom'=>$r['nom'],'description'=>$r['description'],'latitude'=>(float)$r['latitude'],'longitude'=>(float)$r['longitude']];},$rows),JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let adminMap,loireData=null,loireLayerMain=null,markers=[];
function initAdminMap(){if(adminMap)return;adminMap=L.map('adminMap');L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(adminMap);adminMap.setView([47.5,0.6],7);adminMap.on('click',e=>{new bootstrap.Modal(document.getElementById('createModal')).show();setTimeout(()=>{initCreateMap();setCreatePoint(e.latlng.lat,e.latlng.lng)},150)});loadLoire().then(()=>{drawLoireOn(adminMap)});renderMarkers()}
function renderMarkers(){markers.forEach(m=>adminMap.removeLayer(m));markers=[];POINTS_DATA.forEach(p=>{const m=L.marker([p.latitude,p.longitude]).addTo(adminMap);m.bindPopup(`<strong>${p.nom}</strong><br><button class="btn btn-sm btn-primary mt-2" data-id="${p.id}" data-nom="${p.nom.replace(/"/g,'&quot;')}" data-desc="${(p.description||'').replace(/"/g,'&quot;')}" data-lat="${p.latitude}" data-lng="${p.longitude}" onclick="openEditFromMap(this)">Modifier</button>`);markers.push(m)})}
function openEditFromMap(btn){document.getElementById('eid').value=btn.dataset.id;document.getElementById('enom').value=btn.dataset.nom;document.getElementById('edesc').value=btn.dataset.desc;document.getElementById('elat').value=btn.dataset.lat;document.getElementById('elng').value=btn.dataset.lng;new bootstrap.Modal(document.getElementById('editModal')).show();setTimeout(()=>{initEditMap();setEditPoint(parseFloat(btn.dataset.lat),parseFloat(btn.dataset.lng))},150)}
async function loadLoire(){if(loireData)return loireData;const r=await fetch('../data/loire.json');if(!r.ok)return null;loireData=await r.json();return loireData}
function drawLoireOn(map){if(!loireData)return;const layer=L.geoJSON(loireData,{style:{color:'blue',weight:4,opacity:.8}}).addTo(map);if(loireLayerMain){try{map.removeLayer(loireLayerMain)}catch(e){}}loireLayerMain=layer}
let createMap,createMarker,editMap,editMarker,loireLayerCreate=null,loireLayerEdit=null;
function initCreateMap(){if(createMap)return;createMap=L.map('createMap');L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(createMap);createMap.setView([47.5,0.6],7);createMap.on('click',e=>{setCreatePoint(e.latlng.lat,e.latlng.lng)});loadLoire().then(()=>{const layer=L.geoJSON(loireData,{style:{color:'blue',weight:4,opacity:.8}}).addTo(createMap);loireLayerCreate=layer})}
function initEditMap(){if(editMap)return;editMap=L.map('editMap');L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19}).addTo(editMap);editMap.setView([47.5,0.6],7);editMap.on('click',e=>{setEditPoint(e.latlng.lat,e.latlng.lng)});loadLoire().then(()=>{const layer=L.geoJSON(loireData,{style:{color:'blue',weight:4,opacity:.8}}).addTo(editMap);loireLayerEdit=layer})}
function setCreatePoint(lat,lng){document.getElementById('clat').value=lat.toFixed(5);document.getElementById('clng').value=lng.toFixed(5);if(!createMarker){createMarker=L.marker([lat,lng]).addTo(createMap)}else{createMarker.setLatLng([lat,lng])}createMap.setView([lat,lng],14)}
function setEditPoint(lat,lng){document.getElementById('elat').value=lat.toFixed(5);document.getElementById('elng').value=lng.toFixed(5);if(!editMarker){editMarker=L.marker([lat,lng]).addTo(editMap)}else{editMarker.setLatLng([lat,lng])}editMap.setView([lat,lng],14)}
function debouncer(fn,delay){let t;return(...a)=>{clearTimeout(t);t=setTimeout(()=>fn(...a),delay)}}
async function geocode(q){if(!q||q.trim().length<3)return[];const u=`https://nominatim.openstreetmap.org/search?format=json&limit=8&countrycodes=fr&q=${encodeURIComponent(q)}&addressdetails=1&email=admin@kayak.com`;const r=await fetch(u,{headers:{"Accept":"application/json"}});if(!r.ok)return[];return await r.json()}
function bindSearch(inputId,btnId,suggestId,onPick){const i=document.getElementById(inputId);const b=document.getElementById(btnId);const box=document.getElementById(suggestId);const render=items=>{if(!items.length){box.classList.add('d-none');box.innerHTML='';return}box.innerHTML=items.map(x=>`<div class="suggest-item" data-lat="${x.lat}" data-lon="${x.lon}">${x.display_name}</div>`).join('');box.classList.remove('d-none');Array.from(box.querySelectorAll('.suggest-item')).forEach(el=>{el.addEventListener('click',()=>{onPick(parseFloat(el.dataset.lat),parseFloat(el.dataset.lon));box.classList.add('d-none')})})};const run=debouncer(async()=>{const items=await geocode(i.value);render(items)},300);i.addEventListener('input',run);b.addEventListener('click',()=>run());document.addEventListener('click',e=>{if(!box.contains(e.target)&&e.target!==i)box.classList.add('d-none')})}
const createModal=document.getElementById('createModal');createModal.addEventListener('shown.bs.modal',()=>{initCreateMap();setTimeout(()=>{createMap.invalidateSize()},50)});
bindSearch('csearch','csearchBtn','csuggest',(lat,lon)=>{setCreatePoint(lat,lon)});
const editModal=document.getElementById('editModal');editModal.addEventListener('shown.bs.modal',()=>{initEditMap();const lat=parseFloat(document.getElementById('elat').value||'47.5');const lng=parseFloat(document.getElementById('elng').value||'0.6');setEditPoint(lat,lng);setTimeout(()=>{editMap.invalidateSize()},50)});
bindSearch('esearch','esearchBtn','esuggest',(lat,lon)=>{setEditPoint(lat,lon)});
document.querySelectorAll('.btn-edit').forEach(b=>{b.addEventListener('click',function(){document.getElementById('eid').value=this.dataset.id;document.getElementById('enom').value=this.dataset.nom;document.getElementById('edesc').value=this.dataset.description;document.getElementById('elat').value=this.dataset.lat;document.getElementById('elng').value=this.dataset.lng;})});
document.addEventListener('DOMContentLoaded',initAdminMap);
</script>
<?php require_once __DIR__ . '/_layout_end.php'; ?>
