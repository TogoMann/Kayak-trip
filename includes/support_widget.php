<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
?>
<style>
#kb-chat-bubble{position:fixed;right:18px;bottom:18px;width:56px;height:56px;border-radius:50%;background:#0d6efd;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 8px 24px rgba(0,0,0,.25);z-index:9999}
#kb-chat-panel{position:fixed;right:18px;bottom:86px;width:340px;max-width:90vw;background:#111827;color:#e5e7eb;border-radius:12px;box-shadow:0 16px 40px rgba(0,0,0,.35);display:none;flex-direction:column;overflow:hidden;z-index:9999}
#kb-chat-header{padding:10px 12px;background:#0b1220;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:space-between}
#kb-chat-body{height:360px;overflow:auto;padding:12px;background:#0f172a}
#kb-chat-start{padding:12px}
#kb-chat-form{display:flex;gap:8px;padding:10px;border-top:1px solid rgba(255,255,255,.08);background:#0b1220}
#kb-chat-input{flex:1}
.kb-msg{display:flex;margin-bottom:8px}
.kb-me{justify-content:flex-end}
.kb-bubble{padding:8px 10px;border-radius:10px;max-width:72%}
.kb-bubble.me{background:#0d6efd;color:#fff}
.kb-bubble.other{background:#1f2937;color:#e5e7eb}
.kb-meta{font-size:12px;opacity:.7;margin-bottom:4px}
</style>
<div id="kb-chat-bubble" title="Besoin d'aide?">
  <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 4.906 14.32c.196.196.322.305.51.443.436.327.947.586 1.584.736a.5.5 0 0 0 .6-.6c-.15-.637-.41-1.148-.736-1.584-.138-.188-.247-.314-.443-.51A8 8 0 0 0 8 0"/></svg>
</div>
<div id="kb-chat-panel">
  <div id="kb-chat-header">
    <div>Assistance</div>
    <button id="kb-close" class="btn btn-sm btn-outline-light">Fermer</button>
  </div>
  <div id="kb-chat-start">
    <div class="mb-2">Quel est le sujet de votre demande ?</div>
    <select id="kb-cat" class="form-select mb-2">
      <option value="technique">Problème technique</option>
      <option value="commercial">Question commerciale</option>
      <option value="autre">Autre</option>
    </select>
    <?php if(!$uid): ?>
      <input type="text" id="kb-name" class="form-control mb-2" placeholder="Votre nom">
      <input type="email" id="kb-email" class="form-control mb-2" placeholder="Votre e-mail">
    <?php endif; ?>
    <textarea id="kb-first" class="form-control mb-2" rows="2" placeholder="Décrivez votre problème"></textarea>
    <button id="kb-start-btn" class="btn btn-primary w-100">Commencer</button>
  </div>
  <div id="kb-chat-body" class="d-none"></div>
  <form id="kb-chat-form" class="d-none">
    <input type="text" id="kb-chat-input" class="form-control" placeholder="Écrire un message">
    <button class="btn btn-primary">Envoyer</button>
  </form>
</div>
<script>
(function(){
  let opened=false, threadId=null, lastId=0, polling=null, busy=false;
  const bubble=document.getElementById('kb-chat-bubble');
  const panel=document.getElementById('kb-chat-panel');
  const startBox=document.getElementById('kb-chat-start');
  const chatBody=document.getElementById('kb-chat-body');
  const chatForm=document.getElementById('kb-chat-form');
  const input=document.getElementById('kb-chat-input');
  function toggle(){ opened=!opened; panel.style.display=opened?'flex':'none'; if(opened && threadId){ scrollBottom(); if(!polling){ polling=setInterval(poll,3000); } } else { if(polling){ clearInterval(polling); polling=null; } } }
  function row(m){
    const mine=m.sender_role==='user';
    const div=document.createElement('div');
    div.className='kb-msg '+(mine?'kb-me':'');
    const b=document.createElement('div');
    b.className='kb-bubble '+(mine?'me':'other');
    const meta=document.createElement('div');
    meta.className='kb-meta';
    const t=new Date(m.created_at.replace(' ','T'));
    const hh=String(t.getHours()).padStart(2,'0'), mm=String(t.getMinutes()).padStart(2,'0');
    meta.textContent=(mine?'Vous':(m.sender_name||'Support'))+' • '+hh+':'+mm;
    const body=document.createElement('div');
    body.innerHTML=m.body_html;
    b.appendChild(meta); b.appendChild(body); div.appendChild(b);
    return div;
  }
  function scrollBottom(){ chatBody.scrollTop=chatBody.scrollHeight; }
  async function start(){
    const fd=new FormData();
    fd.set('category',document.getElementById('kb-cat').value);
    const first=document.getElementById('kb-first').value.trim();
    if(first!=='') fd.set('message',first);
    const n=document.getElementById('kb-name'); const e=document.getElementById('kb-email');
    if(n) fd.set('name',n.value.trim());
    if(e) fd.set('email',e.value.trim());
    const r=await fetch('/api/support_open.php',{method:'POST',body:fd});
    if(!r.ok) return;
    const data=await r.json();
    threadId=data.id;
    startBox.classList.add('d-none');
    chatBody.classList.remove('d-none');
    chatForm.classList.remove('d-none');
    if(polling){ clearInterval(polling); }
    polling=setInterval(poll,3000);
    await poll();
    scrollBottom();
  }
  async function poll(){
    if(!threadId || busy) return;
    busy=true;
    try{
      const r=await fetch('/api/support_poll.php?thread_id='+threadId+'&since_id='+lastId,{headers:{'Accept':'application/json'}});
      if(r.ok){
        const data=await r.json();
        if(Array.isArray(data)&&data.length){
          data.forEach(m=>{ chatBody.appendChild(row(m)); lastId=Math.max(lastId,m.id); });
          scrollBottom();
        }
      }
    }catch(e){}
    busy=false;
  }
  async function send(e){
    e.preventDefault();
    if(!threadId) return;
    const text=input.value.trim();
    if(text==='') return;
    const fd=new FormData();
    fd.set('thread_id',threadId);
    fd.set('body',text);
    const r=await fetch('/api/support_send.php',{method:'POST',body:fd});
    if(r.ok){
      input.value='';
      await poll();
    }
  }
  document.getElementById('kb-close').addEventListener('click',toggle);
  bubble.addEventListener('click',toggle);
  document.getElementById('kb-start-btn').addEventListener('click',start);
  chatForm.addEventListener('submit',send);
})();
</script>
