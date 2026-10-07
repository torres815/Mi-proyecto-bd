/* Red de partículas interactiva (Canvas). Reacciona al cursor. */
(() => {
    const canvas = document.getElementById('particulas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const reducir = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const raton = { x: null, y: null, radio: 150 };
    let ancho, alto, puntos = [], rafId;

    function medir() {
        const dpr = Math.min(devicePixelRatio || 1, 2);
        ancho = innerWidth; alto = innerHeight;
        canvas.width = ancho * dpr; canvas.height = alto * dpr;
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        const cantidad = Math.min(110, Math.floor((ancho * alto) / 14000));
        puntos = Array.from({ length: cantidad }, () => ({
            x: Math.random() * ancho, y: Math.random() * alto,
            vx: (Math.random() - .5) * .5, vy: (Math.random() - .5) * .5,
            r: Math.random() * 1.8 + .8,
        }));
    }

    function dibujar() {
        ctx.clearRect(0, 0, ancho, alto);
        for (const p of puntos) {
            if (!reducir) {
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > ancho) p.vx *= -1;
                if (p.y < 0 || p.y > alto) p.vy *= -1;
                if (raton.x !== null) {                       // el cursor las empuja suavemente
                    const dx = p.x - raton.x, dy = p.y - raton.y, d = Math.hypot(dx, dy);
                    if (d < raton.radio && d > 0) {
                        const f = (raton.radio - d) / raton.radio;
                        p.x += (dx / d) * f * 2.2; p.y += (dy / d) * f * 2.2;
                    }
                }
            }
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(244, 217, 140, .75)';
            ctx.fill();
        }
        for (let i = 0; i < puntos.length; i++) {
            for (let j = i + 1; j < puntos.length; j++) {
                const d = Math.hypot(puntos[i].x - puntos[j].x, puntos[i].y - puntos[j].y);
                if (d < 120) {
                    ctx.strokeStyle = `rgba(224, 176, 79, ${(1 - d / 120) * .28})`;
                    ctx.lineWidth = .8;
                    ctx.beginPath(); ctx.moveTo(puntos[i].x, puntos[i].y); ctx.lineTo(puntos[j].x, puntos[j].y); ctx.stroke();
                }
            }
            if (raton.x !== null) {                           // conexiones hacia el cursor
                const d = Math.hypot(puntos[i].x - raton.x, puntos[i].y - raton.y);
                if (d < raton.radio) {
                    ctx.strokeStyle = `rgba(244, 217, 140, ${(1 - d / raton.radio) * .55})`;
                    ctx.beginPath(); ctx.moveTo(puntos[i].x, puntos[i].y); ctx.lineTo(raton.x, raton.y); ctx.stroke();
                }
            }
        }
        rafId = requestAnimationFrame(dibujar);
    }

    addEventListener('mousemove', e => { raton.x = e.clientX; raton.y = e.clientY; });
    addEventListener('mouseleave', () => { raton.x = raton.y = null; });
    addEventListener('touchmove', e => { raton.x = e.touches[0].clientX; raton.y = e.touches[0].clientY; }, { passive: true });
    addEventListener('touchend', () => { raton.x = raton.y = null; });
    addEventListener('resize', medir);
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) cancelAnimationFrame(rafId); else dibujar();
    });

    medir();
    dibujar();
})();
