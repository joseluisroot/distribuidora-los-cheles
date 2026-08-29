# Tablas, confirmaciones y avisos

Integración inicial en /admin/accesos. DataTables 3.0.2 y SweetAlert2 11.26.25 instalados desde el canal latest de npm y fijados en package.json/package-lock.json. Fuentes: https://datatables.net/manual/core/installation y https://sweetalert2.github.io/.

- Búsqueda, orden y paginación en español sobre los registros cargados. La auditoría sigue limitada a los últimos 50 registros, no es búsqueda de todo el historial. No se habilita persistencia en localStorage ni exportación.
- Confirmación previa al POST de permisos mostrando acción, destino, ámbito y motivo como texto, nunca HTML. Cancelar no envía; confirmar mantiene CSRF y controles del servidor. Sin SweetAlert se usa confirmación nativa; sin JavaScript permanece el formulario HTML y la autorización del servidor.
- Éxitos como toast con cierre explícito y errores como alerta; se conserva el aviso persistente en la página. La confirmación no anuncia éxito: este proviene de la respuesta del servidor.
- No se implementan notificaciones push ni se solicitan permisos del navegador. Esta integración no se ha extendido aún a todos los módulos.

## Archivos y despliegue

Ejecutar `npm ci --ignore-scripts` y `npm run assets:ui` en desarrollo para reproducir los archivos de public/assets/vendor, incluidas licencias. Se sirven localmente, sin CDN para estas dos librerías. HostGator solo necesita los archivos públicos generados junto con PHP; no necesita ejecutar Node en producción. No se ha desplegado.

## Verificación y pendientes

41 pruebas PHP / 140 aserciones y 4 pruebas JavaScript correctas. Las pruebas JS usan dobles del DOM/SweetAlert para comprobar cancelar, confirmar, fallback, tratamiento como texto y configuración; no equivalen a pruebas de navegador. Revisión visual e interacción real de DataTables/SweetAlert pendientes en escritorio y móvil, sin cambiar permisos reales para probar.

La auditoría npm en línea detectó 2 dependencias previas con severidad alta: nanoid y postcss. No son DataTables ni SweetAlert2. Permanecen pendientes de actualizar y verificar dentro de I1. Una auditoría offline devolvió cero pero no se considera evidencia vigente. No afirmar seguridad completa ni cerrar I1 por esta integración.
