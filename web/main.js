// Helper: convierte setTimeout en una Promesa
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

document.addEventListener('DOMContentLoaded', () => {

    // ===== FILTROS DEL MENÚ (síncrono, no necesita async) =====
    const filterButtons = document.querySelectorAll('#menu-filters button');
    const menuItems = document.querySelectorAll('.menu-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const filterValue = button.getAttribute('data-filter');

            menuItems.forEach(item => {
                if (filterValue === 'all' || item.getAttribute('data-category') === filterValue) {
                    item.style.display = 'block';
                    item.classList.add('animate-fade');
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // ===== CALCULADORA (síncrono) =====
    const personSlider = document.getElementById('personCount');
    const personValue = document.getElementById('personValue');
    const experienceSelect = document.getElementById('experienceType');
    const totalEstimate = document.getElementById('totalEstimate');

    function calculateEstimate() {
        if (!personSlider || !experienceSelect || !totalEstimate) return;

        const count = parseInt(personSlider.value);
        const pricePerPerson = parseInt(experienceSelect.value);
        const total = count * pricePerPerson;

        personValue.textContent = count;
        totalEstimate.textContent = `$${total.toFixed(2)}`;
    }

    if (personSlider && experienceSelect) {
        personSlider.addEventListener('input', calculateEstimate);
        experienceSelect.addEventListener('change', calculateEstimate);
    }

    // ===== CONTADORES (ahora con async/await) =====
    const counters = document.querySelectorAll('.counter');
    let animated = false;

    // Anima UN contador: el bucle espera 30ms entre cada paso
    async function animateCounter(counter) {
        const target = parseInt(counter.getAttribute('data-target'));
        const speed = target / 50;
        let count = 0;

        while (count < target) {
            count += speed;
            counter.innerText = Math.min(Math.ceil(count), target);
            await sleep(30); // pausa sin bloquear la página
        }
        counter.innerText = target;
    }

    async function checkScrollCounters() {
        const counterSection = document.getElementById('counter-section');
        if (!counterSection || animated) return;

        const sectionPos = counterSection.getBoundingClientRect().top;
        const screenPos = window.innerHeight / 1.3;

        if (sectionPos < screenPos) {
            animated = true;
            // Todos los contadores corren al mismo tiempo
            await Promise.all([...counters].map(animateCounter));
            // Aquí ya terminaron todos (podrías hacer algo más)
        }
    }

    window.addEventListener('scroll', checkScrollCounters);

    // ===== FORMULARIO DE RESERVA (ahora con async/await) =====
    const reservationForm = document.getElementById('reservationForm');
    const alertSuccess = document.getElementById('alertSuccess');

    // Simula el envío al servidor. Cuando tengas un backend, cámbialo por:
    // const res = await fetch('reservas.php', { method: 'POST', body: new FormData(reservationForm) });
    // if (!res.ok) throw new Error('Error al enviar');
    async function enviarReserva() {
        await sleep(800);
    }

    if (reservationForm) {
        reservationForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = reservationForm.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true; // evita doble envío

            try {
                await enviarReserva();               // espera la "respuesta"
                alertSuccess.classList.remove('d-none');
                reservationForm.reset();

                await sleep(5000);                   // espera 5s...
                alertSuccess.classList.add('d-none'); // ...y recién oculta
            } catch (err) {
                console.error('No se pudo enviar la reserva:', err);
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // ===== NAVEGACIÓN SUAVE (síncrono) =====
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link, .hero-content .btn');
    const navbarCollapse = document.getElementById('mainNav');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (href && href.startsWith('#')) {
                e.preventDefault();
                const targetSection = document.querySelector(href);
                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth' });

                    if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                        const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                        if (bsCollapse) bsCollapse.hide();
                    }
                }
            }
        });
    });
});


// ===== PEDIDOS (ya usaba async/await; lo dejo igual, con try/catch/finally) =====
document.addEventListener('DOMContentLoaded', () => {
    const pedidosContainer = document.getElementById('pedidos-container');
    if (!pedidosContainer) return;

    const pedidosLoading = document.getElementById('pedidos-loading');
    const pedidosError = document.getElementById('pedidos-error');
    const pedidosTableWrapper = document.getElementById('pedidos-table-wrapper');
    const btnRefreshPedidos = document.getElementById('btnRefreshPedidos');

    function escapeHtml(valor) {
        if (valor === null || valor === undefined) return '';
        return String(valor)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderPedidos(pedidos) {
        if (!pedidos || pedidos.length === 0) {
            pedidosTableWrapper.innerHTML =
                '<p class="text-muted text-center py-4 mb-0">No hay pedidos registrados todavía.</p>';
            pedidosTableWrapper.classList.remove('d-none');
            return;
        }

        const columnas = Object.keys(pedidos[0]);

        let html = '<table class="table table-dark table-hover align-middle pedidos-table">';
        html += '<thead><tr>' + columnas.map(c => `<th>${escapeHtml(c)}</th>`).join('') + '</tr></thead>';
        html += '<tbody>';
        pedidos.forEach(pedido => {
            html += '<tr>' + columnas.map(c => `<td>${escapeHtml(pedido[c])}</td>`).join('') + '</tr>';
        });
        html += '</tbody></table>';

        pedidosTableWrapper.innerHTML = html;
        pedidosTableWrapper.classList.remove('d-none');
    }

    async function cargarPedidos() {
        pedidosLoading.classList.remove('d-none');
        pedidosError.classList.add('d-none');
        pedidosTableWrapper.classList.add('d-none');

        try {
            const response = await fetch('pedidos.php', { cache: 'no-store' });  // 1er await: la respuesta HTTP
            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                throw new Error('El servidor no devolvió JSON. ¿Estás accediendo por http://localhost y no por file://?');
            }

            const json = await response.json();                                   // 2do await: leer el cuerpo

            if (!response.ok || !json.success) {
                throw new Error(json.detalle || json.error || 'No se pudo obtener los pedidos.');
            }

            renderPedidos(json.data);
        } catch (err) {
            pedidosError.textContent = 'Error al cargar los pedidos: ' + err.message;
            pedidosError.classList.remove('d-none');
        } finally {
            pedidosLoading.classList.add('d-none');
        }
    }

    if (btnRefreshPedidos) {
        btnRefreshPedidos.addEventListener('click', cargarPedidos);
    }

    cargarPedidos();
});


// ===== MODALES (síncronos, sin cambios) =====
function openDishModal(title, price, description, imgSrc) {
    document.getElementById('dishModalTitle').textContent = title;
    document.getElementById('dishModalPrice').textContent = price;
    document.getElementById('dishModalDesc').textContent = description;
    document.getElementById('dishModalImg').src = imgSrc;

    new bootstrap.Modal(document.getElementById('dishModal')).show();
}

function openLightbox(imgSrc) {
    document.getElementById('lightboxImg').src = imgSrc;
    new bootstrap.Modal(document.getElementById('lightboxModal')).show();
}