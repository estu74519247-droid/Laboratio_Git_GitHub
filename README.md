# PrimeLab — Sistema de Reserva de Citas Médicas

Aplicación web académica para la **reserva y el seguimiento de citas médicas** en centros de salud públicos del Perú. Integra tres perfiles (paciente, médico y administrador) sobre **PHP + MySQL/MariaDB + Apache (XAMPP)** y una interfaz accesible y responsiva.

> Versión preparada para la **Semana de Innovación 2026**. Incluye 64 pacientes, 17 médicos, 3 administradores, base de datos auto-reparable y despliegue portable en cualquier IP y puerto de XAMPP.

---

## 1. Características principales

### Pacientes

- Registro con **DNI**, autocompletado de nombres y apellidos y **contraseña propia** (mínimo 8 caracteres).
- Inicio de sesión con **DNI + contraseña**; la contraseña se puede cambiar desde «Mi perfil».
- Reserva de citas por centro, especialidad, médico, fecha y hora.
- Cálculo del costo según seguro: **SIS S/ 0.00 · EPS S/ 20.00 · Privado S/ 50.00**.
- Historial de citas, **ticket imprimible** y perfil con edición controlada.
- El ticket muestra diagnóstico, indicaciones, medicamentos y reprogramaciones registradas por el médico.

### Médicos

- Acceso mediante **CMP + contraseña** (solo ven sus propias citas). La contraseña inicial de demostración es `Medico2026!` y se cambia desde el panel.
- Registro del estado de la cita: **pendiente, atendida, cancelada o extendida**.
- Registro de la **descripción de lo observado al paciente** (diagnóstico/evaluación).
- Registro de **indicaciones y medicamentos** entregados o recetados.
- **Reprogramación** de una cita creando una nueva con el **mismo médico, paciente y centro**, con fecha, hora y motivo.
- Motivos admitidos: inasistencia del paciente, cancelación, seguimiento del tratamiento, evaluación de resultados de exámenes, error administrativo del sistema u otro.
- Validación de disponibilidad del horario y historial de cada acción.

### Administración

- Panel con indicadores: citas totales, pendientes, atendidas, extendidas, reprogramadas y canceladas.
- Gestión de citas, médicos, centros, especialidades y consultas.
- **Tres cuentas de administrador** listas para la demostración.

### Autocompletado de DNI (cascada)

1. (Si el DNI ya tiene cuenta solo se avisa; no se devuelven sus datos.)
2. **Directorio local** `API_Citas_Medicas/data/usuarios.json` (funciona sin la API Node.js y sin internet).
3. API interna `API_Citas_Medicas` (Node.js + Express), si está encendida.
4. Servicios públicos de consulta RENIEC (SUNAT, apis.net.pe, miapi.cloud, apiperu.dev, perudevs, reniec.cloud).

> RENIEC no ofrece una API pública oficial, gratuita y abierta. Si los servicios externos no responden, el sistema lo informa y permite completar los nombres manualmente.

---

## 2. Inicio rápido

1. Inicia **Apache** y **MySQL/MariaDB** desde XAMPP.
2. Copia la carpeta `PrimeLab` dentro de `htdocs`:
   ```
   C:\xampp\htdocs\PrimeLab
   ```
3. Importa la base de datos (opcional, recomendado para la demo):
   - Abre `http://localhost/phpmyadmin`
   - Importa `PrimeLab/BD/bd_primelab.sql`
4. Abre el sistema:
   ```
   http://localhost/PrimeLab/
   ```
   También funciona con otra IP o puerto:
   ```
   http://192.168.1.50/PrimeLab/
   http://localhost:8080/PrimeLab/
   ```

> No uses Live Server: solo sirve archivos estáticos y no ejecuta PHP.

### Configuración automática

- El puerto de MySQL se detecta probando **3306, 3307, 3308, 3309 y 3009**.
- Si la base `bd_primelab` no existe, se **crea automáticamente**.
- Si la tabla `cita` es antigua, se **agregan las columnas faltantes** (`diagnostico`, `indicaciones`, `motivo_reprogramacion`, `reprogramada_de`, `observaciones`).
- La URL de la API se arma con el **host de la petición** y el puerto 3000.
- Los enlaces del sitio son **relativos**: no dependen de una IP ni de un puerto fijos.

Para fijar valores manualmente, crea `config.local.php`:

```php
<?php
define('PRIMELAB_DB_HOST', '127.0.0.1');
define('PRIMELAB_DB_PORT', 3009);
define('PRIMELAB_DB_USER', 'root');
define('PRIMELAB_DB_PASS', '');
define('PRIMELAB_DB_NAME', 'bd_primelab');
// define('PRIMELAB_API_URL', 'http://127.0.0.1:3000');
```

También se admiten variables de entorno con los mismos nombres.

---

## 3. Credenciales de prueba

### Administradores

| Usuario  | Correo                    | Contraseña     |
| -------- | ------------------------- | -------------- |
| `prime`  | `primelab@centrosalud.pe` | `pr1m3l4b`     |
| `admin2` | `gestion@centrosalud.pe`  | `PrimeLab2026` |
| `admin3` | `soporte@centrosalud.pe`  | `PrimeLab2026` |

### Médicos (CMP + contraseña inicial `Medico2026!`)

| CMP     | Médico                              | Especialidad     |
| ------- | ----------------------------------- | ---------------- |
| `10234` | Dra. Ana Torres Mendoza             | Medicina General |
| `18542` | Dr. Luis Alberto Mendoza Quispe     | Cardiología      |
| `21306` | Dra. Rosa María Salazar Huamán      | Pediatría        |
| `45876` | Dra. Gabriela Inés Ortiz Villanueva | Medicina Interna |

El listado completo (MED001–MED017) está en `Usuarios_PrimeLab.xlsx` y en la tabla `medico`.

### Pacientes de demostración (contraseña inicial = DNI)

| DNI        | Paciente                               | Seguro  |
| ---------- | -------------------------------------- | ------- |
| `45678912` | Ana Torres Mendoza                     | SIS     |
| `41234567` | Luis Ramos Cárdenas                    | EPS     |
| `61161158` | Eidan Valencia Zamora                  | Privado |
| `10000000` | Ana García Chávez                      | SIS     |
| `10000049` | Héctor Núñez Ramírez                   | SIS     |
| `61216468` | Ademir Andrew Alberto Sanchez Chinchay | SIS     |

- Los DNI **10000000–10000049** corresponden a los 50 usuarios de `usuarios.json`.
- Los DNI **61216468, 74519247, 72850117, 75532819, 72484597, 72689307 y 61161158** son cuentas de estudiante.
- Solo las cuentas de demostración del script SQL tienen como contraseña inicial su **DNI**; cámbiala desde «Mi perfil». Las cuentas nuevas eligen su propia contraseña al registrarse.
- Todas las contraseñas (pacientes, médicos y administradores) se guardan con **hash bcrypt**; `usuarios.json` ya no contiene contraseñas.

El directorio completo está en **`Usuarios_PrimeLab.xlsx`** (hojas Accesos, Resumen e Instrucciones).

---

## 4. Estructura del proyecto

```
PrimeLab/
├── admin_login.html          Acceso administrativo
├── admin_citas.php           Gestión de citas
├── admin_consultas.php       Consultas del sistema
├── admin_medicos.php         Gestión de médicos
├── admin_centros.php         Gestión de centros
├── index.html                Acceso de pacientes
├── registro.html / .php      Registro de pacientes
├── login_paciente.php        Validación de pacientes
├── login.php                 Validación de administradores
├── medico_login.html         Acceso de médicos
├── login_medico.php          Validación por CMP
├── panel.php                 Panel del paciente
├── panel_medico.php          Panel y gestión clínica del médico
├── reservar.php              Reserva de citas
├── mis_citas.php             Historial del paciente
├── ticket.php                Comprobante de la cita
├── perfil.php                Perfil del paciente
├── consultas.php             Consultas médicas
├── consulta_dni.php          Endpoint JSON de consulta de DNI
├── file_conexion.php         Conexión, autenticación y auto-reparación
├── config.php                Configuración portable
├── BD/
│   └── bd_primelab.sql       Base completa (64 pacientes, 17 médicos, 3 admins)
├── API_Citas_Medicas/        API opcional (Node.js + Express)
├── css/style.css
├── js/main.js
├── img/                      Logotipo y avatares
├── Usuarios_PrimeLab.xlsx
├── Doc_PDF/                  Documentación formal (PDF)
└── README.md
```

---

## 5. Base de datos

- Nombre: **`bd_primelab`**
- Motor: MySQL / MariaDB (InnoDB, `utf8mb4_spanish_ci`)
- Script completo: **`BD/bd_primelab.sql`**

| Tabla                 | Registros |
| --------------------- | --------- |
| `centro`              | 8         |
| `especialidad`        | 17        |
| `centro_especialidad` | 49        |
| `medico`              | 17        |
| `paciente`            | 64        |
| `cita`                | 10        |
| `cita_historial`      | 1+        |
| `administrador`       | 3         |

### Esquema auto-reparable

`file_conexion.php` ejecuta `asegurarEsquemaPaciente()`, `asegurarEsquemaMedico()`, `asegurarEsquemaCita()` y `asegurarAdministradores()` al conectarse (también convierte a hash cualquier contraseña antigua en texto plano). Esto permite abrir una base creada con una versión anterior sin errores fatales y sin reimportar manualmente.

---

## 6. API interna opcional (Node.js)

La carpeta `API_Citas_Medicas` contiene una API educativa con Express y un archivo JSON.

```bash
cd API_Citas_Medicas
npm install
npm start
```

- URL base: `http://127.0.0.1:3000` (la API **solo escucha en esta máquina**, no tiene CORS abierto y no guarda contraseñas).
- Endpoints: `/api/usuarios`, `/api/usuarios/:id`, `/api/usuarios/buscar?dni=...`
- El sistema **no depende** de esta API para funcionar: el directorio local `API_Citas_Medicas/data/usuarios.json` cubre la consulta de DNI.

---

## 7. Diagnóstico de la consulta de DNI

Prueba directa del endpoint:

```
http://localhost/PrimeLab/consulta_dni.php?dni=10000000
```

La respuesta incluye `ok`, `nombres`, `apellidos` y `mensaje`. El endpoint limita a **20 consultas cada 10 minutos por IP** y ya no tiene modo `debug`.

Requisitos para las fuentes externas:

- Conexión a internet.
- Extensión `curl` habilitada en PHP.
- Acceso saliente permitido desde el servidor.
- Certificados SSL raíz configurados en PHP/cURL. Si tu XAMPP no los tiene, define `PRIMELAB_SSL_VERIFY=false` **solo** en `config.local.php` para pruebas locales.

---

## 8. Seguridad y buenas prácticas

- Las consultas usan **sentencias preparadas**.
- La salida HTML se escapa con `htmlspecialchars`.
- Las sesiones se regeneran al iniciar sesión.
- El médico solo puede operar sobre **sus propias citas**.
- Contraseñas con `password_hash()` / `password_verify()` (bcrypt).
- Máximo **5 intentos fallidos cada 10 minutos** por IP y usuario en los tres accesos.
- **Token CSRF** automático en todos los formularios POST de una sesión iniciada.
- Cookie de sesión `HttpOnly` y `SameSite=Lax`.
- `.htaccess` bloquea la descarga por URL de `.sql`, `.json`, `.md`, `.xlsx`, `BD/`, `API_Citas_Medicas/`, `Doc_PDF/` y de `config*.php` / `file_conexion.php` (Apache debe tener `AllowOverride All`, que es el valor de XAMPP).
- Un administrador desactivado o eliminado ya no puede entrar (no hay claves fijas en el código).
- Las reservas se validan en el servidor (fecha futura, horario, médico/especialidad/centro) y se guardan en una transacción para evitar dobles reservas.
- El seguro del paciente no se puede cambiar desde el perfil (define el costo de la cita).
- Antes de publicar el sistema: cambia las claves de demostración, pon contraseña al usuario `root` de MySQL (`config.local.php`) y restringe phpMyAdmin.

---

## 9. Solución de problemas

| Síntoma                             | Causa probable                                                          | Solución                                                                                 |
| ----------------------------------- | ----------------------------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| `Unknown column 'c.diagnostico'`    | Base de datos antigua                                                   | El sistema la repara solo al conectarse; si persiste, importa `BD/bd_primelab.sql`.      |
| `Error de conexión a MySQL`         | MySQL apagado o puerto distinto                                         | Inicia MySQL en XAMPP; el sistema prueba 3306–3309 y 3009.                               |
| No aparecen nombres al buscar DNI   | DNI ajeno al directorio, fuentes externas caídas o sin certificados SSL | Verifica internet, `curl` y certificados; completa los nombres manualmente.              |
| «Demasiados intentos fallidos»      | Se superó el límite de 5 intentos en 10 minutos                         | Espera 10 minutos (o borra la carpeta `primelab_rl` de la carpeta temporal del sistema). |
| «Solicitud no válida (token…)»      | La página llevaba mucho tiempo abierta o la sesión expiró               | Recarga la página e inténtalo de nuevo.                                                  |
| Los enlaces llevan a otra dirección | Se abrió con Live Server                                                | Usa Apache (`http://localhost/PrimeLab/`).                                               |
| La API no responde                  | Node.js apagado                                                         | No es obligatorio: el directorio local cubre la consulta.                                |

---

## 10. Documentación adicional

- **`Doc_PDF/SIT2026-Documentación_PrimeLab.pdf`** — documentación formal del proceso, arquitectura, modelo de datos, flujos y pruebas.
- **`Usuarios_PrimeLab.xlsx`** — directorio de accesos de demostración.
- **`BD/bd_primelab.sql`** — base de datos completa lista para importar.

---

Proyecto académico. Los datos mostrados son de ejemplo.
