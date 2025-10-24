# 🚀 Workflows CI/CD

## 📋 qa-cicd.yml

Workflow automatizado para el ambiente de QA (Quality Assurance).

### 🎯 Triggers

El workflow se ejecuta en los siguientes eventos:

1. **Push a rama `qa`**
   - Ejecuta: Tests → Scan → Build → Deploy

2. **Pull Request a `qa`**
   - Ejecuta: Tests → Scan
   - NO hace build ni deploy
   - Genera reporte de cobertura en el PR

3. **PR Review (aprobado)**
   - Ejecuta: Tests → Scan → Build
   - NO hace deploy automático

4. **Manual (workflow_dispatch)**
   - Permite ejecutar manualmente desde GitHub
   - Ejecuta el flujo completo

### 📊 Jobs

#### 1. **meta** - Preparación
- Genera un ID único de deploy
- Formato: `YYYYMMDDHHMMSS-RUN_NUMBER-SHA`

#### 2. **test** - Tests con PHPUnit
- ✅ Ejecuta todos los tests
- 📊 Genera reporte de cobertura HTML
- 📈 Genera reporte Clover XML
- 💬 Comenta en PR con porcentaje de cobertura
- 📤 Sube artifacts: JUnit, Coverage HTML, Coverage XML

#### 3. **scan** - Análisis de Seguridad
- 🔍 Escanea con Trivy (CRITICAL, HIGH)
- 📋 Genera reporte SARIF
- ⚠️ Falla si encuentra vulnerabilidades
- 📤 Sube a GitHub Code Scanning

#### 4. **build** - Construcción
- 🏗️ Instala dependencias de producción
- ⚡ Compila assets con Vite
- 📦 Genera .tar.gz con el código
- Solo se ejecuta en push/manual/PR aprobado

#### 5. **deploy** - Despliegue
- 🚀 Despliega a servidor QA
- 🔄 Sistema de releases (mantiene últimas 3)
- 🔗 Symlink atómico
- ⚙️ Ejecuta migraciones
- Solo se ejecuta en push/manual

### 📦 Artifacts Generados

| Artifact | Descripción |
|----------|-------------|
| `phpunit-junit_*` | Resultados de tests en formato JUnit XML |
| `coverage-report_*` | Reporte HTML de cobertura de código |
| `coverage-clover_*` | Reporte Clover XML para badges |
| `trivy-report_*` | Reporte de seguridad SARIF |
| `build_qa_*` | Build empaquetado listo para deploy |

### 🔐 Secrets Requeridos

Configura estos secrets en GitHub → Settings → Secrets:

```
QA_APP_URL          # URL de la aplicación
QA_DB_CONNECTION    # mysql
QA_DB_HOST          # Host de BD
QA_DB_PORT          # Puerto de BD
QA_DB_DATABASE      # Nombre de BD
QA_DB_USERNAME      # Usuario de BD
QA_DB_PASSWORD      # Contraseña de BD
QA_HOST             # IP/dominio del servidor
QA_SSH_USER         # Usuario SSH
QA_SSH_PASSWORD     # Contraseña SSH
```

### 📈 Ver Resultados

1. **Tests y Cobertura:**
   - Ve a Actions → Selecciona el workflow run
   - Descarga el artifact `coverage-report_*`
   - Abre `index.html` en tu navegador

2. **Seguridad:**
   - Ve a Security → Code scanning alerts
   - Ver reportes de Trivy

3. **Deploy:**
   - Revisa el job summary para ver la ruta del release
   - Symlink: `/var/www/laravel-app/current`

### 🔧 Desarrollo Local

Para probar antes de hacer push:

```bash
# Tests con cobertura
php artisan test --coverage-html coverage-report

# Escaneo de seguridad
docker run --rm -v .:/app aquasec/trivy fs /app

# Build de assets
npm run build
```

### 📝 Notas

- ⚠️ El job `scan` fallará si hay vulnerabilidades CRITICAL o HIGH
- 🔄 Se mantienen las últimas 3 releases en el servidor
- 🎯 Los PRs obtienen comentarios automáticos con cobertura
- 📊 Xdebug se activa automáticamente para coverage
