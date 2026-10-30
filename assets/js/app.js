// ---------- รายการโปรด (หัวใจ) ----------
document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('.fav-btn');
    if (!btn) return;
    var body = new URLSearchParams({ game_id: btn.dataset.id });
    fetch('favorite.php', { method: 'POST', body: body })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                // อัปเดตทุกปุ่มของเกมเดียวกันในหน้า
                document.querySelectorAll('.fav-btn[data-id="' + btn.dataset.id + '"]')
                    .forEach(function (b) { b.classList.toggle('active', data.favorited); });
            }
        })
        .catch(function () { alert('บันทึกรายการโปรดไม่สำเร็จ'); });
});

// ---------- เลือกเกมที่เคยเล่น (หน้าแรก) ----------
(function () {
    var chipsBox = document.getElementById('playedChips');
    if (!chipsBox || !window.GAME_LIST) return;

    var search = document.getElementById('playedSearch');
    var suggest = document.getElementById('playedSuggest');
    var allBox = document.getElementById('allGames');
    var toggle = document.getElementById('toggleAllGames');

    function pickedIds() {
        return Array.prototype.map.call(chipsBox.querySelectorAll('.played-chip'), function (c) {
            return parseInt(c.dataset.id, 10);
        });
    }

    function refreshPickButtons() {
        var ids = pickedIds();
        allBox.querySelectorAll('.pick-game').forEach(function (b) {
            b.classList.toggle('picked', ids.indexOf(parseInt(b.dataset.id, 10)) !== -1);
        });
    }

    function addGame(id) {
        if (pickedIds().indexOf(id) !== -1) return;
        var game = window.GAME_LIST.find(function (g) { return g.id === id; });
        if (!game) return;
        var chip = document.createElement('span');
        chip.className = 'played-chip';
        chip.dataset.id = id;
        chip.appendChild(document.createTextNode(game.title + ' '));
        var x = document.createElement('button');
        x.type = 'button'; x.className = 'x'; x.setAttribute('aria-label', 'ลบ'); x.textContent = '×';
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'played[]'; input.value = id;
        chip.appendChild(x);
        chip.appendChild(input);
        chipsBox.appendChild(chip);
        refreshPickButtons();
    }

    chipsBox.addEventListener('click', function (ev) {
        if (ev.target.classList.contains('x')) {
            ev.target.closest('.played-chip').remove();
            refreshPickButtons();
        }
    });

    search.addEventListener('input', function () {
        var q = search.value.trim().toLowerCase();
        suggest.innerHTML = '';
        if (!q) { suggest.hidden = true; return; }
        var ids = pickedIds();
        var found = window.GAME_LIST.filter(function (g) {
            return g.title.toLowerCase().indexOf(q) !== -1 && ids.indexOf(g.id) === -1;
        }).slice(0, 8);
        found.forEach(function (g) {
            var li = document.createElement('li');
            li.textContent = g.title;
            li.addEventListener('click', function () {
                addGame(g.id);
                search.value = '';
                suggest.hidden = true;
            });
            suggest.appendChild(li);
        });
        suggest.hidden = found.length === 0;
    });

    document.addEventListener('click', function (ev) {
        if (!ev.target.closest('.search-box')) suggest.hidden = true;
    });

    toggle.addEventListener('click', function () {
        allBox.hidden = !allBox.hidden;
        toggle.textContent = allBox.hidden ? 'ดูเกมทั้งหมด ⌄' : 'ซ่อนรายการ ⌃';
    });

    allBox.addEventListener('click', function (ev) {
        var b = ev.target.closest('.pick-game');
        if (b) addGame(parseInt(b.dataset.id, 10));
    });

    refreshPickButtons();
})();
