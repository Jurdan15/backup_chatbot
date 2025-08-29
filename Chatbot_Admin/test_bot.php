<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CSU-G Inquiry Assistance Chatbot</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f4f4f4;
    }
    .header {
      background-color: #8B0000;
      color: white;
      padding: 15px;
      text-align: center;
      font-size: 20px;
      font-weight: bold;
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      color: white;
      font-size: 24px;
      text-decoration: none;
      font-weight: bold;
      background: none;
      border: none;
      cursor: pointer;
    }
    .close-btn:hover {
      color: #ffcccc;
    }
    .chat-container {
      max-width: 400px;
      margin: 20px auto;
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid #ccc;
      display: flex;
      flex-direction: column;
      height: 600px;
      background-color: #fff;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    #chat-box {
      flex: 1;
      padding: 15px;
      overflow-y: auto;
      border-top: 1px solid #ccc;
      border-bottom: 1px solid #ccc;
      display: flex;
      flex-direction: column;
    }
    .message {
      max-width: 75%;
      margin: 8px;
      padding: 10px;
      border-radius: 12px;
      word-wrap: break-word;
      font-size: 14px;
      line-height: 1.4;
      flex-shrink: 0; /* prevent shrinking */
    }
    .user-message {
      background: #004085;
      color: white;
      align-self: flex-end;
    }
    .bot-message {
      background: #e9ecef;
      color: #000;
      align-self: flex-start;
    }
    #input-container {
      display: flex;
      border-top: 1px solid #ccc;
    }
    #user-input {
      flex: 1;
      padding: 12px;
      border: none;
      outline: none;
      font-size: 14px;
    }
    #send-btn {
      width: 80px;
      background-color: #6c757d;
      color: white;
      border: none;
      cursor: pointer;
      font-size: 14px;
    }
    #send-btn:hover {
      background-color: #5a6268;
    }
    .button-container {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin: 8px;
      align-self: flex-start;
    }
    .rasa-button {
      background-color: #e9ecef;
      color: #000;
      border: 1px solid #ccc;
      padding: 8px 12px;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
    }
    .rasa-button:hover {
      background-color: #d4d4d4;
    }
  </style>
</head>
<body>

<div class="header">
  CSU-G Inquiry Assistance Chatbot
  <button class="close-btn" onclick="confirmExit()">×</button>
</div>

<div class="chat-container">
  <div id="chat-box"></div>
  <div id="input-container">
    <input type="text" id="user-input" placeholder="Type your message..." />
    <button id="send-btn">Send</button>
  </div>
</div>

<script>
const chatBox = document.getElementById('chat-box');
const userInput = document.getElementById('user-input');
const sendBtn = document.getElementById('send-btn');

sendBtn.addEventListener('click', sendMessage);
userInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
});

function appendMessage(text, className) {
    const msg = document.createElement('div');
    msg.className = 'message ' + className;
    msg.innerHTML = text;
    chatBox.appendChild(msg);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function appendButtonsContainer(text, buttons) {
    const container = document.createElement('div');
    container.className = 'message bot-message';

    const textElem = document.createElement('div');
    textElem.style.marginBottom = '8px';
    textElem.innerText = text;
    container.appendChild(textElem);

    const buttonWrap = document.createElement('div');
    buttonWrap.style.display = 'flex';
    buttonWrap.style.flexDirection = 'column';
    buttonWrap.style.gap = '6px';

    buttons.forEach(btn => {
        const b = document.createElement('button');
        b.className = 'rasa-button';
        b.textContent = btn.title;
        b.onclick = () => {
            // Show title in chat
            appendMessage(btn.title, 'user-message');
            // Send payload (without "/")
            const payload = btn.payload ? btn.payload : btn.title;
            fetch('chat_api.php?message=' + encodeURIComponent(payload))
                .then(res => res.json())
                .then(handleBotResponse)
                .catch(err => {
                    console.error(err);
                    appendMessage("Error: Could not reach server.", 'bot-message');
        });
};

        buttonWrap.appendChild(b);
    });

    container.appendChild(buttonWrap);
    chatBox.appendChild(container);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function appendCard(card) {
    const container = document.createElement('div');
    container.className = 'message bot-message';
    container.style.border = '1px solid #ccc';
    container.style.borderRadius = '8px';
    container.style.overflow = 'hidden';
    container.style.padding = '10px';
    container.style.background = '#f8f9fa';
    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.alignItems = 'center';
    container.style.maxWidth = '90%';

    if (card.image) {
        const img = document.createElement('img');
        img.src = card.image; 
        img.style.width = '100%';
        img.style.borderRadius = '8px';
        img.style.marginBottom = '8px';
        container.appendChild(img);
    }

    if (card.title) {
        const title = document.createElement('div');
        title.style.fontWeight = 'bold';
        title.style.fontSize = '16px';
        title.style.textAlign = 'center';
        title.innerText = card.title;
        container.appendChild(title);
    }

    if (card.subtitle) {
        const subtitle = document.createElement('div');
        subtitle.style.fontSize = '14px';
        subtitle.style.color = '#555';
        subtitle.style.textAlign = 'center';
        subtitle.style.marginBottom = '8px';
        subtitle.innerText = card.subtitle;
        container.appendChild(subtitle);
    }

    if (card.buttons && card.buttons.length) {
        const btnContainer = document.createElement('div');
        btnContainer.style.marginTop = '8px';
        btnContainer.style.display = 'flex';
        btnContainer.style.flexDirection = 'column';
        btnContainer.style.width = '100%';
        btnContainer.style.gap = '6px';

        card.buttons.forEach(btn => {
            const b = document.createElement('button');
            b.className = 'rasa-button';
            b.style.width = '100%';
            b.textContent = btn.title;
            b.onclick = () => {
                // Show title in chat
                appendMessage(btn.title, 'user-message');
                // Send payload (without "/")
                const payload = btn.payload ? btn.payload : btn.title;
                fetch('chat_api.php?message=' + encodeURIComponent(payload))
                    .then(res => res.json())
                    .then(handleBotResponse)
                    .catch(err => {
                        console.error(err);
                        appendMessage("Error: Could not reach server.", 'bot-message');
                    });
            };

            btnContainer.appendChild(b);
        });

        container.appendChild(btnContainer);
    }

    chatBox.appendChild(container);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function sendMessage() {
    const text = userInput.value.trim();
    if (!text) return;
    appendMessage(text, 'user-message');
    userInput.value = '';

    fetch('chat_api.php?message=' + encodeURIComponent(text))
    .then(res => res.json())
    .then(data => {
        if (!Array.isArray(data)) {
            data = [data];
        }
        data.forEach(response => {
            if (response.type === 'text') {
                appendMessage(response.content, 'bot-message');
            } else if (response.type === 'image') {
                appendMessage(
                    response.text + '<div style="margin-top:8px;"><img src="'+response.image+'" style="max-width:200px;"></div>',
                    'bot-message'
                );
            } else if (response.type === 'button') {
                appendButtonsContainer(response.text, response.buttons);
            } else if (response.type === 'card') {
                appendCard(response);
            }
        });
    })
    .catch(err => {
        console.error(err);
        appendMessage("Error: Could not reach server.", 'bot-message');
    });
}

function confirmExit() {
    if (confirm("Are you sure you want to exit the chatbot?")) {
        window.location.href = "dashboard.php";
    }
}
function handleBotResponse(data) {
    if (!Array.isArray(data)) data = [data];
    data.forEach(response => {
        if (response.type === 'text') {
            appendMessage(response.content, 'bot-message');
        } else if (response.type === 'image') {
            appendMessage(
                response.text + '<div style="margin-top:8px;"><img src="'+response.image+'" style="max-width:200px;"></div>',
                'bot-message'
            );
        } else if (response.type === 'button') {
            appendButtonsContainer(response.text, response.buttons);
        } else if (response.type === 'card') {
            appendCard(response);
        }
    });
}

</script>

</body>
</html>
