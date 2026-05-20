const socket = io();

const DEFAULT_AVATAR =
  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96"%3E%3Cdefs%3E%3ClinearGradient id="g" x1="0" x2="1" y1="0" y2="1"%3E%3Cstop stop-color="%231583e9"/%3E%3Cstop offset=".58" stop-color="%2339ad6a"/%3E%3Cstop offset="1" stop-color="%23f4a72e"/%3E%3C/linearGradient%3E%3C/defs%3E%3Crect width="96" height="96" rx="48" fill="url(%23g)"/%3E%3Ccircle cx="48" cy="36" r="18" fill="white" opacity=".92"/%3E%3Cpath d="M18 86c5-20 17-31 30-31s25 11 30 31" fill="white" opacity=".92"/%3E%3C/svg%3E';

const state = {
  user: null,
  joinAvatar: null,
  profileAvatar: null,
  editingMessageId: null,
  typingUsers: new Map(),
  typingTimer: null
};

const joinScreen = document.querySelector('#joinScreen');
const joinForm = document.querySelector('#joinForm');
const joinNickname = document.querySelector('#joinNickname');
const joinAvatar = document.querySelector('#joinAvatar');
const joinAvatarPreview = document.querySelector('#joinAvatarPreview');
const joinColor = document.querySelector('#joinColor');
const appShell = document.querySelector('#appShell');
const profileForm = document.querySelector('#profileForm');
const profileNickname = document.querySelector('#profileNickname');
const profileAvatar = document.querySelector('#profileAvatar');
const profileAvatarPreview = document.querySelector('#profileAvatarPreview');
const removeProfilePhoto = document.querySelector('#removeProfilePhoto');
const profileColor = document.querySelector('#profileColor');
const messageForm = document.querySelector('#messageForm');
const messageInput = document.querySelector('#messageInput');
const messageColor = document.querySelector('#messageColor');
const messages = document.querySelector('#messages');
const userList = document.querySelector('#userList');
const onlineCount = document.querySelector('#onlineCount');
const connectionStatus = document.querySelector('#connectionStatus');
const typingLine = document.querySelector('#typingLine');
const formatPreview = document.querySelector('#formatPreview');
const editState = document.querySelector('#editState');
const cancelEdit = document.querySelector('#cancelEdit');
const clearMessages = document.querySelector('#clearMessages');
const sidebar = document.querySelector('#sidebar');
const sidebarToggle = document.querySelector('#sidebarToggle');
const mobileScrim = document.querySelector('#mobileScrim');

const storedProfile = JSON.parse(localStorage.getItem('msn-profile') || '{}');
state.joinAvatar = storedProfile.avatar || null;
state.profileAvatar = storedProfile.avatar || null;
joinNickname.value = storedProfile.nickname || '';
joinColor.value = storedProfile.color || '#0b72d9';
joinAvatarPreview.src = state.joinAvatar || DEFAULT_AVATAR;
profileAvatarPreview.src = state.profileAvatar || DEFAULT_AVATAR;
messageColor.value = joinColor.value;
formatPreview.style.color = messageColor.value;

function setViewportHeight() {
  const height = window.visualViewport ? window.visualViewport.height : window.innerHeight;
  // CORRECAO MOBILE: usa a altura real do VisualViewport para o composer nao ficar atras do teclado virtual.
  document.documentElement.style.setProperty('--vvh', `${height}px`);
  requestAnimationFrame(scrollToBottom);
}

setViewportHeight();
window.addEventListener('resize', setViewportHeight);
if (window.visualViewport) {
  window.visualViewport.addEventListener('resize', setViewportHeight);
  window.visualViewport.addEventListener('scroll', setViewportHeight);
}

function saveProfile(profile) {
  localStorage.setItem('msn-profile', JSON.stringify({
    nickname: profile.nickname,
    color: profile.color,
    avatar: profile.avatar || null
  }));
}

function applyProfile(profile) {
  state.user = profile;
  state.profileAvatar = profile.avatar || null;
  profileNickname.value = profile.nickname;
  profileColor.value = profile.color;
  profileAvatarPreview.src = state.profileAvatar || DEFAULT_AVATAR;
  messageColor.value = profile.color;
  formatPreview.style.color = profile.color;
  saveProfile(profile);
}

function scrollToBottom() {
  messages.scrollTop = messages.scrollHeight;
}

function formatTime(value) {
  return new Intl.DateTimeFormat('pt-BR', {
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(value));
}

function getInitial(name) {
  return String(name || '?').trim().charAt(0).toUpperCase() || '?';
}

function buildAvatar(user, className) {
  if (user.avatar) {
    const img = document.createElement('img');
    img.className = className;
    img.src = user.avatar;
    img.alt = `Foto de ${user.nickname}`;
    return img;
  }

  const fallback = document.createElement('span');
  fallback.className = `${className} avatar-fallback`;
  fallback.style.setProperty('--user-color', user.color);
  fallback.style.setProperty('--message-color', user.color);
  fallback.textContent = getInitial(user.nickname);
  fallback.setAttribute('aria-hidden', 'true');
  return fallback;
}

function loadAvatarFile(file, callback) {
  if (!file) return;

  if (!file.type.startsWith('image/')) {
    addSystemMessage({ text: 'Escolha um arquivo de imagem valido.' });
    return;
  }

  if (file.size > 4 * 1024 * 1024) {
    addSystemMessage({ text: 'A foto precisa ter ate 4 MB.' });
    return;
  }

  const reader = new FileReader();
  reader.onload = () => {
    const img = new Image();
    img.onload = () => {
      const size = 160;
      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d');
      const scale = Math.max(size / img.width, size / img.height);
      const width = img.width * scale;
      const height = img.height * scale;
      const x = (size - width) / 2;
      const y = (size - height) / 2;

      canvas.width = size;
      canvas.height = size;
      context.drawImage(img, x, y, width, height);
      callback(canvas.toDataURL('image/jpeg', 0.82));
    };
    img.src = reader.result;
  };
  reader.readAsDataURL(file);
}

function addSystemMessage(message) {
  const node = document.createElement('div');
  node.className = 'system-message';
  node.textContent = message.text;
  messages.appendChild(node);
  scrollToBottom();
}

function createEditedLabel() {
  const label = document.createElement('span');
  label.className = 'edited-label';
  label.textContent = 'editada';
  return label;
}

function createMessageActions(message) {
  const actions = document.createElement('div');
  actions.className = 'message-actions';

  const edit = document.createElement('button');
  edit.className = 'message-action';
  edit.type = 'button';
  edit.textContent = 'Editar';
  edit.addEventListener('click', () => startEditing(message.id));

  const remove = document.createElement('button');
  remove.className = 'message-action delete';
  remove.type = 'button';
  remove.textContent = 'Excluir';
  remove.addEventListener('click', () => requestDeleteMessage(message.id));

  actions.append(edit, remove);
  return actions;
}

function addChatMessage(message) {
  const isOwn = message.userId === state.user?.id;
  const wrapper = document.createElement('article');
  wrapper.className = `message${isOwn ? ' own' : ''}`;
  wrapper.dataset.messageId = message.id;
  wrapper.style.setProperty('--message-color', message.color);

  const avatar = buildAvatar(message, 'message-avatar');
  const body = document.createElement('div');
  body.className = 'message-body';

  const bubble = document.createElement('div');
  bubble.className = 'bubble';

  const meta = document.createElement('div');
  meta.className = 'message-meta';

  const name = document.createElement('span');
  name.className = 'message-name';
  name.textContent = message.nickname;

  const time = document.createElement('time');
  time.dateTime = message.createdAt;
  time.textContent = formatTime(message.createdAt);

  const text = document.createElement('div');
  text.className = 'message-text';
  text.textContent = message.text;

  meta.append(name, time);
  bubble.append(meta, text);
  body.appendChild(bubble);

  if (isOwn) {
    body.appendChild(createMessageActions(message));
  }

  wrapper.append(avatar, body);
  messages.appendChild(wrapper);
  scrollToBottom();
}

function renderUsers(users) {
  userList.replaceChildren();
  onlineCount.textContent = `${users.length} ${users.length === 1 ? 'usuario' : 'usuarios'}`;

  users.forEach((user) => {
    const item = document.createElement('li');
    item.className = 'user-item';
    item.title = user.nickname;

    const avatar = buildAvatar(user, 'user-photo');

    const name = document.createElement('span');
    name.textContent = user.nickname;

    item.append(avatar, name);
    userList.appendChild(item);
  });
}

function setConnectionLabel(label) {
  connectionStatus.textContent = label;
}

function updateTypingLine() {
  const names = [...state.typingUsers.values()];
  typingLine.textContent = names.length ? `${names.join(', ')} digitando...` : '';
}

function openSidebar() {
  sidebar.classList.add('is-open');
  mobileScrim.classList.add('is-open');
}

function closeSidebar() {
  sidebar.classList.remove('is-open');
  mobileScrim.classList.remove('is-open');
}

function startEditing(messageId) {
  const message = messages.querySelector(`[data-message-id="${CSS.escape(messageId)}"]`);
  if (!message || message.classList.contains('deleted')) return;

  const text = message.querySelector('.message-text')?.textContent || '';
  state.editingMessageId = messageId;
  messageInput.value = text;
  editState.hidden = false;
  messageForm.querySelector('.send-button').textContent = 'Salvar';
  messageInput.focus({ preventScroll: true });
}

function cancelEditing() {
  state.editingMessageId = null;
  editState.hidden = true;
  messageInput.value = '';
  messageForm.querySelector('.send-button').textContent = 'Enviar';
}

function requestDeleteMessage(messageId) {
  const shouldDelete = window.confirm('Excluir esta mensagem para todos na sala?');
  if (!shouldDelete) return;
  socket.emit('chat:delete', { id: messageId });
}

function applyEditedMessage({ id, text, editedAt }) {
  const message = messages.querySelector(`[data-message-id="${CSS.escape(id)}"]`);
  if (!message || message.classList.contains('deleted')) return;

  const textNode = message.querySelector('.message-text');
  const meta = message.querySelector('.message-meta');
  textNode.textContent = text;
  message.dataset.editedAt = editedAt;

  if (!meta.querySelector('.edited-label')) {
    meta.appendChild(createEditedLabel());
  }

  if (state.editingMessageId === id) {
    cancelEditing();
  }
}

function applyDeletedMessage({ id }) {
  const message = messages.querySelector(`[data-message-id="${CSS.escape(id)}"]`);
  if (!message) return;

  message.classList.add('deleted');
  message.querySelector('.message-text').textContent = 'Mensagem excluida.';
  message.querySelector('.message-actions')?.remove();

  if (state.editingMessageId === id) {
    cancelEditing();
  }
}

document.querySelectorAll('.swatch').forEach((swatch) => {
  swatch.addEventListener('click', () => {
    joinColor.value = swatch.dataset.color;
    messageColor.value = swatch.dataset.color;
    formatPreview.style.color = swatch.dataset.color;
  });
});

joinColor.addEventListener('input', () => {
  messageColor.value = joinColor.value;
  formatPreview.style.color = joinColor.value;
});

joinAvatar.addEventListener('change', () => {
  loadAvatarFile(joinAvatar.files[0], (avatar) => {
    state.joinAvatar = avatar;
    joinAvatarPreview.src = avatar;
  });
});

profileAvatar.addEventListener('change', () => {
  loadAvatarFile(profileAvatar.files[0], (avatar) => {
    state.profileAvatar = avatar;
    profileAvatarPreview.src = avatar;
  });
});

removeProfilePhoto.addEventListener('click', () => {
  state.profileAvatar = null;
  profileAvatar.value = '';
  profileAvatarPreview.src = DEFAULT_AVATAR;
});

messageColor.addEventListener('input', () => {
  formatPreview.style.color = messageColor.value;
});

joinForm.addEventListener('submit', (event) => {
  event.preventDefault();
  socket.emit('user:join', {
    nickname: joinNickname.value,
    color: joinColor.value,
    avatar: state.joinAvatar
  });
});

profileForm.addEventListener('submit', (event) => {
  event.preventDefault();
  socket.emit('user:update', {
    nickname: profileNickname.value,
    color: profileColor.value,
    avatar: state.profileAvatar
  });
  closeSidebar();
});

profileColor.addEventListener('input', () => {
  messageColor.value = profileColor.value;
  formatPreview.style.color = profileColor.value;
});

cancelEdit.addEventListener('click', cancelEditing);

// CORRECAO MOBILE: submit do form cobre toque/click no botao, Enter e "Ir" no teclado virtual.
messageForm.addEventListener('submit', function (event) {
  event.preventDefault();

  const text = messageInput.value.trim();
  if (!text) return;

  if (state.editingMessageId) {
    socket.emit('chat:edit', {
      id: state.editingMessageId,
      text
    });
  } else {
    socket.emit('chat:message', {
      text,
      color: messageColor.value
    });
  }

  if (!state.editingMessageId) {
    messageInput.value = '';
  }

  socket.emit('typing:stop');
  messageInput.focus({ preventScroll: true });
});

messageInput.addEventListener('input', () => {
  socket.emit('typing:start');
  window.clearTimeout(state.typingTimer);
  state.typingTimer = window.setTimeout(() => {
    socket.emit('typing:stop');
  }, 900);
});

messageInput.addEventListener('blur', () => {
  socket.emit('typing:stop');
});

clearMessages.addEventListener('click', () => {
  messages.replaceChildren();
  cancelEditing();
});

sidebarToggle.addEventListener('click', openSidebar);
mobileScrim.addEventListener('click', closeSidebar);

socket.on('connect', () => {
  setConnectionLabel('Online');
});

socket.on('disconnect', () => {
  setConnectionLabel('Reconectando...');
});

socket.on('user:ready', (user) => {
  applyProfile(user);
  joinScreen.hidden = true;
  appShell.hidden = false;
  requestAnimationFrame(() => messageInput.focus({ preventScroll: true }));
});

socket.on('users:list', renderUsers);

socket.on('system:message', addSystemMessage);

socket.on('chat:message', addChatMessage);

socket.on('chat:edited', applyEditedMessage);

socket.on('chat:deleted', applyDeletedMessage);

socket.on('message:error', addSystemMessage);

socket.on('typing:update', ({ userId, nickname, isTyping }) => {
  if (userId === state.user?.id) return;

  if (isTyping) {
    state.typingUsers.set(userId, nickname);
  } else {
    state.typingUsers.delete(userId);
  }

  updateTypingLine();
});
