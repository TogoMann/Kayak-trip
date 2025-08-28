<?php
require_once __DIR__.'/_guard.php';
$active='support';
$page_title='Support';
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT t.*,u.email,COALESCE(u.nom_affichage,TRIM(CONCAT(u.prenom,' ',u.nom))) AS uname FROM support_thread t LEFT JOIN utilisateur u ON u.id=t.user_id WHERE t.id=?");
$stmt->execute([$id]); $t=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$t){ header('Location: /admin/support.php'); exit; }
require_once __DIR__.'/_layout_start.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="text-white fw-semibold">Fil #<?php echo (int)$t['id']; ?> • <?php echo htmlspecialchars(ucfirst($t['category'])); ?> • <?php echo $t['status']==='open'?'Ouvert':'Fermé'; ?></div>
  <div class="d-flex gap-2">
    <form action="/admin/support_actions.php" method="post">
      <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
      <input type="hidden" name="action" value="<?php echo $t['status']==='open'?'close':'open'; ?>">
      <button class="btn btn-<?php echo $t['status']==='open'?'warning':'success'; ?>"><?php echo $t['status']==='open'?'Fermer':'Réouvrir'; ?></button>
    </form>
    <a href="/admin/support.php" class="btn btn-outline-light">Retour</a>
  </div>
</div>

<div class="card p-3 mb-3">
  <div class="row g-2">
    <div class="col-12 col-md-4">
      <div class="text-secondary">Client</div>
      <div class="fw-semibold"><?php echo htmlspecialchars($t['uname']?:($t['name']?:'Visiteur')); ?></div>
      <div class="small"><?php echo htmlspecialchars($t['email']?:''); ?></div>
    </div>
    <div class="col-12 col-md-4">
      <div class="text-secondary">Créé le</div>
      <div><?php echo date('d/m/Y H:i',strtotime($t['created_at'])); ?></div>
    </div>
    <div class="col-12 col-md-4">
      <div class="text-secondary">Catégorie</div>
      <div><?php echo htmlspecialchars(ucfirst($t['category'])); ?></div>
    </div>
  </div>
</div>

<div class="card p-0">
  <div id="supBody" style="height:60vh;overflow:auto" class="p-3"></div>
  <form id="supForm" class="p-2 border-top">
    <div class="input-group">
      <input type="text" id="supInput" class="form-control" placeholder="Votre message" <?php echo $t['status']==='open'?'':'disabled'; ?>>
      <button class="btn btn-primary" <?php echo $t['status']==='open'?'':'disabled'; ?>>Envoyer</button>
    </div>
  </form>
</div>

<script>
let lastId=0, busy=false;
function rowHtml(m){
  const mine=m.sender_role==='admin';
  const t=new Date(m.created_at.replace(' ','T'));
  const hh=String(t.getHours()).padStart(2,'0'), mm=String(t.getMinutes()).padStart(2,'0');
  return `<div class="d-flex ${mine?'justify-content-end':''} mb-2">
    <div class="p-2 rounded ${mine?'bg-primary text-white':'bg-dark text-white-50'}" style="max-width:70%">
      <div class="small ${mine?'text-white-50':'text-secondary'}">${mine?'Moi':(m.sender_name||'Client')} • ${hh}:${mm}</div>
      <div>${m.body_html}</div>
    </div>
  </div>`;
}
function scrollBottom(){ const el=document.getElementById('supBody'); el.scrollTop=el.scrollHeight; }
async function poll(){
  if(busy) return; busy=true;
  try{
    const r=await fetch('/admin/support_poll.php?thread_id=<?php echo (int)$t['id']; ?>&since_id='+lastId,{headers:{'Accept':'application/json'}});
    if(r.ok){
      const data=await r.json();
      if(Array.isArray(data)&&data.length){
        const body=document.getElementById('supBody');
        data.forEach(m=>{ body.insertAdjacentHTML('beforeend',rowHtml(m)); lastId=Math.max(lastId,m.id); });
        scrollBottom();
      }
    }
  }catch(e){}
  busy=false;
}
async function send(e){
  e.preventDefault();
  const i=document.getElementById('supInput');
  const text=i.value.trim();
  if(!text) return;
  const fd=new FormData();
  fd.set('thread_id','<?php echo (int)$t['id']; ?>');
  fd.set('body',text);
  const r=await fetch('/admin/support_send.php',{method:'POST',body:fd});
  if(r.ok){ i.value=''; await poll(); }
}
document.getElementById('supForm').addEventListener('submit',send);
setInterval(poll,3000);
poll();
</script>
<?php require_once __DIR__.'/_layout_end.php'; ?>
