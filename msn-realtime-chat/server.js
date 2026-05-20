const path = require('path');
const crypto = require('crypto');
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*'
  }
});

const PORT = process.env.PORT || 3000;
const users = new Map();
const messageOwners = new Map();

app.use(express.static(path.join(__dirname, 'public')));

function sanitizeNickname(value) {
  const nickname = String(value || '').replace(/\s+/g, ' ').trim().slice(0, 24);
  return nickname || `Convidado-${crypto.randomInt(1000, 9999)}`;
}

function sanitizeColor(value) {
  const color = String(value || '').trim();
  return /^#[0-9a-f]{6}$/i.test(color) ? color : '#0b72d9';
}

function sanitizeMessage(value) {
  return String(value || '').replace(/\s+/g, ' ').trim().slice(0, 500);
}

function sanitizeAvatar(value) {
  const avatar = String(value || '').trim();
  const isSafeDataImage = /^data:image\/(png|jpe?g|webp|gif);base64,[a-z0-9+/=]+$/i.test(avatar);
  return isSafeDataImage && avatar.length <= 180000 ? avatar : null;
}

function onlineUsers() {
  return [...users.values()].sort((a, b) => a.nickname.localeCompare(b.nickname));
}

io.on('connection', (socket) => {
  socket.data.lastMessageAt = 0;

  // REGRA DE BLOQUEIO DE HISTORICO: o servidor nao armazena mensagens e nao emite nenhum evento de historico ao conectar.
  socket.emit('users:list', onlineUsers());

  socket.on('user:join', (payload = {}) => {
    const user = {
      id: socket.id,
      nickname: sanitizeNickname(payload.nickname),
      color: sanitizeColor(payload.color),
      avatar: sanitizeAvatar(payload.avatar),
      joinedAt: new Date().toISOString()
    };

    users.set(socket.id, user);
    socket.data.user = user;

    socket.emit('user:ready', user);
    io.emit('users:list', onlineUsers());

    // Novos entrantes nao recebem historico; apenas quem ja estava na sala ve este aviso no DOM atual.
    socket.broadcast.emit('system:message', {
      type: 'join',
      text: `${user.nickname} entrou na sala.`,
      createdAt: new Date().toISOString()
    });
  });

  socket.on('user:update', (payload = {}) => {
    if (!socket.data.user) return;

    const previousNickname = socket.data.user.nickname;
    const updated = {
      ...socket.data.user,
      nickname: sanitizeNickname(payload.nickname),
      color: sanitizeColor(payload.color),
      avatar: payload.avatar === null ? null : sanitizeAvatar(payload.avatar || socket.data.user.avatar)
    };

    users.set(socket.id, updated);
    socket.data.user = updated;

    socket.emit('user:ready', updated);
    io.emit('users:list', onlineUsers());

    if (previousNickname !== updated.nickname) {
      socket.broadcast.emit('system:message', {
        type: 'rename',
        text: `${previousNickname} agora e ${updated.nickname}.`,
        createdAt: new Date().toISOString()
      });
    }
  });

  socket.on('chat:message', (payload = {}) => {
    const user = socket.data.user;
    if (!user) return;

    const now = Date.now();
    if (now - socket.data.lastMessageAt < 350) return;
    socket.data.lastMessageAt = now;

    const text = sanitizeMessage(payload.text);
    if (!text) return;

    const message = {
      id: crypto.randomUUID(),
      userId: socket.id,
      nickname: user.nickname,
      color: sanitizeColor(payload.color || user.color),
      avatar: user.avatar,
      text,
      createdAt: new Date().toISOString()
    };

    // Permite editar/excluir mensagens ao vivo sem guardar historico de texto para novos entrantes.
    messageOwners.set(message.id, socket.id);
    io.emit('chat:message', message);
  });

  socket.on('chat:edit', (payload = {}) => {
    const user = socket.data.user;
    const messageId = String(payload.id || '');
    const text = sanitizeMessage(payload.text);
    if (!user || !messageId || !text) return;

    if (messageOwners.get(messageId) !== socket.id) {
      socket.emit('message:error', {
        id: messageId,
        text: 'Nao foi possivel editar esta mensagem.'
      });
      return;
    }

    io.emit('chat:edited', {
      id: messageId,
      text,
      editedAt: new Date().toISOString()
    });
  });

  socket.on('chat:delete', (payload = {}) => {
    const messageId = String(payload.id || '');
    if (!socket.data.user || !messageId) return;

    if (messageOwners.get(messageId) !== socket.id) {
      socket.emit('message:error', {
        id: messageId,
        text: 'Nao foi possivel excluir esta mensagem.'
      });
      return;
    }

    messageOwners.delete(messageId);
    io.emit('chat:deleted', {
      id: messageId,
      deletedAt: new Date().toISOString()
    });
  });

  socket.on('typing:start', () => {
    if (!socket.data.user) return;
    socket.broadcast.emit('typing:update', {
      userId: socket.id,
      nickname: socket.data.user.nickname,
      isTyping: true
    });
  });

  socket.on('typing:stop', () => {
    if (!socket.data.user) return;
    socket.broadcast.emit('typing:update', {
      userId: socket.id,
      nickname: socket.data.user.nickname,
      isTyping: false
    });
  });

  socket.on('disconnect', () => {
    const user = socket.data.user;
    if (!user) return;

    users.delete(socket.id);
    for (const [messageId, ownerId] of messageOwners.entries()) {
      if (ownerId === socket.id) {
        messageOwners.delete(messageId);
      }
    }
    io.emit('users:list', onlineUsers());
    socket.broadcast.emit('system:message', {
      type: 'leave',
      text: `${user.nickname} saiu da sala.`,
      createdAt: new Date().toISOString()
    });
  });
});

server.listen(PORT, () => {
  console.log(`MSN realtime chat rodando em http://localhost:${PORT}`);
});
