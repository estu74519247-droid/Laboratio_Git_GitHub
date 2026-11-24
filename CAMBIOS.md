# Cambios de la revisión (noviembre 2026)

## Seguridad

- `.htaccess` (raíz, `BD/`, `API_Citas_Medicas/`, `Doc_PDF/`): ya no se pueden descargar por URL .sql, .json, .md, .xlsx ni config.
- Contraseñas con hash bcrypt (pacientes, médicos, administradores). Migración automática de las antiguas.
- Médicos: ahora CMP + contraseña (inicial `Medico2026!`, se cambia desde su panel).
- Pacientes: contraseña propia en el registro (mín. 8), cambio de contraseña en «Mi perfil»; el DNI ya no se acepta como clave universal; eliminar cuenta pide la contraseña.
- Administradores: sin claves fijas en el código (un admin desactivado ya no entra).
- API Node: solo 127.0.0.1, sin CORS abierto, sin contraseñas (`usuarios.json` limpio).
- `consulta_dni.php`: límite 20/10 min por IP, sin `debug`, SSL verificado, sin clave «demo», no devuelve datos de DNI ya registrados.
- `config.php`: la URL de la API ya no depende del header Host.
- CSRF automático en formularios POST, límite de 5 intentos fallidos / 10 min en los 3 logins, cookie HttpOnly + SameSite.

## Lógica

- Reservas: valida fecha/hora futura, horario, y que médico–especialidad–centro coincidan; transacción con bloqueo del médico (sin dobles reservas).
- Horario liberado igual para `cancelada` y `reprogramada` en todas las pantallas.
- Reprogramar: transacción (si falla, la cita original no cambia); el médico ya no puede poner «reprogramada» a mano.
- `generarCodigo()` con orden numérico y reintento ante duplicado.
- Perfil: el seguro ya no se puede cambiar solo (define el costo).
- Admin: cancelar/atender validan el estado y registran el usuario; eliminar muestra el resultado real.
- Zona horaria America/Lima; puerto de MySQL en caché (5 min).
- Datos demo: citas pendientes con fechas futuras; README corregido (49 filas en centro_especialidad).

## Pendiente (no se pudo o no se debe cambiar solo)

- Contraseña de `root` en MySQL (configurar en `config.local.php`).
- `Doc_PDF` (PDF) sigue describiendo el acceso anterior; conviene regenerarlo.
- Códigos de cita: `codci` es CHAR(6) (máx. CIT999).
