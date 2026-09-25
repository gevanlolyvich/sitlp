<?php /* Partial JS: form dinamis rekomendasi & TL untuk create.php / edit.php */ ?>
<script>
(function () {
    var BUKTI_BASE = '/sisia/uploads/tl_lhp/';
    var STATUSES = ['Proses', 'Sesuai', 'Belum Sesuai', 'Belum Ditindak Lanjut', 'Tidak Dapat Ditindak Lanjut'];

    function esc(s) {
        return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function u(s) { return encodeURIComponent(s); }

    function statusOptions(sel) {
        var html = '<option value="">Pilih Status</option>';
        STATUSES.forEach(function (s) {
            html += '<option value="' + s + '"' + (s === sel ? ' selected' : '') + '>' + s + '</option>';
        });
        return html;
    }

    function buktiExistingHtml(bukti, i, j) {
        if (!bukti || !bukti.length) {
            return '<small class="text-muted">Belum ada bukti.</small>';
        }
        var html = '<div class="mb-1"><small class="text-muted d-block">Bukti tersimpan (' + bukti.length + '):</small>';
        bukti.forEach(function (f) {
            html += '<div class="form-check mb-1">'
                + '<input class="form-check-input" type="checkbox" name="rekomendasi[' + i + '][tl][' + j + '][hapus_bukti][]" value="' + u(f) + '">'
                + '<label class="form-check-label">'
                + '<a href="' + BUKTI_BASE + u(f) + '" target="_blank" class="text-break"><i class="fas fa-paperclip"></i> ' + esc(f) + '</a>'
                + ' <small class="text-danger">hapus</small></label>'
                + '<input type="hidden" name="rekomendasi[' + i + '][tl][' + j + '][existing_bukti][]" value="' + u(f) + '">'
                + '</div>';
        });
        html += '</div>';
        return html;
    }

    function tlHtml(i, j, data) {
        data = data || {};
        var existing = data.bukti || [];
        return '<div class="border rounded p-3 mb-2 tl-box" data-rek="' + i + '" data-tl="' + j + '">'
            + '<div class="d-flex justify-content-between align-items-center mb-2">'
            + '<label class="form-label mb-0 fw-semibold">Tindak Lanjut ' + (j + 1) + '</label>'
            + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeTL(' + i + ',' + j + ')"><i class="fas fa-trash"></i></button>'
            + '</div>'
            + '<div class="mb-2">'
            + '<textarea name="rekomendasi[' + i + '][tl][' + j + '][uraian]" class="form-control" rows="2" placeholder="Uraian tindak lanjut..." required>' + esc(data.uraian || '') + '</textarea>'
            + '</div>'
            + '<div class="row g-2 align-items-start">'
            + '<div class="col-md-4">'
            + '<select name="rekomendasi[' + i + '][tl][' + j + '][status]" class="form-select" required>' + statusOptions(data.status || '') + '</select>'
            + '</div>'
            + '<div class="col-md-5">'
            + '<input type="file" name="rekomendasi[' + i + '][tl][' + j + '][bukti][]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">'
            + '<small class="text-muted">Maks 5 file @5MB. <span class="text-warning fw-semibold">Jangan upload file berukuran besar!</span></small>'
            + '</div>'
            + '<div class="col-md-3">' + buktiExistingHtml(existing, i, j) + '</div>'
            + '</div>'
            + '</div>';
    }

    function rekHtml(i, data) {
        data = data || {};
        var jml = data.jml_tl != null ? data.jml_tl : (data.tl ? data.tl.length : 0);
        return '<div class="card border mb-3 rst-rek-card">'
            + '<div class="card-header py-2 d-flex justify-content-between align-items-center">'
            + '<strong>Rekomendasi <span class="rek-no-text">' + (i + 1) + '</span></strong>'
            + '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRek(' + i + ')"><i class="fas fa-trash"></i></button>'
            + '</div>'
            + '<div class="card-body">'
            + '<div class="row g-2 mb-2">'
            + '<div class="col-md-9">'
            + '<label class="form-label">Uraian Rekomendasi <span class="jxb-required">*</span></label>'
            + '<textarea name="rekomendasi[' + i + '][uraian]" class="form-control" rows="2" required>' + esc(data.uraian || '') + '</textarea>'
            + '</div>'
            + '<div class="col-md-3">'
            + '<label class="form-label">Jml Tindak Lanjut</label>'
            + '<input type="number" id="jml_tl_' + i + '" name="rekomendasi[' + i + '][jml_tl]" class="form-control" min="0" max="10" value="' + jml + '" oninput="renderTL(' + i + ')">'
            + '</div>'
            + '</div>'
            + '<div id="tl_container_' + i + '"></div>'
            + '</div>'
            + '</div>';
    }

    function captureSeed() {
        var seed = [];
        var cards = document.querySelectorAll('#rekContainer .rst-rek-card');
        cards.forEach(function (card, i) {
            var obj = { uraian: '', jml_tl: 0, tl: [] };
            var uraian = card.querySelector('textarea[name="rekomendasi[' + i + '][uraian]"]');
            if (uraian) { obj.uraian = uraian.value; }
            var jmlEl = card.querySelector('input[name="rekomendasi[' + i + '][jml_tl]"]');
            if (jmlEl) { obj.jml_tl = parseInt(jmlEl.value) || 0; }
            card.querySelectorAll('.tl-box').forEach(function (box, j) {
                var t = { uraian: '', status: '', bukti: [] };
                var tu = box.querySelector('textarea[name="rekomendasi[' + i + '][tl][' + j + '][uraian]"]');
                if (tu) { t.uraian = tu.value; }
                var ts = box.querySelector('select[name="rekomendasi[' + i + '][tl][' + j + '][status]"]');
                if (ts) { t.status = ts.value; }
                box.querySelectorAll('input[name="rekomendasi[' + i + '][tl][' + j + '][existing_bukti][]"]').forEach(function (h) {
                    t.bukti.push(decodeURIComponent(h.value));
                });
                box.querySelectorAll('input[name="rekomendasi[' + i + '][tl][' + j + '][hapus_bukti][]"]:checked').forEach(function (h) {
                    var f = decodeURIComponent(h.value);
                    t.bukti = t.bukti.filter(function (x) { return x !== f; });
                });
                obj.tl.push(t);
            });
            seed.push(obj);
        });
        return seed;
    }

    function writeSeed(seed) {
        window.LHP_SEED = seed;
    }

    function renderTL(i) {
        var el = document.getElementById('jml_tl_' + i);
        if (!el) { return; }
        var n = Math.max(0, Math.min(10, parseInt(el.value) || 0));
        var cont = document.getElementById('tl_container_' + i);
        if (!cont) { return; }
        var seed = (window.LHP_SEED || [])[i] || {};
        var tls = seed.tl || [];
        var html = '';
        for (var j = 0; j < n; j++) {
            html += tlHtml(i, j, tls[j] || {});
        }
        cont.innerHTML = html;
        var jmlNow = parseInt(el.value) || 0;
        if (seed.jml_tl == null && tls.length && !jmlNow) {
            el.value = tls.length;
        }
        updateSummary();
    }

    function renderRek(skipCapture) {
        if (!skipCapture && document.querySelectorAll('#rekContainer .rst-rek-card').length > 0) {
            writeSeed(captureSeed());
        }
        var total = document.getElementById('jml_rekomendasi');
        if (!total) { return; }
        var n = Math.max(0, Math.min(20, parseInt(total.value) || 0));
        var cont = document.getElementById('rekContainer');
        if (!cont) { return; }
        var seed = window.LHP_SEED || [];
        var html = '';
        for (var i = 0; i < n; i++) {
            html += rekHtml(i, seed[i] || {});
        }
        cont.innerHTML = html;
        var jml = window.LHP_SEED || [];
        for (var k = 0; k < n; k++) {
            var jmlEl = document.getElementById('jml_tl_' + k);
            if (jmlEl) {
                var s = jml[k] || {};
                if (s.jml_tl != null) { jmlEl.value = s.jml_tl; }
                else if (s.tl && s.tl.length) { jmlEl.value = s.tl.length; }
            }
            renderTL(k);
        }
        updateSummary();
    }

    function removeRek(i) {
        var seed = captureSeed();
        seed.splice(i, 1);
        writeSeed(seed);
        var total = document.getElementById('jml_rekomendasi');
        total.value = Math.max(0, seed.length);
        renderRek(true);
    }

    function removeTL(i, j) {
        var seed = captureSeed();
        if (!seed[i] || !seed[i].tl.length) { return; }
        seed[i].tl.splice(j, 1);
        writeSeed(seed);
        var jmlEl = document.getElementById('jml_tl_' + i);
        if (jmlEl) { jmlEl.value = seed[i].tl.length; }
        renderTL(i);
    }

    function updateSummary() {
        var counts = { 'Proses': 0, 'Sesuai': 0, 'Belum Sesuai': 0, 'Belum Ditindak Lanjut': 0, 'Tidak Dapat Ditindak Lanjut': 0 };
        document.querySelectorAll('#rekContainer select[name$="[status]"]').forEach(function (sel) {
            if (counts[sel.value] !== undefined) { counts[sel.value]++; }
        });
        STATUSES.forEach(function (k) {
            var el = document.getElementById('sum_' + k.replace(/\s+/g, '_'));
            if (el) { el.textContent = counts[k]; }
        });
    }

    function validateLhpForm(e) {
        var msg = [];
        var bad = false;
        document.querySelectorAll('#rekContainer input[type="file"]').forEach(function (inp) {
            var files = inp.files || [];
            if (files.length > 5) { bad = true; msg.push('- Satu TL maksimal 5 file bukti.'); }
            for (var x = 0; x < files.length; x++) {
                if (files[x].size > 5 * 1024 * 1024) {
                    bad = true;
                    msg.push('- File "' + files[x].name + '" melebihi 5MB.');
                }
            }
        });
        if (bad) {
            e.preventDefault();
            alert('Periksa file bukti:\n' + msg.join('\n'));
        }
        updateSummary();
    }

    window.renderRek = renderRek;
    window.renderTL = renderTL;
    window.removeRek = removeRek;
    window.removeTL = removeTL;
    window.updateSummary = updateSummary;

    document.addEventListener('DOMContentLoaded', function () {
        var seed = window.LHP_SEED || [];
        writeSeed(seed);
        var total = document.getElementById('jml_rekomendasi');
        if (seed.length && total) { total.value = seed.length; }
        renderRek();
        document.querySelectorAll('form[data-rek-form]').forEach(function (form) {
            form.addEventListener('submit', validateLhpForm);
        });
    });
})();
</script>