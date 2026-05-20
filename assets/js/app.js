(function(){
    function escapeHtml(str){
        return String(str || '').replace(/[&<>"']/g,function(m){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m];});
    }
    function encodeForm(form){
        var data = [];
        for (var i=0;i<form.elements.length;i++) {
            var el = form.elements[i];
            if (el.name) { data.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value)); }
        }
        return data.join('&');
    }
    function postForm(url, form, callback){
        var xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.onreadystatechange = function(){
            if (xhr.readyState === 4) {
                var res = {};
                try { res = JSON.parse(xhr.responseText); } catch(e) {}
                callback(res);
            }
        };
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.send(encodeForm(form));
    }
    function postData(url, params, callback){
        var xhr = new XMLHttpRequest();
        var data = [];
        for (var key in params) {
            if (params.hasOwnProperty(key)) {
                data.push(encodeURIComponent(key) + '=' + encodeURIComponent(params[key]));
            }
        }
        xhr.open('POST', url, true);
        xhr.onreadystatechange = function(){
            if (xhr.readyState === 4) {
                var res = {};
                try { res = JSON.parse(xhr.responseText); } catch(e) {}
                callback(res);
            }
        };
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.send(data.join('&'));
    }
    function findMessageButton(target){
        while (target && target !== document) {
            if (target.getAttribute && target.getAttribute('data-action')) { return target; }
            target = target.parentNode;
        }
        return null;
    }

    var box = document.getElementById('hcMessages');
    var form = document.getElementById('hcMessageForm');
    var editState = document.getElementById('hcEditState');
    var cancelEdit = document.getElementById('hcCancelEdit');
    var lastId = 0;
    var editingId = 0;

    function closeMessageMenus(){
        if (!box) { return; }
        var menus = box.querySelectorAll('.hc-msg-actions.open');
        for (var i=0;i<menus.length;i++) {
            menus[i].className = menus[i].className.replace(/\s?open/g, '');
        }
    }

    function messageActions(m, deleted){
        if (deleted) { return ''; }
        var id = parseInt(m.id, 10);
        var senderId = parseInt(m.sender_id, 10);
        var items = '';
        if (parseInt(m.can_edit, 10) === 1) {
            items += '<button type="button" data-action="edit" data-message-id="' + id + '">Editar</button>';
        }
        if (parseInt(m.can_delete, 10) === 1) {
            items += '<button type="button" data-action="delete" data-message-id="' + id + '">Apagar</button>';
        }
        if (parseInt(m.can_report, 10) === 1) {
            items += '<button type="button" data-action="report" data-message-id="' + id + '">Denunciar</button>';
        }
        if (parseInt(m.can_block, 10) === 1) {
            items += '<button type="button" data-action="block" data-sender-id="' + senderId + '">Bloquear usuario</button>';
        }
        if (!items) { return ''; }
        return '<div class="hc-msg-actions">' +
            '<button class="hc-msg-menu-toggle" type="button" data-action="menu" data-message-id="' + id + '" aria-label="Acoes da mensagem">...</button>' +
            '<div class="hc-msg-menu" role="menu">' + items + '</div>' +
            '</div>';
    }

    function renderMessage(m){
        var mine = parseInt(m.mine, 10) === 1;
        var deleted = parseInt(m.is_deleted, 10) === 1;
        var edited = parseInt(m.is_edited, 10) === 1;
        var classes = 'hc-msg-row' + (mine ? ' mine' : '') + (deleted ? ' deleted' : '');
        var actions = messageActions(m, deleted);
        var status = '';
        var reported = parseInt(m.is_reported, 10) === 1;
        if (edited) {
            status = '<span class="hc-msg-status">editada</span>';
        }
        if (deleted) {
            status = '<span class="hc-msg-status">excluida</span>';
        } else if (reported) {
            status += '<span class="hc-msg-status">denunciada</span>';
        }

        return '<div class="' + classes + '" data-message-id="' + parseInt(m.id, 10) + '">' +
            '<img class="hc-msg-avatar" src="' + escapeHtml(m.avatar_url) + '" alt="' + escapeHtml(m.name) + '">' +
            '<div class="hc-msg">' +
                '<span class="hc-msg-name">' + escapeHtml(m.name) + '</span>' +
                '<span class="hc-msg-text">' + escapeHtml(m.body) + '</span>' +
                '<small>' + escapeHtml(m.created_at) + status + '</small>' +
                actions +
            '</div>' +
        '</div>';
    }

    function upsertMessage(m){
        if (!box) { return; }
        var id = parseInt(m.id, 10);
        var existing = box.querySelector('[data-message-id="' + id + '"]');
        if (existing) {
            existing.outerHTML = renderMessage(m);
        } else {
            box.insertAdjacentHTML('beforeend', renderMessage(m));
        }
        if (id > lastId) { lastId = id; }
        if (editingId === id && parseInt(m.is_deleted, 10) === 1) {
            clearEditMode();
        }
        box.scrollTop = box.scrollHeight;
    }

    function poll(){
        if (!box) { return; }
        var cid = box.getAttribute('data-conversation-id');
        var xhr = new XMLHttpRequest();
        xhr.open('GET','api/chat_poll.php?conversation_id=' + encodeURIComponent(cid) + '&last_id=0',true);
        xhr.onreadystatechange = function(){
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.ok) {
                        for (var i=0;i<res.messages.length;i++) { upsertMessage(res.messages[i]); }
                    }
                } catch(e) {}
            }
        };
        xhr.send(null);
    }

    function setEditMode(id){
        if (!form || !box) { return; }
        var row = box.querySelector('[data-message-id="' + parseInt(id, 10) + '"]');
        if (!row || row.className.indexOf('deleted') !== -1) { return; }
        var text = row.querySelector('.hc-msg-text');
        var input = form.elements['body'];
        var hidden = form.elements['message_id'];
        editingId = parseInt(id, 10);
        if (hidden) { hidden.value = editingId; }
        if (input && text) {
            input.value = text.textContent || text.innerText || '';
            input.focus();
        }
        if (editState) { editState.hidden = false; }
        var send = form.querySelector('.hc-send-btn');
        if (send) { send.setAttribute('aria-label', 'Salvar'); }
    }

    function clearEditMode(){
        if (!form) { return; }
        editingId = 0;
        if (form.elements['message_id']) { form.elements['message_id'].value = ''; }
        if (form.elements['body']) { form.elements['body'].value = ''; }
        if (editState) { editState.hidden = true; }
        var send = form.querySelector('.hc-send-btn');
        if (send) { send.setAttribute('aria-label', 'Enviar'); }
    }

    if (box) {
        poll();
        setInterval(poll, 2500);
        box.onclick = function(e){
            e = e || window.event;
            var button = findMessageButton(e.target || e.srcElement);
            if (!button) { return; }
            var action = button.getAttribute('data-action');
            var id = button.getAttribute('data-message-id');
            var cid = box.getAttribute('data-conversation-id');
            var csrf = form && form.elements['csrf_token'] ? form.elements['csrf_token'].value : '';
            if (action === 'menu') {
                var holder = button.parentNode;
                var wasOpen = holder.className.indexOf('open') !== -1;
                closeMessageMenus();
                if (!wasOpen) {
                    holder.className += ' open';
                }
                return;
            }
            closeMessageMenus();
            if (action === 'edit') {
                setEditMode(id);
            }
            if (action === 'delete') {
                if (!window.confirm('Excluir esta mensagem para todos?')) { return; }
                if (form.elements['message_id']) { form.elements['message_id'].value = id; }
                postForm('api/chat_delete.php', form, function(res){
                    if (!res.ok && res.error) { window.alert(res.error); }
                    if (editingId === parseInt(id, 10)) { clearEditMode(); }
                    if (form.elements['message_id'] && !editingId) { form.elements['message_id'].value = ''; }
                    poll();
                });
            }
            if (action === 'report') {
                var reason = window.prompt('Descreva rapidamente o motivo da denuncia:', 'mensagem_inadequada');
                if (reason === null) { return; }
                postData('api/chat_report.php', {
                    csrf_token: csrf,
                    conversation_id: cid,
                    message_id: id,
                    reason: reason
                }, function(res){
                    if (res.ok) {
                        window.alert('Denuncia enviada para analise.');
                        poll();
                    } else if (res.error) {
                        window.alert(res.error);
                    }
                });
            }
            if (action === 'block') {
                var senderId = button.getAttribute('data-sender-id');
                if (!senderId || !window.confirm('Bloquear este usuario? A conversa sera encerrada.')) { return; }
                window.location.href = 'api/block_user.php?target_id=' + encodeURIComponent(senderId);
            }
        };
    }

    document.onclick = function(e){
        e = e || window.event;
        var target = e.target || e.srcElement;
        if (target && target.getAttribute && target.getAttribute('data-action')) { return; }
        closeMessageMenus();
    };

    if (cancelEdit) {
        cancelEdit.onclick = function(){
            clearEditMode();
            return false;
        };
    }

    if (form) {
        form.onsubmit = function(e){
            if (e.preventDefault) { e.preventDefault(); }
            var input = form.elements['body'];
            if (!input || !input.value.replace(/\s+/g,'')) { return false; }
            var url = editingId ? 'api/chat_edit.php' : 'api/chat_send.php';
            postForm(url, form, function(res){
                if (res.ok) {
                    clearEditMode();
                    poll();
                } else if (res.error) {
                    window.alert(res.error);
                }
            });
            return false;
        };
    }

    var profileInput = document.getElementById('hcProfilePhotoInput');
    var profilePreview = document.getElementById('hcProfilePreview');
    if (profileInput && profilePreview && window.FileReader) {
        profileInput.onchange = function(){
            var file = profileInput.files && profileInput.files[0] ? profileInput.files[0] : null;
            if (!file || !file.type || file.type.indexOf('image/') !== 0) { return; }
            var reader = new FileReader();
            reader.onload = function(ev){
                profilePreview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        };
    }
})();
