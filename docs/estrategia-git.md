# Estrategia de ramas por incremento

## Convención acordada

- `main`: base integrada y verificada. No desarrollar directamente en ella. No equivale por sí sola a una versión desplegada.
- `feature/{nombre-incremento}`: rama de trabajo de cada incremento, por ejemplo `feature/i2-inventario-trazable` o `feature/i3-traslados`.
- Commits pequeños por propósito: migraciones/modelo, lógica, interfaz, pruebas y documentación cuando esa separación deje cambios comprensibles. No subir secretos ni archivos operativos.
- Confirmado por el usuario: toda integración de un incremento a `main` se realiza mediante pull request (PR), nunca mediante merge directo local ni push directo a main. El PR debe incluir alcance, pruebas realizadas y pendientes; se integra después de revisión y aprobación. Desplegar es una acción separada y requiere autorización.
- Crear el siguiente incremento desde main después de integrar su dependencia. Si se necesita comenzar antes, usar una rama dependiente con base explícita en la rama anterior y documentar esa dependencia en el PR; no presentar su diff como independiente.

## Transición del trabajo acumulado

Se creó y seleccionó `feature/i2-inventario-trazable` desde el HEAD local de main (`3ba36f1`). Se conservaron el índice y los cambios sin commit, incluyendo I1 e I2. No se hizo commit, push, merge ni reescritura del historial. La referencia local main no avanzó. No se consultó ni actualizó el estado remoto con fetch.

La rama no constituye un respaldo del trabajo sin commit. Antes de continuar con nuevas funciones: revisar el diff, separar lo que sea viable en commits coherentes de I1 y de la base I2, y excluir datos locales. El PR inicial debe declarar que contiene la base acumulada; no afirmar que I1 ya fue integrado o que I2 está completo. No forzar una separación que produzca commits incompatibles.

Se añadió node_modules/ al ignore. Mantener package-lock.json y composer.lock; revisar los assets compilados necesarios para HostGator. Revisar especialmente tmp/, output/, public/uploads/ y archivos nuevos antes de agregarlos; no usar git add . sin esa revisión. Nunca incluir .env, credenciales, sesiones ni respaldos con datos reales.

## Comprobaciones por entrega

`git status`, revisión de diff e índice, `npm run build`, pruebas PHP/JS aplicables, prueba de migraciones en base aislada y revisión visual cuando cambie la interfaz. Documentar verificaciones pendientes, sin declarar I1 cerrado mientras falten sus criterios.

Los commits y la publicación remota se realizarán cuando se soliciten; no se automatiza push a main ni se han configurado protecciones remotas. Se recomienda proteger main para exigir revisión y checks antes de integrar.
