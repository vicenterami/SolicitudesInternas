import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

if (window.Echo) {

    // 1 Canal de solicitudes (Tabla Dashboard)
    window.Echo.channel('solicitudes')
        .listen('.solicitud.creada', (e) => {

            // Verificamos si ya existe una fila con ese ID. Si existe, no hacemos NADA.
            if (document.getElementById(`fila-${e.solicitud.id}`)) {
                return; 
            }

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

    // 3. ACTUALIZACIÓN EN TIEMPO REAL (SHOW Y DASHBOARD)
    
    // A) Si estamos viendo una solicitud específica (show.blade.php)
    if (solicitudData) {
        const idSolicitudVista = solicitudData.dataset.id;
        
        window.Echo.channel(`solicitud.${idSolicitudVista}`)
            .listen('.solicitud.actualizada', (e) => {
                console.log('Solicitud actualizada:', e);

                // 1. Actualizar Estado visualmente
                const badgeEstado = document.getElementById(`estado-solicitud-${e.solicitud.id}`);
                if (badgeEstado) {
                    badgeEstado.innerText = e.solicitud.estado.toUpperCase();
                    // Opcional: Aquí podrías agregar lógica para cambiar el color (bg-green-200, bg-red-200) según el estado
                }

                // 2. Actualizar Técnico asignado
                const textoTecnico = document.getElementById(`tecnico-solicitud-${e.solicitud.id}`);
                if (textoTecnico) {
                    textoTecnico.innerText = e.solicitud.tecnico ? e.solicitud.tecnico.name : 'Sin asignar';
                    
                    // Efecto visual de parpadeo amarillo para que el usuario note el cambio
                    textoTecnico.parentElement.style.backgroundColor = '#fef9c3';
                    setTimeout(() => textoTecnico.parentElement.style.backgroundColor = 'transparent', 2000);
                }
            });
    }

    // B) Escuchar actualizaciones globales para el Dashboard (Contadores)
    // Nota: Para actualizar el GRÁFICO necesitaríamos recalcular todo. 
    // Por ahora haremos un truco simple: Recargar la página si cambian los stats O incrementar manualmente.
    // Para simplificar tu aprendizaje actual, haremos que si llega una nueva solicitud, aumente el contador Total y Pendientes.

    window.Echo.channel('solicitudes')
        .listen('.solicitud.creada', (e) => {
            // Incrementar contadores del Dashboard si existen en pantalla
            const totalEl = document.getElementById('dash-total');
            const pendientesEl = document.getElementById('dash-pendientes');
            
            if (totalEl) totalEl.innerText = parseInt(totalEl.innerText) + 1;
            if (pendientesEl) pendientesEl.innerText = parseInt(pendientesEl.innerText) + 1;
            
            // Actualizar gráfico (Solo si existe la variable del gráfico)
            // Esto es un truco: enviamos datos falsos visuales o recargamos. 
            // Para hacerlo pro, deberías recibir los nuevos datos del gráfico en el evento.
        })
        .listen('.solicitud.actualizada', (e) => {
             // Si cambia de estado, lo ideal sería recargar los contadores.
             // Como es complejo calcular restar uno de pendientes y sumar uno a asignado en JS puro,
             // una opción válida en sistemas reactivos simples es recargar el dashboard suavemente
             // o simplemente dejar que el usuario refresque para ver los números globales exactos.
        });
}