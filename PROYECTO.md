# Sistema Medico - Guia del Proyecto

## Descripcion General

Sistema de gestion para consultorios medicos desarrollado en **Laravel 12**. Modelo de negocio: software auto-hospedado, vendido por instalacion (pago unico + mantenimiento). Cada cliente recibe su propio servidor con su propia base de datos.

**Stack:** PHP 8.2+ | Laravel 12 | Livewire 4 | MariaDB 10.4 (XAMPP) | Tailwind CSS | DomPDF | Maatwebsite Excel

**Base de datos:** `mediapp` (localhost, root, sin password)

---

## Clientes Objetivo

| Perfil | Descripcion | Configuracion |
|--------|-------------|---------------|
| **Urologo (cliente inicial)** | 1 doctor, 2 clinicas, 2 secretarias (aisladas por clinica). Secretaria NO ve gastos ni resumen financiero | `secretary_limited` por clinica |
| **Consultorio 3 doctores** | 3 doctores comparten 1 clinica y 1 secretaria. Cada doctor tiene sus propios pacientes pero la secretaria ve todos | `doctor_associate` x3, `secretary_full` x1 |
| **Futuro** | Otras especialidades mas alla de urologia | Configurable via tabla `settings` (Fase 4) |

---

## Arquitectura

### Modelo de Tenencia
- **Servidor por cliente** (aislamiento a nivel de infraestructura)
- Una sola organizacion por instalacion (NO hay modelo Organization encima de Clinic)
- NO se usa `stancl/tenancy`
- Actualizacion: `git pull && php artisan migrate` por servidor

### Seguridad de Datos - MedicalRecordScope
Scope global aplicado a Patient, Appointment, Consultation, Payment y Service:
- **Doctores:** Solo ven registros propios (filtro por `doctor_id` o `primary_doctor_id`)
- **Secretarias/Enfermeras:** Ven registros de las clinicas a las que estan asignadas
- **CLI/Queue:** Sin filtro (para seeds, migraciones, jobs)

### Sistema de Permisos (Spatie v6)
5 roles con 69 permisos atomicos:

| Rol | Acceso |
|-----|--------|
| `doctor_admin` | Acceso total (dueno de clinica, gestiona staff, configuracion) |
| `doctor_associate` | Clinico + financiero, sin admin ni configuracion |
| `secretary_full` | Pacientes, turnos, consultas, recetas, gastos, caja |
| `secretary_limited` | Pacientes, turnos, consultas, caja. SIN ver gastos/cobros |
| `nurse` | Solo lectura: pacientes, turnos, consultas |

### Middleware
- `admin.auth` - Rutas de super admin
- `clinic.required` - Rutas operativas requieren clinica activa en sesion
- `permission:xxx` - Permisos granulares por ruta (Spatie)

---

## Estructura del Proyecto

### Modelos (15)
```
User                  - Doctores, secretarias, enfermeras (HasRoles de Spatie)
Admin                 - Super admin (guard separado)
Patient               - Pacientes (primary_doctor_id, clinic pivot)
Appointment           - Turnos/citas
Consultation          - Consultas medicas (SOAP, signos vitales JSON)
Prescription          - Recetas (auto-numeradas RX-YYYYMMDD-####)
PrescriptionItem      - Items individuales de receta
Clinic                - Clinicas (settings JSON)
Service               - Catalogo de servicios (personal por doctor)
Payment               - Cobros (vinculado a doctor, paciente, servicio, caja)
CashRegister          - Sesiones de caja (apertura/cierre)
Expense               - Gastos
ExpenseCategory       - Categorias de gastos
PatientMedicalHistory - Historia clinica (campos JSON)
AuditLog              - Auditoria (morphable)
```

### Controladores (20)
```
/Controllers/
  DashboardController        - Dashboard + seleccion de clinica
  PatientController          - CRUD pacientes + historia clinica
  AppointmentController      - CRUD turnos + cambio de estado
  ConsultationController     - CRUD consultas (crear desde turno)
  PrescriptionController     - CRUD recetas + PDF (crear desde consulta)
  ClinicController           - Gestion de clinicas
  ServiceController          - Catalogo de servicios + quick store
  PaymentController          - Cobros
  CashRegisterController     - Apertura/cierre de caja
  ExpenseController          - Gastos + resumen financiero
  ExpenseCategoryController  - Categorias de gastos + quick store
  RoleController              - CRUD roles + asignacion de rol a staff
  SecretaryController        - CRUD secretarias + toggle estado
  ProfileController          - Perfil, foto, password

/Controllers/Admin/
  AdminAuthController        - Login/logout super admin
  AdminDashboardController   - Dashboard super admin
  AdminDoctorController      - Gestion de doctores

/Controllers/Auth/
  LoginController            - Login/logout usuarios
```

### Migraciones (22)
Ultimas migraciones (Fase 1 y 2):
- `2026_04_11_000001` - Agrega `primary_doctor_id` a patients
- `2026_04_12_000001` - Elimina columna `role` enum de users
- `2026_04_12_002741` - Tablas de Spatie Permission
- `2026_04_12_010000` - Agrega `doctor_id` a payments
- `2026_04_12_020000` - Reemplaza `is_active` por `status` enum en users
- `2026_04_12_030000` - Servicios personales por doctor

### Vistas (53 archivos Blade)
```
/views/
  admin/          - Super admin (login, dashboard, doctores, perfil)
  appointments/   - CRUD turnos
  auth/           - Login usuario
  cash-registers/ - Listado y detalle de caja
  clinics/        - CRUD clinicas
  consultations/  - CRUD consultas
  expense-categories/ - Gestion categorias
  expenses/       - CRUD gastos + resumen
  layouts/        - Layout principal (tenant.blade.php)
  patients/       - CRUD pacientes
  payments/       - Cobros
  prescriptions/  - CRUD recetas + PDF
  profile/        - Edicion de perfil
  roles/          - CRUD roles y permisos (index, create, edit)
  secretaries/    - CRUD secretarias
  services/       - Catalogo servicios
  dashboard.blade.php
  dashboard-setup.blade.php
```

---

## Plan de Implementacion - 7 Fases

### Fase 1 - Ownership Paciente-Doctor ✅
**Commit:** `48cd9a0` (Mar 30, 2026)

Implementado:
- [x] `primary_doctor_id` en tabla patients
- [x] Relacion `Patient → primaryDoctor()` y `User → patients()`
- [x] `MedicalRecordScope` global en Patient, Appointment, Consultation
- [x] Rediseno del menu/sidebar
- [x] Traducciones al espanol

### Fase 2 - Permisos Granulares con Spatie ✅
**Commit:** `4baaa7e` (Abr 11, 2026)

Implementado:
- [x] Instalacion y configuracion de `spatie/laravel-permission` v6
- [x] 69 permisos atomicos organizados por modulo
- [x] 5 roles (doctor_admin, doctor_associate, secretary_full, secretary_limited, nurse)
- [x] Eliminacion del enum `role` y middleware `CheckRole`
- [x] Middleware `permission:xxx` en todas las rutas operativas
- [x] Estados de usuario: active / passive / inactive (reemplaza `is_active`)
- [x] `doctor_id` en payments (cobros atribuidos al doctor)
- [x] Servicios personales por doctor
- [x] Seeder con datos de prueba (escenario urologo + multi-doctor)

### Fase 3 - UI de Configuracion de Roles y Permisos ✅
**Commit:** pendiente (Abr 12, 2026)

Implementado:
- [x] `RoleController` con CRUD de roles + asignacion de rol a usuarios
- [x] Vista index: tabla de roles (sistema/editable/personalizado) + asignacion de roles a staff
- [x] Vista create: formulario con permisos agrupados por modulo + toggle all
- [x] Vista edit: edicion de permisos por rol (nombre protegido en roles predefinidos)
- [x] Sidebar: link "Roles y permisos" en seccion Administracion
- [x] 69 permisos con etiquetas en espanol organizados en 13 modulos
- [x] Proteccion: roles de doctor no editables, roles predefinidos no eliminables
- [x] Bug fix: secretaria ahora ve "Registrar cobro" en sidebar (payments.create sin payments.view)
- [x] Bug fix: boton "Pasar a consulta" para secretarias (antes decia "Iniciar consulta")

### Fase 4 - Especialidad Configurable por Medico ✅
**Commit:** pendiente (Abr 12, 2026)

Implementado:
- [x] `config/specialties.php` con definicion de especialidades (urologia, pediatria, neurologia, general)
- [x] Migracion: columnas `specialty_data` JSON y `neurological_exam` en consultations
- [x] Enum `type` expandido con tipos de pediatria (well_child, vaccination)
- [x] Partials Blade por especialidad: symptoms + exams + show (edit y lectura)
- [x] Urologia: sintomas urinarios, funcion sexual, examen GU, tacto rectal
- [x] Pediatria: crecimiento, desarrollo psicomotor, alimentacion, vacunacion
- [x] Neurologia: sintomas neurologicos, Glasgow, NIHSS, examen neurologico
- [x] Consulta carga dinamicamente los campos segun la especialidad del doctor
- [x] Selector de especialidad estandarizado (dropdown) en panel admin de doctores
- [x] Especialidad por medico (no por clinica ni instalacion)

### Fase 5 - Comando de Instalacion
**Objetivo:**
- Comando `php artisan medicalsystem:install` interactivo
- Pregunta perfil (doctor individual, consultorio multi-doctor, etc.)
- Siembra roles, permisos y configuracion por defecto segun perfil elegido
- Seeders diferenciados por tipo de instalacion

### Fase 6 - Branding y Modulos Opcionales (Opcional)
**Objetivo:**
- Personalizacion visual por instalacion (logo, colores, nombre)
- Toggle de modulos opcionales (recetas, gastos, caja, etc.)

### Fase 7 - Modelo Contable Multi-Doctor
**Objetivo:** Separacion financiera por doctor con gastos compartidos.

**Schema:**
- `expenses.owner_doctor_id` (nullable) - NULL = compartido, valor = personal
- `clinics.expense_split_method` - enum: equal | percentage | by_income
- `clinics.expense_split_config` - JSON con porcentajes por doctor

**Logica del neto personal:**
```
mis_ingresos       = SUM(payments WHERE doctor_id = me)
mis_gastos_propios = SUM(expenses WHERE owner_doctor_id = me)
mi_pool_compartido = SUM(expenses WHERE owner_doctor_id IS NULL) x mi_porcentaje
mi_neto            = mis_ingresos - mis_gastos_propios - mi_pool_compartido
```

**Menu:**
- "Mi resumen" - neto personal del doctor logueado
- "Gastos compartidos" - desglose del pool

**Fuera de scope:** Entidad legal de clinica, IVA/retenciones, facturacion electronica, cuentas por cobrar, sociedades formales.

### Fase 8 - Plantillas de consulta personalizadas por doctor
**Motivacion:** Dos doctores de la misma especialidad pueden tener cuestionarios distintos (ej. Dra. Herrera pide un set propio, otro urologo usa el generico). Queremos asignar una plantilla concreta a cada doctor al crearlo.

**Schema:**
- `users.consultation_template` VARCHAR nullable - slug del template que usa ese doctor (fallback al generico de su especialidad si es NULL).
- `consultations.consultation_template` VARCHAR nullable - snapshot del template al momento de crear la consulta, para que cambios futuros no rompan historiales viejos.

**Registro:** `config/consultation_templates.php` lista templates disponibles:
```php
'urology_generic'    => ['label' => 'Urologia generico',        'specialty' => 'urology'],
'urology_herrera'    => ['label' => 'Urologia - Dra. Herrera',  'specialty' => 'urology'],
'pediatrics_generic' => ['label' => 'Pediatria generico',       'specialty' => 'pediatrics'],
```

**Blade:** Cada template tiene sus 4 partials en `resources/views/consultations/partials/`:
- `{template}-symptoms.blade.php`
- `{template}-exams.blade.php`
- `{template}-show-subjective.blade.php`
- `{template}-show-objective.blade.php`

**UI:** En `/admin/doctors/{edit,create}` agregar dropdown "Plantilla de consulta" que filtra por la especialidad elegida. Consulta mira `consultation_template` (con fallback a `specialty` + `'_generic'`).

**Datos custom:** Siguen guardados en `consultations.specialty_data` JSON, sin cambios.

**Alcance:**
- El dev (yo) escribe cada template custom a pedido (~30 min-1h por template), commitea y deploya.
- No es self-service: no hay form builder UI para el cliente.
- Todos los templates viven en el repo unico y se distribuyen a todas las instalaciones; cada install solo usa los que asigna a sus doctores.

**Fuera de scope:** Form builder drag-and-drop, templates creables desde UI, schema JSON dinamico.

---

## Seeders y Datos de Prueba

El `DatabaseSeeder` crea dos escenarios completos:

**Escenario 1 - Urologo:**
- 1 doctor_admin
- 2 clinicas
- 2 secretarias (una por clinica, `secretary_limited`)

**Escenario 2 - Consultorio compartido:**
- 3 doctores (1 admin + 2 associate)
- 1 clinica compartida
- 1 secretaria (`secretary_full`)

**Super Admin:** admin@mediapp.local

---

## Comandos Utiles

```bash
# Setup inicial
composer setup

# Desarrollo (servidor + queue + logs + vite)
composer dev

# Migrar base de datos
php artisan migrate

# Re-sembrar datos de prueba
php artisan migrate:fresh --seed

# Tests
composer test

# Linting
./vendor/bin/pint
```

---

## Relaciones Clave entre Entidades

```
User (doctor) ──┬── hasMany ──→ Patient (via primary_doctor_id)
                ├── hasMany ──→ Service (catalogo personal)
                ├── hasMany ──→ Payment (cobros)
                └── belongsToMany ──→ Clinic (pivot: clinic_user)

Patient ──┬── belongsToMany ──→ Clinic (pivot: clinic_patient)
          ├── hasMany ──→ Appointment
          ├── hasMany ──→ Prescription
          └── hasOne ──→ PatientMedicalHistory

Appointment ──→ hasOne ──→ Consultation ──→ hasMany ──→ Prescription

Clinic ──┬── hasMany ──→ Appointment
         ├── hasMany ──→ CashRegister
         ├── hasMany ──→ Expense
         └── hasMany ──→ ExpenseCategory

CashRegister ──→ hasMany ──→ Payment
```

---

## Flujo Operativo Recomendado

### Atencion diaria (turno → consulta → receta)

**Secretaria:**
1. Registra el turno del paciente
2. Cuando el paciente llega → "Paciente llego" (status: `in_waiting_room`)
3. Cuando toca pasar → "Pasar a consulta" (status: `in_progress`)
4. Registra cobros de consulta desde **Caja**

**Doctora:**
1. Ve sus turnos del dia → entra al turno que esta "en curso"
2. "Iniciar consulta" → crea la consulta vinculada al turno
3. Atiende, llena la consulta, crea receta si necesita (boton "Crear receta" en la consulta)
4. "Firmar y cerrar" → consulta firmada + turno completado automaticamente

**Importante:** El turno es el punto de entrada para el flujo normal. Al firmar la consulta, el turno se cierra automaticamente. Crear consulta directamente (sin turno) es solo para excepciones como atenciones de emergencia.

### Cobros — dos canales separados

| Canal | Quien | Donde | Pasa por caja |
|-------|-------|-------|---------------|
| **Caja** | Secretaria | Desde "Caja" en sidebar | Si |
| **Mis cobros** | Doctora | Desde "Ingresos > Mis cobros" | No |

La secretaria NO ve los cobros personales de la doctora. Dos personas no alimentan la misma caja.

---

## Notas de Despliegue

- Cada cliente tiene su propio servidor
- No hay sistema de licencias (pago unico)
- El desarrollador administra los servidores de los clientes
- Actualizacion manual: `git pull && php artisan migrate`
- Base de datos en pre-produccion: se puede hacer refactoring destructivo
