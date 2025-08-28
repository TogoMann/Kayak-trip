<?php
require_once __DIR__.'/_guard.php';
$active='chat';
$page_title='Chat';
$me=(int)$_SESSION['user_id'];
$channel=($_GET['channel']??'general')==='general'?'general':null;
$to=isset($_GET['to'])?(int)$_GET['to']:0;
if($to===$me){ $to=0; }
$admins=$pdo->query("SELECT id,COALESCE(nom_affichage,TRIM(CONCAT(prenom,' ',nom))) AS nom,email FROM utilisateur WHERE role='admin' ORDER BY COALESCE(nom_affichage,TRIM(CONCAT(prenom,' ',nom)),email) ASC")->fetchAll(PDO::FETCH_ASSOC);
require_once __DIR__.'/_layout_start.php';
?>
<div class="row g-3">
  <div class="col-12 col-lg-3">
    <div class="card p-2">
      <div class="list-group">
        <a class="list-group-item list-group-item-action <?php echo $channel==='general'?'active':''; ?>" href="/admin/chat.php?channel=general">Salon général</a>
        <div class="mt-2 px-2 text-secondary small">Admins</div>
        <?php foreach($admins as $a): ?>
          <a class="list-group-item list-group-item-action <?php echo ($to===(int)$a['id']&&$channel===null)?'active':''; ?>" href="/admin/chat.php?to=<?php echo (int)$a['id']; ?>"><?php echo htmlspecialchars($a['nom']?:$a['email']); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <div class="col-12 col-lg-9">
    <div class="card p-0">
      <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
        <div class="fw-semibold">
          <?php
            if($channel==='general'){ echo 'Salon général'; }
            else {
              $cur=array_values(array_filter($admins,function($x) use($to){ return (int)$x['id']===$to; }));
              echo $cur?htmlspecialchars(($cur[0]['nom']?:$cur[0]['email'])):'Sélection';
            }
          ?>
        </div>
      </div>
      <div id="chatBody" style="height:60vh;overflow:auto" class="p-3"></div>
      <form id="chatForm" class="p-2 border-top">
        <div class="input-group">
          <input type="text" id="chatInput" class="form-control" placeholder="Écrire un message" autocomplete="off" required>
          <button class="btn btn-primary" id="sendBtn">Envoyer</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
let lastId=0, busy=false;
function rowHtml(m){
  const mine=m.sender_id===<?php echo $me; ?>;
  const who=mine?'Moi':m.sender_name;
  const t=new Date(m.created_at.replace(' ','T'));
  const hh=String(t.getHours()).padStart(2,'0'), mm=String(t.getMinutes()).padStart(2,'0');
  return `<div class="d-flex ${mine?'justify-content-end':''} mb-2">
    <div class="p-2 rounded ${mine?'bg-primary text-white':'bg-dark text-white-50'}" style="max-width:70%">
      <div class="small ${mine?'text-white-50':'text-secondary'}">${who} • ${hh}:${mm}</div>
      <div>${m.body_html}</div>
    </div>
  </div>`;
}
function scrollBottom(){ const el=document.getElementById('chatBody'); el.scrollTop=el.scrollHeight; }
async function poll(){
  if(busy) return;
  busy=true;
  try{
    const params=new URLSearchParams();
    params.set('since_id',String(lastId));
    <?php if($channel==='general'): ?>
      params.set('channel','general');
    <?php else: ?>
      params.set('to','<?php echo (int)$to; ?>');
    <?php endif; ?>
    const r=await fetch('/admin/chat_poll.php?'+params.toString(),{headers:{'Accept':'application/json'}});
    if(r.ok){
      const data=await r.json();
      if(Array.isArray(data)&&data.length){
        const body=document.getElementById('chatBody');
        data.forEach(m=>{ body.insertAdjacentHTML('beforeend',rowHtml(m)); lastId=Math.max(lastId, m.id); });
        scrollBottom();
      }
    }
  }catch(e){}
  finally{ busy=false; }
}
async function sendMessage(){
  const input=document.getElementById('chatInput');
  const text=input.value.trim();
  if(!text) return;
  const fd=new FormData();
  fd.set('body',text);
  <?php if($channel==='general'): ?>
    fd.set('channel','general');
  <?php else: ?>
    fd.set('to','<?php echo (int)$to; ?>');
  <?php endif; ?>
  const r=await fetch('/admin/chat_send.php',{method:'POST',body:fd});
  if(r.ok){ input.value=''; poll(); }
}
document.getElementById('chatForm').addEventListener('submit',function(e){ e.preventDefault(); sendMessage(); });
setInterval(poll,3000);
poll();
</script>
<?php require_once __DIR__.'/_layout_end.php'; ?>
