/* Formulario de inscripción: edad, máscaras, foto (archivo o cámara) y validación en el navegador.
   El servidor vuelve a validar todo en guardar_formulario.php. */
(() => {
    const form = document.getElementById('formInscripcion');
    if (!form) return;

    const el = n => form.elements[n];
    const val = n => (el(n).value || '').trim();
    const avisar = (msg, tipo = 'error') => window.CEN && CEN.toast(msg, tipo);
    const hoy = new Date(); hoy.setHours(0, 0, 0, 0);

    /* ---------- Fechas y edad ---------- */
    function fechaOk(s) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return false;
        const [y, m, d] = s.split('-').map(Number);
        const f = new Date(y, m - 1, d);
        return f.getFullYear() === y && f.getMonth() === m - 1 && f.getDate() === d && y >= 1900 && f <= hoy;
    }
    const salidaEdad = document.getElementById('alumnoEdad');
    function actualizarEdad() {
        const s = val('alumno_fnac');
        if (!fechaOk(s)) { salidaEdad.textContent = '—'; return; }
        const [y, m, d] = s.split('-').map(Number);
        let a = hoy.getFullYear() - y;
        if (hoy.getMonth() < m - 1 || (hoy.getMonth() === m - 1 && hoy.getDate() < d)) a--;
        salidaEdad.textContent = `${a} años`;
    }
    el('alumno_fnac').addEventListener('input', actualizarEdad);
    actualizarEdad();

    /* ---------- Máscaras: DNI solo números, CUIL ##-########-# ---------- */
    form.querySelectorAll('[data-num]').forEach(i =>
        i.addEventListener('input', () => { i.value = i.value.replace(/\D/g, '').slice(0, 8); }));
    form.querySelectorAll('[data-cuil]').forEach(i => {
        const dar = () => {
            const d = i.value.replace(/\D/g, '').slice(0, 11);
            i.value = [d.slice(0, 2), d.slice(2, 10), d.slice(10)].filter(Boolean).join('-');
        };
        i.addEventListener('input', dar);
        dar();
    });

    /* ---------- Acta: copiar nombre y DNI de la madre mientras no se editen a mano ---------- */
    [['madre_nombre', 'acta_nombre'], ['madre_dni', 'acta_dni']].forEach(([origen, destino]) => {
        const o = el(origen), d = el(destino);
        if (d.value && d.value !== o.value) d.dataset.manual = '1';
        d.addEventListener('input', () => { d.dataset.manual = '1'; });
        o.addEventListener('input', () => { if (!d.dataset.manual) d.value = o.value; });
    });

    /* ---------- Foto ---------- */
    const foto = el('foto'), previa = document.getElementById('fotoPrevia');
    function mostrarPrevia(archivo) {
        previa.replaceChildren();
        if (!archivo) { previa.textContent = 'Sin foto'; return; }
        const img = new Image();
        img.alt = 'Vista previa de la foto';
        img.src = URL.createObjectURL(archivo);
        img.onload = () => URL.revokeObjectURL(img.src);
        previa.appendChild(img);
    }
    function errorFoto(f) {
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) return 'La foto debe ser JPG, PNG o WEBP.';
        if (f.size > 3 * 1024 * 1024) return 'La foto no puede superar los 3 MB.';
        return '';
    }
    foto.addEventListener('change', () => {
        const f = foto.files[0];
        const msg = f ? errorFoto(f) : '';
        if (msg) { avisar(msg); foto.value = ''; mostrarPrevia(null); return; }
        mostrarPrevia(f || null);
    });
    document.getElementById('btnArchivo').addEventListener('click', () => foto.click());
    document.getElementById('btnQuitarFoto').addEventListener('click', () => { foto.value = ''; mostrarPrevia(null); });

    /* ---------- Cámara ---------- */
    const dlg = document.getElementById('dlgCamara'), video = document.getElementById('video');
    let stream = null;
    const detener = () => { stream?.getTracks().forEach(t => t.stop()); stream = null; video.srcObject = null; };
    dlg.addEventListener('close', detener);

    document.getElementById('btnCamara').addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            avisar('Este navegador no permite usar la cámara aquí. Subí la foto como archivo.');
            return;
        }
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 } }, audio: false });
            video.srcObject = stream;
            dlg.showModal();
        } catch {
            avisar('No pudimos acceder a la cámara. Revisá los permisos del navegador.');
        }
    });
    document.getElementById('btnCancelarCamara').addEventListener('click', () => dlg.close());
    document.getElementById('btnTomar').addEventListener('click', () => {
        if (!video.videoWidth) return;
        const escala = Math.min(1, 960 / video.videoWidth);
        const c = document.createElement('canvas');
        c.width = Math.round(video.videoWidth * escala);
        c.height = Math.round(video.videoHeight * escala);
        c.getContext('2d').drawImage(video, 0, 0, c.width, c.height);
        c.toBlob(blob => {
            if (!blob) { avisar('No se pudo tomar la foto. Probá de nuevo.'); return; }
            const archivo = new File([blob], 'foto-camara.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(archivo);
            foto.files = dt.files;
            mostrarPrevia(archivo);
            dlg.close();
        }, 'image/jpeg', 0.88);
    });

    /* ---------- Validación ---------- */
    const alerta = document.getElementById('alerta');
    const marcar = nombre => {
        const nodo = el(nombre);
        const base = nodo && !nodo.tagName ? nodo[0] : nodo;     // grupo de radios
        base?.closest('.f')?.classList.add('invalido');
    };

    function validar() {
        const errores = [];
        form.querySelectorAll('.invalido').forEach(n => n.classList.remove('invalido'));
        const exigir = (campo, ok, msg) => { if (!ok) { errores.push(msg); marcar(campo); } };
        const dni = s => /^\d{7,8}$/.test(s.replace(/\D/g, ''));
        const cuil = s => s === '' || s.replace(/\D/g, '').length === 11;
        const mail = s => s === '' || /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(s);
        const tel = s => /^[0-9+\-()\s]{6,30}$/.test(s);

        exigir('alumno_apellido_nombres', val('alumno_apellido_nombres') !== '', 'Completá el apellido y nombres del alumno/a.');
        exigir('alumno_dni', dni(val('alumno_dni')), 'El D.N.I. del alumno/a debe tener 7 u 8 números.');
        exigir('alumno_cuil', cuil(val('alumno_cuil')), 'El CUIL del alumno/a debe tener 11 números.');
        exigir('alumno_fnac', fechaOk(val('alumno_fnac')), 'La fecha de nacimiento del alumno/a no es válida.');
        if (foto.files[0]) exigir('foto', !errorFoto(foto.files[0]), errorFoto(foto.files[0]));

        [['madre', 'la madre/tutora', true], ['padre', 'el padre/tutor', false]].forEach(([p, etq, obligatorio]) => {
            const cargado = val(p + '_nombre') !== '';
            if (!obligatorio && !cargado) return;                    // el padre/tutor es opcional
            exigir(p + '_nombre', cargado, `Completá el apellido y nombres de ${etq}.`);
            exigir(p + '_dni', dni(val(p + '_dni')), `El D.N.I. de ${etq} debe tener 7 u 8 números.`);
            exigir(p + '_domicilio', val(p + '_domicilio') !== '', `Completá el domicilio de ${etq}.`);
            exigir(p + '_telefono', tel(val(p + '_telefono')), `Ingresá un teléfono válido para ${etq}.`);
            exigir(p + '_cuil', cuil(val(p + '_cuil')), `El CUIL de ${etq} debe tener 11 números.`);
            exigir(p + '_fnac', val(p + '_fnac') === '' || fechaOk(val(p + '_fnac')), `La fecha de nacimiento de ${etq} no es válida.`);
            exigir(p + '_email', mail(val(p + '_email')), 'Revisá los correos: uno no tiene un formato válido.');
        });

        exigir('acta_nombre', val('acta_nombre') !== '', 'Completá el nombre del tutor/a que firma el acta.');
        exigir('acta_dni', dni(val('acta_dni')), 'El D.N.I. del tutor/a del acta debe tener 7 u 8 números.');
        exigir('acta_correo', mail(val('acta_correo')), 'Revisá los correos: uno no tiene un formato válido.');
        exigir('acta_imagen', !!form.querySelector('[name="acta_imagen"]:checked'), 'Indicá si autoriza o no el uso de imagen en el acta.');
        return [...new Set(errores)];
    }

    form.addEventListener('submit', e => {
        const errores = validar();
        if (errores.length) {
            e.preventDefault();
            alerta.hidden = false;
            alerta.replaceChildren();
            const t = document.createElement('strong');
            t.textContent = 'Revisá estos puntos antes de guardar:';
            const ul = document.createElement('ul');
            errores.forEach(m => { const li = document.createElement('li'); li.textContent = m; ul.appendChild(li); });
            alerta.append(t, ul);
            alerta.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }
        const b = document.getElementById('btnGuardar');
        b.disabled = true; b.textContent = 'Guardando…';
    });

    /* ---------- Limpiar ---------- */
    document.getElementById('btnLimpiar').addEventListener('click', () => {
        if (!confirm('¿Borrar todo lo cargado en el formulario?')) return;
        form.querySelectorAll('input, textarea, select').forEach(i => {
            if (i.type === 'hidden') return;
            if (i.type === 'radio') i.checked = false;
            else i.value = i.dataset.defecto || '';
            delete i.dataset.manual;
        });
        form.querySelectorAll('.invalido').forEach(n => n.classList.remove('invalido'));
        alerta.hidden = true;
        mostrarPrevia(null);
        actualizarEdad();
    });
})();