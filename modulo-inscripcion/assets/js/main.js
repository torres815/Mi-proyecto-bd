/* Interacciones del módulo: toasts, validación, login por fetch, ripple, tilt y menú. */
window.CEN = (() => {
    const contenedor = () => document.getElementById('toasts');

    function toast(mensaje, tipo = 'info', ms = 4200) {
        const cont = contenedor();
        if (!cont) return;
        const el = document.createElement('div');
        el.className = `toast ${tipo}`;
        el.setAttribute('role', tipo === 'error' ? 'alert' : 'status');
        el.textContent = mensaje;
        cont.appendChild(el);
        setTimeout(() => {
            el.classList.add('sale');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        }, ms);
    }

    function ripple(e) {
        const btn = e.currentTarget, r = btn.getBoundingClientRect(), d = Math.max(r.width, r.height);
        const onda = document.createElement('span');
        onda.className = 'ripple';
        onda.style.cssText = `width:${d}px;height:${d}px;left:${e.clientX - r.left - d / 2}px;top:${e.clientY - r.top - d / 2}px`;
        btn.appendChild(onda);
        onda.addEventListener('animationend', () => onda.remove());
    }

    return { toast, ripple };
})();

document.addEventListener('DOMContentLoaded', () => {
    iniciarLogin();
    iniciarPanel();
});

/* ---------- Login ---------- */
function iniciarLogin() {
    const form = document.getElementById('formLogin');
    if (!form) return;

    const email = form.email, pass = form.password, btn = document.getElementById('btnEntrar');
    const card = document.getElementById('loginCard');

    const reglas = {
        email: v => !v ? 'Ingresá tu correo institucional.'
            : !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? 'El correo no tiene un formato válido.' : '',
        password: v => !v ? 'Ingresá tu contraseña.' : v.length < 6 ? 'Debe tener al menos 6 caracteres.' : '',
    };
    const mensajes = { email: document.getElementById('errEmail'), password: document.getElementById('errPass') };

    function validar(input) {
        const msg = reglas[input.name](input.value.trim());
        const campo = input.closest('.campo');
        campo.classList.toggle('invalido', !!msg);
        campo.classList.toggle('valido', !msg && input.value !== '');
        mensajes[input.name].textContent = msg;
        return !msg;
    }

    [email, pass].forEach(i => {
        i.addEventListener('input', () => validar(i));   // validación en tiempo real
        i.addEventListener('blur', () => validar(i));
    });

    // Mostrar / ocultar contraseña
    const toggle = document.getElementById('togglePass');
    toggle.addEventListener('click', () => {
        const visible = pass.type === 'text';
        pass.type = visible ? 'password' : 'text';
        toggle.setAttribute('aria-pressed', String(!visible));
        toggle.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
    });

    btn.addEventListener('click', CEN.ripple);

    form.addEventListener('submit', async e => {
        e.preventDefault();
        if (![email, pass].map(validar).every(Boolean)) {
            sacudir(card);
            CEN.toast('Revisá los campos marcados.', 'error');
            return;
        }
        btn.disabled = true; btn.classList.add('cargando');
        try {
            const resp = await fetch('auth/login_process.php', {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'fetch' },
                credentials: 'same-origin',
            });
            const datos = await resp.json();
            if (datos.ok) {
                CEN.toast(datos.mensaje, 'ok', 1500);
                card.style.transition = 'opacity .5s, transform .5s';
                card.style.opacity = '0'; card.style.transform = 'translateY(-20px) scale(.98)';
                setTimeout(() => (location.href = datos.redirect), 600);
                return;
            }
            CEN.toast(datos.mensaje, 'error');
            sacudir(card);
            pass.value = ''; pass.focus();
        } catch {
            CEN.toast('Sin conexión con el servidor. Revisá que Apache y MySQL estén activos.', 'error');
        }
        btn.disabled = false; btn.classList.remove('cargando');
    });

    function sacudir(el) {
        el.classList.remove('sacudir'); void el.offsetWidth; el.classList.add('sacudir');
    }
}

/* ---------- Panel ---------- */
function iniciarPanel() {
    const sidebar = document.getElementById('sidebar');
    if (!sidebar) return;

    const fecha = document.getElementById('fechaHoy');
    if (fecha) fecha.textContent = new Date().toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' });

    // Navegación con estado activo
    const enlaces = sidebar.querySelectorAll('.menu a');
    enlaces.forEach(a => a.addEventListener('click', e => {
        e.preventDefault();
        enlaces.forEach(x => x.classList.remove('activo'));
        a.classList.add('activo');
        sidebar.classList.remove('abierto');
        if (a.dataset.seccion !== 'inicio') CEN.toast(`"${a.textContent}" estará disponible pronto.`);
    }));

    document.getElementById('burger')?.addEventListener('click', () => sidebar.classList.toggle('abierto'));

    document.querySelectorAll('.enlace-tarjeta').forEach(a => a.addEventListener('click', e => {
        e.preventDefault();
        CEN.toast(`"${a.dataset.aviso}" estará disponible pronto.`);
    }));

    // Efecto tilt 3D
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.querySelectorAll('.tilt').forEach(tarjeta => {
        const max = 10;
        tarjeta.addEventListener('mousemove', e => {
            const r = tarjeta.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
            tarjeta.style.transform = `rotateX(${(.5 - y) * max * 2}deg) rotateY(${(x - .5) * max * 2}deg) translateY(-6px)`;
            tarjeta.style.setProperty('--mx', `${x * 100}%`);
            tarjeta.style.setProperty('--my', `${y * 100}%`);
        });
        tarjeta.addEventListener('mouseleave', () => (tarjeta.style.transform = ''));
    });
}
