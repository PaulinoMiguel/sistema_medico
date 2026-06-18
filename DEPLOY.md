# Despliegue al servidor

Pasos para migrar la app al servidor (piloto en paralelo desde el lunes 22-jun-2026).

## 1. Código
```bash
git pull                # o desplegar la rama feature/modo-paciente
composer install --no-dev --optimize-autoloader
```

## 2. Assets de producción (IMPORTANTE)
```bash
npm install
npm run build           # build, NO 'npm run dev'
```
> En producción el CSS va compilado en el `<head>`, así que todo lo de Alpine
> (buscadores, dropdowns dependientes, edición en línea) carga sincrónico.

## 3. Base de datos
```bash
php artisan migrate
```
Migra, entre otras: aseguradoras, procedimientos, cruce `procedure_insurer`,
`patients.insurer_id`, `consultations.clinical_summary`, y el permiso
`insurers.manage` (requiere que los roles ya existan).

## 4. Cargar el catálogo de procedimientos (503 códigos)
```bash
php artisan db:seed --class=ProcedureCatalogSeeder
```
Lee `database/seeders/data/procedimientos.csv` (ya versionado).
Alternativa: cargarlo desde la UI con **Administración → Procedimientos → Importar CSV**.

## 5. Storage y cachés
```bash
php artisan storage:link        # logos de impresión y fotos de pacientes
php artisan config:clear
php artisan route:clear
php artisan view:clear
# (si usas cachés en prod: config:cache / route:cache / view:cache)
```

## 6. Verificación post-deploy (smoke test)
- [ ] Login doctora y login secretaria.
- [ ] Administración → Procedimientos (54) y Aseguradoras (10) visibles para la secretaria.
- [ ] Ficha de paciente: "Aseguradora" como dropdown del catálogo.
- [ ] Consulta inicial → Resumen clínico: elegir aseguradora, buscar/marcar procedimientos, Guardar.
- [ ] Imprimir Resumen clínico: código correcto por ARS, sin columna "Simón", logo y firma OK.

## Notas del piloto
- **Pacientes existentes**: su aseguradora quedó como texto; `insurer_id` = null hasta
  re-editarlos. La aseguradora no se pre-selecciona en el Resumen clínico hasta asignarla.
- **Esquema**: desde el inicio del piloto, cambios de BD solo **aditivos y reversibles**.
- **Backups**: configurar respaldo diario de la BD durante la semana de prueba.
- **Fixes en caliente**: cada corrección se despliega con `git pull && php artisan migrate`
  (+ `npm run build` si tocó assets).
