# Configuración de Herramientas de Seguridad

Este workflow integra **tres herramientas complementarias** de análisis de seguridad.

## Herramientas Integradas

### 1. Trivy (Aqua Security)

**Estado:** ✅ Activo por defecto

**Qué hace:**

- Escanea vulnerabilidades en dependencias (composer.lock, package-lock.json)
- Detecta secretos expuestos (API keys, tokens, passwords)
- Rápido y eficiente

**No requiere configuración adicional.**

---

### 2. OWASP Dependency-Check

**Estado:** ✅ Activo por defecto

**Qué hace:**

- Análisis profundo de dependencias contra NVD
- Genera reportes HTML detallados
- Identifica componentes obsoletos

**No requiere configuración adicional.**

**Ver reportes:**

1. Ve a Actions → Workflow run
2. Descarga artifact: `owasp-dc-report_*`
3. Abre el archivo HTML

---

### 3. GitGuardian

**Estado:** ⚠️ Requiere configuración

**Qué hace:**

- Detección especializada de secretos
- Más preciso que Trivy
- Detecta +350 tipos de credenciales

**Configuración requerida:**

#### Paso 1: Obtener API Key de GitGuardian

1. Ve a <https://dashboard.gitguardian.com/>
2. Regístrate o inicia sesión (gratis para repos públicos)
3. Ve a Settings → API Keys
4. Crea un nuevo token personal
5. Copia el API key

#### Paso 2: Configurar Secret en GitHub

1. Ve a tu repositorio en GitHub
2. Settings → Secrets and variables → Actions
3. Click "New repository secret"
4. Nombre: `GITGUARDIAN_API_KEY`
5. Valor: Pega el API key de GitGuardian
6. Click "Add secret"

#### Paso 3: Verificar

En el próximo push a `qa`, GitGuardian se ejecutará automáticamente.

**Si no configuras la API key:**

- El step de GitGuardian fallará
- El workflow continuará con los otros escaneos
- Los logs mostrarán el error de autenticación

---

## Ver Resultados

### Trivy

- **GitHub UI:** Security → Code scanning alerts
- **Artifact:** Descarga `trivy-report_*` (formato SARIF)

### OWASP DC

- **Artifact:** Descarga `owasp-dc-report_*` y abre el HTML

### GitGuardian

- **Logs:** Revisa los logs del step en Actions
- **GitHub UI:** Si encuentra secretos, fallará el workflow con detalles
