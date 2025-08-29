<?php
session_start();
include 'db.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: login.php");
    exit;
}
$admin = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Messenger</title>
<style>
  :root { 
    --accent:#8B0000; /* main red */
    --accent-green:#1b4d1b; /* bamboo green */
    --bg:#f7f7f7; 
    --card:#fff; 
  }
  * { box-sizing: border-box; }
  body { margin:0; font-family: "Segoe UI", Roboto, Arial; height:100vh; display:flex; background:var(--bg); }

  /* Sidebar */
  .sidebar { width:360px; max-width:40%; background:var(--card); border-right:2px solid var(--accent-green); display:flex; flex-direction:column; }
  .sidebar-header { padding:18px; display:flex; align-items:center; gap:12px; border-bottom:2px solid var(--accent); background:var(--accent); color:#fff; }
  .title { font-size:18px; font-weight:700; }
  .search { padding:12px; border:1px solid var(--accent-green); border-radius:20px; background:#f5f7f9; width:100%; outline:none; }
  .search:focus { border-color:var(--accent); }
  .list { flex:1; overflow:auto; padding:8px 0; }
  .list-item { display:flex; align-items:center; gap:12px; padding:10px 14px; cursor:pointer; transition:background .12s; border-bottom:1px solid #eee; }
  .list-item:hover { background:#f3f3f3; }
  .list-item.active { background:#f9ecec; border-left:4px solid var(--accent); }
  .avatar { width:44px; height:44px; border-radius:50%; background:var(--accent-green); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; flex:0 0 44px; overflow:hidden; }
  .meta { flex:1; min-width:0; }
  .meta .name { font-weight:600; font-size:14px; color:#111; display:flex; align-items:center; gap:8px; }
  .meta .snippet { font-size:13px; color:#666; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; max-width:210px; margin-top:4px; }
  .right { text-align:right; min-width:62px; }
  .right .time { display:block; font-size:12px; color:#999; }
  .dot { width:10px; height:10px; border-radius:50%; background:var(--accent-green); display:inline-block; margin-top:6px; }

  /* Chat area */
  .chat { flex:1; display:flex; flex-direction:column; width: 70%; }
  .chat-header { padding:14px 18px; border-bottom:2px solid var(--accent); background:var(--card); display:flex; align-items:center; gap:12px; }
  .chat-header .avatar { width:48px; height:48px; background:var(--accent); }
  .chat-header .title { font-size:16px; font-weight:700; color:var(--accent-green); }
  .messages { flex:1; padding:20px; overflow:auto; background: linear-gradient(#fafafa, #f0f0f0); }
  .bubble {
    display: inline-block;
    padding: 10px 14px;
    border-radius: 18px;
    margin: 8px 0;
    max-width: 40%;       /* limit width to 40% of chat area */
    word-wrap: break-word; /* allow long words to break */
    overflow-wrap: break-word;
    clear: both;
    }

    .bubble.me {
    background:#ffecec;
    border:1px solid var(--accent);
    float:right;
    text-align:left;  /* better readability */
    color:#111;
    }

    .bubble.other {
    background:#e8f3e8;
    border:1px solid var(--accent-green);
    float:left;
    text-align:left;
    color:#111;
    }

  .ts { display:block; font-size:11px; color:#777; margin-top:6px; }

  .composer { padding:12px; border-top:2px solid var(--accent); background:var(--card); display:flex; gap:10px; align-items:center; }
  .composer input { flex:1; padding:12px 14px; border-radius:20px; border:1px solid var(--accent-green); font-size:14px; outline:none; }
  .composer input:focus { border-color:var(--accent); }
  .composer button { padding:10px 16px; border-radius:18px; background:var(--accent); color:#fff; border:none; cursor:pointer; font-weight:600; transition:background .2s; }
  .composer button:hover { background:#a81212; }

  /* Empty state */
  .empty { padding:40px; text-align:center; color:#666; }
  @media (max-width:900px) {
    .sidebar { width:320px; }
  }

  a { color:#fff; text-decoration:none; }
  a:hover { text-decoration:underline; }
</style>
</head>
<body>
  <div class="sidebar">
    <div class="sidebar-header">
      <div style="flex:1">
        <div class="title">Chats</div>
      </div>
      <div style="font-size:13px;">
        <a href="admindashboard.php">Back</a>
      </div>
    </div>

    <div style="padding:12px;">
      <input id="search" class="search" placeholder="Search Messenger" autocomplete="off"/>
    </div>

    <div id="chat-list" class="list">
      <!-- Recent chats injected here -->
    </div>
  </div>

  <div class="chat">
    <div id="chat-header" class="chat-header">
      <div class="avatar" id="chat-avatar">A</div>
      <div>
        <div class="title" id="chat-name">Select a user</div>
        <div style="font-size:13px;color:#666;" id="chat-sub">Start a conversation or search for a user.</div>
      </div>
    </div>

    <div id="messages" class="messages">
      <div class="empty">No conversation selected.</div>
    </div>

    <form id="composer" class="composer" style="display:none;">
      <input id="message" placeholder="Type a message..." autocomplete="off"/>
      <button type="submit">Send</button>
    </form>
  </div>
<script>

const admin = "<?php echo addslashes($admin); ?>";
let currentUser = "";

// helper: truncate
function truncate(str, n=40){
  return str.length>n ? str.slice(0,n-1) + "…" : str;
}
// helper: friendly time
function timeAgo(iso){
  if(!iso) return "";
  const t = new Date(iso);
  const diff = Math.floor((Date.now() - t.getTime())/1000);
  if(diff < 60) return diff + "s";
  if(diff < 3600) return Math.floor(diff/60) + "m";
  if(diff < 86400) return Math.floor(diff/3600) + "h";
  return t.toLocaleDateString();
}

// render single chat list item
function makeListItem(u){
  const el = document.createElement("div");
  el.className = "list-item";
  el.dataset.user = u.username;

  const avatar = document.createElement("div");
  avatar.className = "avatar";
  avatar.textContent = u.username.charAt(0).toUpperCase();

  const meta = document.createElement("div");
  meta.className = "meta";
  meta.innerHTML = `<div class="name">${u.username}</div>
                    <div class="snippet">${truncate(u.last_message || "", 48)}</div>`;

  const right = document.createElement("div");
  right.className = "right";
  right.innerHTML = `<span class="time">${timeAgo(u.last_time)}</span>
                     ${u.last_time ? '<span class="dot" title="online placeholder"></span>' : ''}`;

  el.appendChild(avatar);
  el.appendChild(meta);
  el.appendChild(right);

  el.addEventListener("click", ()=> {
    // mark active
    document.querySelectorAll(".list-item").forEach(i=>i.classList.remove("active"));
    el.classList.add("active");
    openChat(u.username);
  });

  return el;
}

// load recent chats (users who already have conversations with admin)
function loadRecentChats(){
  fetch("get_recent_chats.php")
    .then(r => r.json())
    .then(list => {
      const container = document.getElementById("chat-list");
      container.innerHTML = "";
      if(!list.length){
        container.innerHTML = '<div style="padding:18px;color:#777;">No chats yet. Search a user to start.</div>';
        return;
      }
      list.forEach(u => container.appendChild(makeListItem(u)));
    })
    .catch(err => console.error(err));
}

// search users
let searchTimer = 0;
document.getElementById("search").addEventListener("input", function(){
  clearTimeout(searchTimer);
  const q = this.value.trim();
  searchTimer = setTimeout(()=> {
    if(!q) { loadRecentChats(); return; }
    fetch("search_user.php?name=" + encodeURIComponent(q))
      .then(r => r.json())
      .then(list => {
        const container = document.getElementById("chat-list");
        container.innerHTML = "";
        if(!list.length) {
          container.innerHTML = '<div style="padding:18px;color:#777;">No users found.</div>'; return;
        }
        list.forEach(u => {
          const item = document.createElement("div");
          item.className = "list-item";
          item.dataset.user = u.username;
          item.innerHTML = `<div class="avatar">${u.username.charAt(0).toUpperCase()}</div>
                            <div class="meta"><div class="name">${u.username}${u.has_chat?'' : ' <small style="color:#999">(new)</small>'}</div>
                            <div class="snippet">${u.last_message?truncate(u.last_message,48):''}</div></div>
                            <div class="right"><span class="time">${u.last_time? timeAgo(u.last_time):''}</span></div>`;
          item.addEventListener("click", ()=> {
            document.querySelectorAll(".list-item").forEach(i=>i.classList.remove("active"));
            item.classList.add("active");
            openChat(u.username);
          });
          container.appendChild(item);
        });
      });
  }, 250);
});

// open a chat with user
function openChat(user){
  currentUser = user;
  document.getElementById("chat-name").textContent = user;
  document.getElementById("chat-avatar").textContent = user.charAt(0).toUpperCase();
  document.getElementById("chat-sub").textContent = "Loading...";
  document.getElementById("composer").style.display = "flex";
  loadMessages();
}

// load messages for current chat
function loadMessages(){
  if(!currentUser) return;
  fetch("get_messages.php?other=" + encodeURIComponent(currentUser))
    .then(r => r.json())
    .then(msgs => {
      const box = document.getElementById("messages");
      box.innerHTML = "";
      if(!msgs.length) box.innerHTML = '<div class="empty">No messages yet. Say hi!</div>';
      msgs.forEach(m => {
        const d = document.createElement("div");
        d.className = "bubble " + (m.sender === admin ? "me" : "other");
        d.innerHTML = `<div>${m.message}</div><div class="ts">${new Date(m.timestamp).toLocaleString()}</div>`;
        box.appendChild(d);
      });
      // update header snippet/time
      const last = msgs.length ? msgs[msgs.length-1] : null;
      document.getElementById("chat-sub").textContent = last ? ( (last.sender===admin? "You: " : "") + (last.message.length>60? last.message.slice(0,60)+"…": last.message) ) : "No messages yet";
      box.scrollTop = box.scrollHeight;
    });
}

// send message
document.getElementById("composer").addEventListener("submit", function(e){
  e.preventDefault();
  const txt = document.getElementById("message");
  const msg = txt.value.trim();
  if(!msg || !currentUser) return;
  fetch("send_message.php", {
    method:"POST",
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: "receiver=" + encodeURIComponent(currentUser) + "&message=" + encodeURIComponent(msg)
  }).then(()=> {
    txt.value = "";
    loadMessages();
    loadRecentChats(); // refresh left list so new convo shows up
  });
});

// poll messages and list
setInterval(()=> {
  if(currentUser) loadMessages();
  loadRecentChats();
}, 2500);

// initial
loadRecentChats();
</script>
</body>
</html>
