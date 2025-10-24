# Documentación Workflow CI/CD QA

## qa-cicd.yml

Workflow automatizado para el ambiente de QA.

### Eventos que disparan el workflow

**Push a rama `qa`:**

- Ejecuta: Tests → Scan de Seguridad → Build → Deploy

**Pull Request a `qa`:**

- Ejecuta: Tests → Scan de Seguridad
- No ejecuta: Build ni Deploy
- Genera reporte de cobertura y comenta en el PR

**Pull Request Review aprobado:**

- Ejecuta: Tests → Scan de Seguridad → Build
- No ejecuta: Deploy

**Ejecución manual:**

- Ejecuta el flujo completo desde GitHub Actions

### Pipeline de Jobs

#### 1. meta - Generación de ID de Deploy

Genera un identificador único para el despliegue.

- Formato: `YYYYMMDDHHMMSS-RUN_NUMBER-SHA`
- Se usa en todos los jobs subsecuentes

#### 2. test - Pruebas PHPUnit con Cobertura

Ejecuta la suite de tests con análisis de cobertura de código.

**Stack tecnológico:**

- PHP 8.3 con Xdebug
- MySQL 8.0
- Node.js 20

**Pasos:**

1. Instalar dependencias de Composer
2. Configurar Node.js e instalar paquetes npm
3. Compilar assets con Vite
4. Configurar ambiente de testing .env.testing
5. Ejecutar PHPUnit con cobertura
6. Subir artifacts JUnit XML, Coverage HTML y Clover XML
7. Comentar porcentaje de cobertura en PRs

**Artifacts generados:**

- `phpunit-junit_*` - Resultados en formato JUnit XML
- `coverage-report_*` - Reporte HTML de cobertura
- `coverage-clover_*` - Reporte Clover XML

#### 3. scan - Escaneo de Vulnerabilidades

Escanea el filesystem buscando vulnerabilidades con tres herramientas complementarias:

**3.1 Trivy (Aqua Security):**

- Severidad: CRITICAL, HIGH
- Escanea: vulnerabilities, secrets
- Exit code: 0
- Formato de salida: SARIF

**Qué detecta:**

- CVEs en composer.lock y package-lock.json
- Secretos expuestos (API keys, tokens, passwords)
- Configuraciones inseguras

**3.2 OWASP Dependency-Check:**

- Análisis profundo de dependencias
- Genera reporte HTML detallado
- Compara contra NVD (National Vulnerability Database)

**Qué detecta:**

- Vulnerabilidades conocidas en dependencias PHP y Node.js
- Componentes obsoletos o deprecados
- Licencias de dependencias

**3.3 GitGuardian:**

- Especializado en detección de secretos
- Más preciso que Trivy para secret detection
- Requiere GITGUARDIAN_API_KEY en secrets

**Qué detecta:**

- API keys de servicios cloud (AWS, GCP, Azure)
- Tokens de GitHub, GitLab, Bitbucket
- Credenciales de bases de datos
- Claves privadas SSH/SSL

**Artifacts generados:**

- `trivy-report_*` - Reporte SARIF de Trivy
- `owasp-dc-report_*` - Reporte HTML de OWASP DC

**Comportamiento:**

- Los tres escaneos se ejecutan en paralelo
- Los resultados de Trivy se suben a GitHub Security tab
- El workflow continúa aunque encuentre vulnerabilidades
- GitGuardian falla el workflow si encuentra secretos críticos

#### 4. build - Construcción para Producción

Crea el paquete de despliegue.

**Se ejecuta cuando:**

- Push a rama qa
- Ejecución manual del workflow
- Pull request review aprobado

**Pasos:**

1. Instalar dependencias de Composer sin dev
2. Configurar Node.js
3. Compilar assets con Vite para producción
4. Configurar .env desde GitHub Secrets
5. Optimizar configuración, rutas y vistas de Laravel
6. Empaquetar en .tar.gz excluyendo .git, node_modules, .github y tests

**Artifacts generados:**

- `build_qa_*` - Paquete de despliegue

#### 5. deploy - Despliegue al Servidor

Despliega al servidor QA usando estrategia de releases.

**Se ejecuta cuando:**

- Push a rama qa
- Ejecución manual del workflow

**Estrategia de despliegue:**

- Mantiene las últimas 3 releases
- Cambio atómico de symlink
- Zero-downtime deployment

**Estructura de directorios:**

```
/var/www/laravel-app/
├── current -> releases/YYYYMMDDHHMMSS-RUN-SHA
└── releases/
    ├── YYYYMMDDHHMMSS-RUN-SHA
    ├── YYYYMMDDHHMMSS-RUN-SHA
    └── YYYYMMDDHHMMSS-RUN-SHA
```

**Pasos del despliegue:**

1. Transferir paquete via SCP a /tmp
2. Extraer a nuevo directorio de release
3. Establecer permisos user:www-data
4. Actualizar symlink de forma atómica
5. Ejecutar migraciones de base de datos
6. Optimizar cache de Laravel
7. Limpiar releases antiguas

### Secrets de GitHub Requeridos

Configurar en: Repository Settings → Secrets and variables → Actions

```
QA_APP_URL          URL de la aplicación
QA_DB_CONNECTION    mysql
QA_DB_HOST          Host de base de datos
QA_DB_PORT          Puerto de base de datos
QA_DB_DATABASE      Nombre de base de datos
QA_DB_USERNAME      Usuario de base de datos
QA_DB_PASSWORD      Contraseña de base de datos
QA_HOST             IP o dominio del servidor
QA_SSH_USER         Usuario SSH
QA_SSH_PASSWORD     Contraseña SSH
GITGUARDIAN_API_KEY API Key de GitGuardian
```

### Ver Resultados del Workflow

**Cobertura de tests:**

1. Ir a Actions → Seleccionar workflow run
2. Descargar artifact `coverage-report_*`
3. Extraer y abrir index.html

**Escaneo de seguridad:**

1. Ir a Security → Code scanning alerts
2. Filtrar por Trivy
3. Revisar vulnerabilidades por severidad

**Estado del despliegue:**

1. Revisar resumen del workflow run
2. Verificar ruta del release y symlink
3. Acceder a la aplicación en QA_APP_URL

### Estructura de Releases en Servidor

El servidor mantiene las últimas 3 releases en `/var/www/laravel-app/releases/`.

**Symlink actual:**

- `/var/www/laravel-app/current` apunta a la release más reciente
