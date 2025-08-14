<?php
session_start();
require_once('../includes/header.php');
?>
<div class="container-fluid">
  <div class="row" style="height: 100vh;">
    <div class="col-md-8 p-0">
      <div id="map" style="height: 100%; width: 100%;"></div>
    </div>
    <div class="col-md-4 bg-light p-4 overflow-auto" style="height: 100%;">
      <h4 class="mb-3">Composer mon itinéraire</h4>
      <div class="d-flex gap-2 mb-3">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-trier">Trier automatiquement</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-inverser">Inverser l’ordre</button>
      </div>
      <div class="mb-2">
        <span class="badge bg-primary me-2">Distance totale: <span id="distance-total">0</span> km</span>
        <span class="badge bg-dark">Durée minimale: <span id="duree-min">1</span> j</span>
      </div>
      <hr>
      <div id="etapes-selectionnees" class="mb-3">
        <h5>Étapes choisies</h5>
        <ul class="list-group" id="liste-etapes"></ul>
      </div>
      <form action="../process/traitement_composer.php" method="POST">
        <input type="hidden" name="etapes" id="etapes-input">
        <div class="mb-3">
          <label class="form-label">Date de départ</label>
          <input type="date" class="form-control" name="date_depart" id="date-depart" min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Date de fin</label>
          <input type="date" class="form-control" name="date_fin" id="date-fin" min="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="mb-3">
          <label for="participants" class="form-label">Nombre de participants</label>
          <input type="number" class="form-control" name="participants" id="participants" min="1" value="1" required>
        </div>
        <div class="mb-3">
          <label for="duree" class="form-label">Durée du séjour (jours)</label>
          <input type="number" class="form-control" name="duree" id="duree" min="1" value="1" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Options supplémentaires</label>
          <div class="form-check">
            <input class="form-check-input service-opt" type="checkbox" name="options[]" value="transport_bagages" id="opt1" data-price="5">
            <label class="form-check-label" for="opt1">Transport de bagages (5 €/étape)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input service-opt" type="checkbox" name="options[]" value="panier_garni" id="opt2" data-price="20">
            <label class="form-check-label" for="opt2">Pack 3 repas/jour (20 €/jour)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input service-opt" type="checkbox" name="options[]" value="location_materiel" id="opt3" data-price="10">
            <label class="form-check-label" for="opt3">Location matériel (10 €/jour)</label>
          </div>
        </div>
        <div class="bg-white border rounded p-3 mb-3">
          <div class="d-flex justify-content-between"><span>Sous-total services</span><strong><span id="total-services">0</span> €</strong></div>
          <div class="d-flex justify-content-between"><span>Total estimé</span><strong><span id="total-general">0</span> €</strong></div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Valider mon itinéraire</button>
      </form>
    </div>
  </div>
</div>
<?php include('../includes/footer.php'); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
const map = L.map('map').setView([47.3, 0.7], 8);
map.setMaxBounds([[43.8,-3.0],[49.5,5.0]]);
L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png',{attribution:'&copy; OpenStreetMap – Leaflet',minZoom:6,maxZoom:18}).addTo(map);

let loireLayer=null;
let loireCoords=[];
fetch('../data/loire.json').then(r=>r.json()).then(g=>{
  loireLayer=L.geoJSON(g,{style:{color:'blue',weight:4,opacity:0.8}}).addTo(map);
  const feats=g.type==='FeatureCollection'?g.features:[{geometry:g}];
  const parts=[];
  feats.forEach(f=>{
    const geom=f.geometry||f;
    if(!geom) return;
    if(geom.type==='LineString'){
      parts.push(geom.coordinates);
    }else if(geom.type==='MultiLineString'){
      geom.coordinates.forEach(c=>parts.push(c));
    }
  });
  parts.sort((a,b)=>a[0][0]-b[0][0]);
  loireCoords = parts.flat().map(c=>[c[1],c[0]]);
});

const etapes=[], etapesNoms=[], pointsIndex={}, markersById={};
let routeLine=null;

function ajouterEtape(id, nom){
  id=Number(id);
  if(!etapes.includes(id)){
    etapes.push(id);
    etapesNoms.push({id,nom});
    mettreAJourListe();
    updateRouteAndStats();
  }
}
function retirerEtape(id){
  id=Number(id);
  const i=etapes.indexOf(id);
  if(i!==-1){
    etapes.splice(i,1);
    etapesNoms.splice(i,1);
    mettreAJourListe();
    updateRouteAndStats();
  }
}
function mettreAJourListe(){
  const ul=document.getElementById('liste-etapes');
  ul.innerHTML='';
  etapesNoms.forEach(p=>{
    const li=document.createElement('li');
    li.className='list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML=`${p.nom}<button class="btn btn-sm btn-outline-danger" onclick="retirerEtape(${p.id})">Retirer</button>`;
    ul.appendChild(li);
  });
  document.getElementById('etapes-input').value=JSON.stringify(etapes);
}
function trierEtapesAuto(){
  if(etapes.length<2) return;
  const arr=etapes.map(id=>({id,idx:nearestIndex(pointsIndex[id].latitude,pointsIndex[id].longitude)}));
  arr.sort((a,b)=>a.idx-b.idx);
  const noms=new Map(etapesNoms.map(o=>[o.id,o.nom]));
  etapes.length=0; etapesNoms.length=0;
  arr.forEach(o=>{etapes.push(o.id); etapesNoms.push({id:o.id,nom:noms.get(o.id)});});
  mettreAJourListe();
  updateRouteAndStats();
}
function inverserEtapes(){
  etapes.reverse(); etapesNoms.reverse();
  mettreAJourListe();
  updateRouteAndStats();
}
function haversine(lat1,lon1,lat2,lon2){
  const R=6371,toRad=Math.PI/180;
  const dLat=(lat2-lat1)*toRad,dLon=(lon2-lon1)*toRad;
  const a=Math.sin(dLat/2)**2+Math.cos(lat1*toRad)*Math.cos(lat2*toRad)*Math.sin(dLon/2)**2;
  return 2*R*Math.asin(Math.sqrt(a));
}
function nearestIndex(lat,lon){
  if(!loireCoords.length) return 0;
  let best=0,bd=Infinity;
  for(let i=0;i<loireCoords.length;i++){
    const p=loireCoords[i];
    const d=haversine(lat,lon,p[0],p[1]);
    if(d<bd){bd=d;best=i;}
  }
  return best;
}
function sliceAlongLoire(i1,i2){
  if(i1<=i2) return loireCoords.slice(i1,i2+1);
  const a=loireCoords.slice(i2,i1+1).reverse();
  return a;
}
function buildRouteCoords(){
  if(etapes.length<2||!loireCoords.length) return [];
  const segs=[];
  for(let i=0;i<etapes.length-1;i++){
    const a=pointsIndex[etapes[i]], b=pointsIndex[etapes[i+1]];
    const ia=nearestIndex(Number(a.latitude),Number(a.longitude));
    const ib=nearestIndex(Number(b.latitude),Number(b.longitude));
    const part=sliceAlongLoire(ia,ib);
    if(part.length) segs.push(...part);
  }
  return segs;
}
function routeDistanceKm(route){
  if(route.length<2) return 0;
  let d=0;
  for(let i=0;i<route.length-1;i++){
    const p=route[i], q=route[i+1];
    d+=haversine(p[0],p[1],q[0],q[1]);
  }
  return Math.round(d);
}
function updateRouteAndStats(){
  if(routeLine){map.removeLayer(routeLine);routeLine=null;}
  const route=buildRouteCoords();
  if(route.length>=2){
    routeLine=L.polyline(route,{color:'red',weight:4,opacity:0.9}).addTo(map);
  }
  const dist=routeDistanceKm(route);
  document.getElementById('distance-total').textContent=dist;
  const daily=30;
  const minDays=Math.max(1,Math.ceil(dist/daily));
  document.getElementById('duree-min').textContent=minDays;
  const dureeInput=document.getElementById('duree');
  dureeInput.min=minDays;
  if(Number(dureeInput.value)<minDays) dureeInput.value=minDays;
  updateDateFinMin();
  updateTotals();
}
function updateDateFinMin(){
  const depart=document.getElementById('date-depart').value;
  const duree=Number(document.getElementById('duree').value||1);
  if(!depart) return;
  const start=new Date(depart);
  const endMin=new Date(start);
  endMin.setDate(endMin.getDate()+duree);
  const y=endMin.getFullYear(),m=String(endMin.getMonth()+1).padStart(2,'0'),d=String(endMin.getDate()).padStart(2,'0');
  const minStr=`${y}-${m}-${d}`;
  const endInput=document.getElementById('date-fin');
  endInput.min=minStr;
  if(!endInput.value||endInput.value<minStr) endInput.value=minStr;
}
function updateTotals(){
  const participants=Number(document.getElementById('participants').value||1);
  const duree=Number(document.getElementById('duree').value||1);
  const stepsCount=etapes.length;
  let services=0;
  document.querySelectorAll('.service-opt:checked').forEach(cb=>{
    const price=Number(cb.getAttribute('data-price')||0);
    if(cb.value==='transport_bagages') services+=price*Math.max(0,stepsCount-1);
    else services+=price*duree*participants;
  });
  document.getElementById('total-services').textContent=services.toFixed(0);
  document.getElementById('total-general').textContent=services.toFixed(0);
}

document.getElementById('btn-trier').addEventListener('click',trierEtapesAuto);
document.getElementById('btn-inverser').addEventListener('click',inverserEtapes);
document.getElementById('duree').addEventListener('input',()=>{updateDateFinMin();updateTotals();});
document.getElementById('participants').addEventListener('input',updateTotals);
document.querySelectorAll('.service-opt').forEach(cb=>cb.addEventListener('change',updateTotals));
document.getElementById('date-depart').addEventListener('change',updateDateFinMin);

fetch('../api/get_points.php').then(r=>r.json()).then(data=>{
  data.forEach(point=>{
    pointsIndex[point.id]=point;
    const marker=L.marker([point.latitude,point.longitude]).addTo(map);
    markersById[point.id]=marker;
    marker.bindPopup(`
      <strong>${point.nom}</strong><br>
      <button class="btn btn-sm btn-primary mt-2" onclick="ajouterEtape(${point.id}, '${String(point.nom).replace(/'/g,"\\'")}')">Ajouter à l’itinéraire</button>
    `);
  });
  const pre=new URLSearchParams(window.location.search).get('add_point');
  if(pre&&pointsIndex[pre]){
    const p=pointsIndex[pre];
    ajouterEtape(Number(pre),p.nom);
    map.setView([p.latitude,p.longitude],10);
    if(markersById[pre]) markersById[pre].openPopup();
  }
});
</script>

