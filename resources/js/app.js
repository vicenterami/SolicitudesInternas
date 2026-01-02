import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

if (window.Echo) {

    // 1 Canal de solicitudes (Tabla Dashboard)
    window.Echo.channel('solicitudes')
        .listen('.solicitud.creada', (e) => {
            console.log('Evento recibido:', e);

            const tablaBody = document.querySelector('table tbody');

            if (tablaBody) {
                // 1. Detectar si es Admin/Tecnico contando columnas de cabecera
                const headers = document.querySelectorAll('table thead th');
                const esAdminOTecnico = headers.length >= 5; // Asumimos que 5 columnas = tiene acciones

                // 2. Construir celda de acciones
                let celdaAcciones = '';
                if (esAdminOTecnico) {
                    celdaAcciones = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="/solicitudes/${e.solicitud.id}/editar" class="text-indigo-600 hover:text-indigo-900">
                                Gestionar
                            </a>
                        </td>
                    `;
                }

                // 3. Crear la fila con ESTILO EN LÍNEA para asegurar el amarillo
                // Usamos style="..." para saltarnos problemas de compilación de Tailwind
                const nuevaFilaHTML = `
                    <tr id="fila-${e.solicitud.id}" style="background-color: #fef9c3; transition: background-color 2s ease;">
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                            <a href="/solicitudes/${e.solicitud.id}" class="text-blue-600 hover:underline font-bold">
                                ${e.solicitud.titulo}
                            </a>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                            ${e.solicitud.creador ? e.solicitud.creador.name : 'Usuario ' + e.solicitud.user_id}
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                            <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                <span class="relative">${e.solicitud.estado}</span>
                            </span>
                        </td>
                        <td class="px-5 py-5 border-b border-gray-200 text-sm">
                            ${e.solicitud.tecnico ? e.solicitud.tecnico.name : 'Sin asignar'}
                        </td>
                        ${celdaAcciones}
                    </tr>
                `;

                // 4. Insertar
                tablaBody.insertAdjacentHTML('afterbegin', nuevaFilaHTML);

                // 5. Animación de desvanecimiento
                setTimeout(() => {
                    const fila = document.getElementById(`fila-${e.solicitud.id}`);
                    if (fila) {
                        fila.style.backgroundColor = 'white'; // Volver a blanco suavemente
                    }
                }, 2000); // Espera 2 segundos antes de desvanecer

            }
        });

    // 2. Canal de Comentarios (ACTUALIZADO VISUALMENTE)
    const solicitudData = document.getElementById('solicitud-data');
    
    if (solicitudData) {
        const solicitudId = solicitudData.dataset.id;
        
        // A) LÓGICA DE ENVÍO SIN RECARGA (AJAX)
        const form = document.getElementById('form-comentario');
        const inputComentario = document.getElementById('comentario');
        const btnEnviar = document.getElementById('btn-enviar');

        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // ¡ALTO! No recargues la página.

                const comentarioTexto = inputComentario.value;
                if (!comentarioTexto.trim()) return;

                // Desactivar botón para evitar doble clic
                btnEnviar.disabled = true;
                btnEnviar.innerText = 'Enviando...';

                // Usamos Axios (ya viene con Laravel)
                axios.post(`/solicitudes/${solicitudId}/comentarios`, {
                    comentario: comentarioTexto
                })
                .then(response => {
                    // Éxito: Limpiamos el campo
                    inputComentario.value = '';
                    // No agregamos el comentario aquí manualmente.
                    // Esperamos a que el WebSocket (o el Queue) nos avise para agregarlo.
                })
                .catch(error => {
                    console.error('Error enviando comentario:', error);
                    alert('Hubo un error al enviar el mensaje.');
                })
                .finally(() => {
                    // Reactivar botón
                    btnEnviar.disabled = false;
                    btnEnviar.innerText = 'Enviar Comentario';
                });
            });
        }

        // B) ESCUCHA DEL WEBSOCKET CON FILTRO DE DUPLICADOS
        window.Echo.channel(`solicitud.${solicitudId}`)
            .listen('.comentario.creado', (e) => {
                
                // 1. VERIFICACIÓN ANTI-DUPLICADOS
                // Si ya existe un div con este ID, no hacemos nada (evita dobles)
                if (document.getElementById(`comentario-${e.comentario.id}`)) {
                    return;
                }

                console.log('Comentario recibido:', e);

                const listaComentarios = document.getElementById('lista-comentarios');
                const mensajeVacio = document.getElementById('mensaje-sin-comentarios');

                if (mensajeVacio) mensajeVacio.remove();

                let rolTexto = 'Usuario';
                if (e.comentario.user.rol_id === 1) rolTexto = 'Admin';
                if (e.comentario.user.rol_id === 2) rolTexto = 'Técnico';

                // HTML con el ID ÚNICO INCLUIDO
                const nuevoComentarioHTML = `
                    <div id="comentario-${e.comentario.id}" class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500 animate-pulse transition-all duration-1000">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-gray-800">
                                ${e.comentario.user.name}
                                <span class="text-xs text-gray-500 font-normal">(${rolTexto})</span>
                            </span>
                            <span class="text-xs text-gray-400">Justo ahora</span>
                        </div>
                        <p class="text-gray-700">
                            ${e.comentario.comentario}
                        </p>
                    </div>
                `;

                listaComentarios.insertAdjacentHTML('beforeend', nuevoComentarioHTML);
                listaComentarios.scrollTop = listaComentarios.scrollHeight;

                setTimeout(() => {
                    const nuevoElemento = document.getElementById(`comentario-${e.comentario.id}`);
                    if (nuevoElemento) {
                        nuevoElemento.classList.remove('animate-pulse');
                    }
                }, 2500);
            });
    }
}