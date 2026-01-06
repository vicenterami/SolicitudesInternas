import './bootstrap';
import Alpine from 'alpinejs';
import axios from 'axios'; 

window.Alpine = Alpine;
Alpine.start();

// 🟢 1. OBTENER ID DEL USUARIO ACTUAL
const metaUserId = document.head.querySelector('meta[name="user-id"]');
const currentUserId = metaUserId ? parseInt(metaUserId.content) : null;

if (window.Echo) {
    console.log('🚀 Echo inicializado. Usuario ID:', currentUserId);

    // =================================================================
    // 🌍 CANAL GLOBAL: 'solicitudes' (Dashboard y Tabla General)
    // =================================================================
    window.Echo.channel('solicitudes')
        
        // A) NUEVA SOLICITUD
        .listen('.solicitud.creada', (e) => {
            console.log('🌍 Nueva Solicitud:', e);

            // 1. Dashboard: Aumentar contadores numéricos
            const totalEl = document.getElementById('dash-total');
            const pendientesEl = document.getElementById('dash-pendientes');
            
            if (totalEl) totalEl.innerText = parseInt(totalEl.innerText) + 1;
            if (pendientesEl) pendientesEl.innerText = parseInt(pendientesEl.innerText) + 1;

            // 2. ACTUALIZAR GRÁFICO DE PRIORIDAD (KPI) 📊
            if (window.miChart && e.solicitud.prioridad) {
                // Buscamos en qué posición del array está la prioridad (ej: "Alta")
                const labels = window.miChart.data.labels;
                const index = labels.findIndex(label => label.toLowerCase() === e.solicitud.prioridad.toLowerCase());

                if (index !== -1) {
                    // Sumamos 1 al valor existente en esa porción del pastel
                    const valorActual = window.miChart.data.datasets[0].data[index];
                    window.miChart.data.datasets[0].data[index] = valorActual + 1;
                    window.miChart.update();
                    console.log(`📊 Gráfico actualizado: +1 a ${e.solicitud.prioridad}`);
                }
            }

            // 3. Tabla: Crear nueva fila si estamos en el index
            const tablaBody = document.querySelector('table tbody');
            if (tablaBody && !document.getElementById(`fila-${e.solicitud.id}`)) {
                const headers = document.querySelectorAll('table thead th');
                const esAdminOTecnico = headers.length >= 5; 
                let celdaAcciones = esAdminOTecnico ? 
                    `<td class="px-6 py-4 whitespace-nowrap text-sm font-medium"><a href="/solicitudes/${e.solicitud.id}/editar" class="text-indigo-600 hover:text-indigo-900">Gestionar</a></td>` : '';

                const nuevaFilaHTML = `
                    <tr id="fila-${e.solicitud.id}" class="bg-yellow-100 transition-colors duration-2000">
                        <td class="px-5 py-5 border-b border-gray-200 text-sm"><a href="/solicitudes/${e.solicitud.id}" class="text-blue-600 hover:underline font-bold">${e.solicitud.titulo}</a></td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">${e.solicitud.creador ? e.solicitud.creador.name : 'Usuario'}</td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                            <span class="px-4 py-1 rounded-full text-sm font-bold border bg-red-100 text-red-800 border-red-200">
                                ${e.solicitud.estado.charAt(0).toUpperCase() + e.solicitud.estado.slice(1)}
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">Sin asignar</td>
                        ${celdaAcciones}
                    </tr>`;
                tablaBody.insertAdjacentHTML('afterbegin', nuevaFilaHTML);
                setTimeout(() => {
                    const f = document.getElementById(`fila-${e.solicitud.id}`);
                    if(f) f.classList.remove('bg-yellow-100');
                }, 2500);
            }
        })

        // B) SOLICITUD ACTUALIZADA
        .listen('.solicitud.actualizada', (e) => {
            console.log('🔄 EVENTO RECIBIDO: Solicitud Actualizada', e);
            
            const ant = e.estadoAnterior ? String(e.estadoAnterior).toLowerCase() : 'desconocido';
            const nue = e.solicitud.estado ? String(e.solicitud.estado).toLowerCase() : 'desconocido';

            console.log(`📊 Procesando cambio Dashboard: ${ant} -> ${nue}`);

            // --- 1. ACTUALIZAR DASHBOARD (Números) ---
            const dashTotal = document.getElementById('dash-total');
            if (dashTotal) {
                const ids = {
                    'pendiente': 'dash-pendientes',
                    'asignada': 'dash-asignadas',
                    'resuelta': 'dash-resueltas'
                };

                if (ant !== nue) {
                    if (ids[ant]) {
                        const elOld = document.getElementById(ids[ant]);
                        if (elOld) {
                            let val = parseInt(elOld.innerText) || 0;
                            if (val > 0) elOld.innerText = val - 1;
                        }
                    }
                    if (ids[nue]) {
                        const elNew = document.getElementById(ids[nue]);
                        if (elNew) elNew.innerText = (parseInt(elNew.innerText) || 0) + 1;
                    }
                }
            }

            // 2. ACTUALIZAR TABLA
            const fila = document.getElementById(`fila-${e.solicitud.id}`);
            if (fila) {
                const celdaEstado = fila.children[2]; 
                const celdaTecnico = fila.children[3];
                const badge = celdaEstado.querySelector('span');
                
                if (badge) {
                    badge.innerText = nue.charAt(0).toUpperCase() + nue.slice(1);
                    let col = 'bg-gray-100';
                    if (nue === 'pendiente') col = 'bg-red-100 text-red-800 border-red-200';
                    if (nue === 'asignada') col = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    if (nue === 'resuelta') col = 'bg-green-100 text-green-800 border-green-200';
                    badge.className = `px-4 py-1 rounded-full text-sm font-bold border ${col}`;
                }
                if (celdaTecnico) {
                    celdaTecnico.innerText = e.solicitud.tecnico ? e.solicitud.tecnico.name : 'Sin asignar';
                }
                fila.classList.add('bg-yellow-100');
                setTimeout(() => fila.classList.remove('bg-yellow-100'), 1500);
            }
        });


    // =================================================================
    // 🎯 CANAL ESPECÍFICO (Chat y Detalles)
    // =================================================================
    const solicitudData = document.getElementById('solicitud-data');
    if (solicitudData) {
        const solicitudId = solicitudData.dataset.id;
        console.log(`📡 Conectando al canal privado: solicitud.${solicitudId}`);

        // Chat Form
        const form = document.getElementById('form-comentario');
        const inputComentario = document.querySelector('textarea[name="comentario"]');
        if (form && inputComentario) {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); 
                const btn = form.querySelector('button[type="submit"]');
                const txt = inputComentario.value;
                if (!txt.trim()) return;
                btn.disabled = true; btn.innerText = '...';
                axios.post(`/solicitudes/${solicitudId}/comentarios`, { comentario: txt })
                    .then(() => inputComentario.value = '')
                    .catch(() => alert('Error'))
                    .finally(() => { btn.disabled = false; btn.innerText = 'Enviar Comentario'; });
            });
        }

        // Listeners Comentarios y Detalles
        window.Echo.channel(`solicitud.${solicitudId}`)
            .listen('.comentario.creado', (e) => {
                if (document.getElementById(`comentario-${e.comentario.id}`)) return;
                const lista = document.getElementById('lista-comentarios');
                const vacio = document.querySelector('p.text-gray-500.italic');
                if (vacio) vacio.remove();
                const esMio = currentUserId === e.comentario.user_id;
                const clase = esMio ? 'border-l-4 border-blue-500' : '';
                let rol = e.comentario.user.rol_id === 2 ? 'Técnico' : (e.comentario.user.rol_id === 3 ? 'Admin' : 'Usuario');
                const html = `
                    <div id="comentario-${e.comentario.id}" x-data="{ editing: false }" class="bg-white p-4 rounded-lg shadow ${clase} animate-pulse">
                        <div class="flex justify-between items-center mb-2">
                            <div class="font-bold text-sm">${e.comentario.user.name} <span class="text-xs text-gray-500">(${rol})</span></div>
                            <div class="text-xs text-gray-400">Ahora</div>
                        </div>
                        <p x-show="!editing" class="text-gray-700 whitespace-pre-wrap">${e.comentario.comentario}</p>
                    </div>`;
                lista.insertAdjacentHTML('beforeend', html);
                lista.scrollTop = lista.scrollHeight;
            })
            .listen('.comentario.actualizado', (e) => {
                const div = document.getElementById(`comentario-${e.comentario.id}`);
                if (div) {
                    const p = div.querySelector('p[x-show="!editing"]');
                    if (p) {
                        p.innerText = e.comentario.comentario;
                        if (!p.innerHTML.includes('Editado')) p.innerHTML += ' <small class="text-gray-400 text-xs">(Editado)</small>';
                    }
                    div.classList.add('bg-blue-50');
                    setTimeout(() => div.classList.remove('bg-blue-50'), 1000);
                }
            })
            .listen('.comentario.eliminado', (e) => {
                const div = document.getElementById(`comentario-${e.comentarioId}`);
                if (div) {
                    div.style.transition = 'opacity 0.5s'; div.style.opacity = '0';
                    setTimeout(() => {
                        div.remove();
                        const lista = document.getElementById('lista-comentarios');
                        if (lista && lista.children.length === 0) lista.innerHTML = '<p class="text-gray-500 italic text-center">No hay comentarios.</p>';
                    }, 500);
                }
            })
            .listen('.solicitud.actualizada', (e) => {
                const badge = document.getElementById(`estado-solicitud-${e.solicitud.id}`);
                if (badge) {
                    badge.innerText = e.solicitud.estado.toUpperCase();
                    let c = 'bg-gray-100';
                    if (e.solicitud.estado === 'pendiente') c = 'bg-red-100 text-red-800 border-red-200';
                    if (e.solicitud.estado === 'asignada') c = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                    if (e.solicitud.estado === 'resuelta') c = 'bg-green-100 text-green-800 border-green-200';
                    badge.className = `px-4 py-1 rounded-full text-sm font-bold border ${c}`;
                }
            });
    }
}