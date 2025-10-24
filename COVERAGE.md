# Reporte de Cobertura de Tests

## 📊 ¿Qué es la cobertura de código?

La cobertura de código te muestra qué porcentaje de tu código es ejecutado durante los tests, ayudándote a identificar áreas sin probar.

## 🔧 Configuración

### 1. Instalar Xdebug (Recomendado para desarrollo)

Xdebug es necesario para generar reportes de cobertura.

**Windows:**
```powershell
# Verificar versión de PHP y si Xdebug está instalado
php -v
php -m | Select-String "xdebug"

# Si no está instalado, descarga desde:
# https://xdebug.org/wizard

# Sigue las instrucciones del wizard:
# 1. Copia la salida de: php -i
# 2. Pégala en https://xdebug.org/wizard
# 3. Sigue las instrucciones para instalar
```

**Alternativa: PCOV (Más rápido para CI/CD)**
```powershell
pecl install pcov
```

### 2. Configurar php.ini

Agrega estas líneas al archivo `php.ini`:

**Para Xdebug 3.x:**
```ini
[xdebug]
zend_extension=xdebug
xdebug.mode=coverage
```

**Para PCOV:**
```ini
[pcov]
extension=pcov.so
pcov.enabled=1
```

## 🚀 Generar Reportes de Cobertura

### Opción 1: Reporte HTML (Recomendado)

```powershell
# Generar reporte HTML completo
php artisan test --coverage-html coverage-report

# O con PHPUnit directamente
vendor/bin/phpunit --coverage-html coverage-report

# Abrir el reporte en el navegador
start coverage-report/index.html
```

### Opción 2: Reporte en Terminal

```powershell
# Ver cobertura resumida en la terminal
php artisan test --coverage

# Ver cobertura con más detalles
php artisan test --coverage --min=80
```

### Opción 3: Reporte XML (para CI/CD)

```powershell
# Generar reporte Clover XML
php artisan test --coverage-clover coverage.xml

# Generar reporte Cobertura XML
php artisan test --coverage-cobertura coverage-cobertura.xml
```

### Opción 4: Reporte de Texto

```powershell
# Generar archivo de texto
vendor/bin/phpunit --coverage-text=coverage.txt
```

## 📈 Comandos Útiles

```powershell
# Ejecutar tests con cobertura mínima requerida (falla si no se alcanza)
php artisan test --coverage --min=70

# Ejecutar solo tests de Feature con cobertura
php artisan test --testsuite=Feature --coverage-html coverage-report

# Ver cobertura solo de archivos modificados
php artisan test --coverage --dirty
```

## 📂 Estructura del Reporte HTML

```
coverage-report/
├── index.html          # Página principal con resumen
├── dashboard.html      # Dashboard con gráficos
├── app/               # Cobertura por directorio
│   ├── Http/
│   ├── Models/
│   └── ...
└── _css/              # Estilos del reporte
```

## 🎨 Interpretación de Colores

En el reporte HTML:
- 🟢 **Verde** (>80%): Buena cobertura
- 🟡 **Amarillo** (50-80%): Cobertura media
- 🔴 **Rojo** (<50%): Cobertura baja

## 💡 Consejos

1. **No busques el 100%**: 70-80% es un buen objetivo
2. **Enfócate en lógica crítica**: Prioriza controllers, models, y lógica de negocio
3. **Excluye código no testeable**: Views, migrations, seeders
4. **Revisa líneas no cubiertas**: El reporte muestra exactamente qué líneas no están probadas

## 🚫 Excluir Archivos de la Cobertura

Si quieres excluir ciertos archivos, edita `phpunit.xml`:

```xml
<source>
    <include>
        <directory>app</directory>
    </include>
    <exclude>
        <directory>app/Console</directory>
        <directory>app/Exceptions</directory>
        <file>app/Providers/AppServiceProvider.php</file>
    </exclude>
</source>
```

## 🔍 Solución de Problemas

### Error: "No code coverage driver available"

**Solución:** Instala Xdebug o PCOV (ver sección de configuración)

### Reporte vacío o sin datos

**Verificar:**
```powershell
# Ver si Xdebug está activo
php -v

# Debe aparecer: "with Xdebug v3.x"
```

### Tests muy lentos con cobertura

**Solución:** Usa PCOV en lugar de Xdebug (es más rápido)

## 📊 Integración con CI/CD

Ejemplo para GitHub Actions:

```yaml
- name: Run tests with coverage
  run: php artisan test --coverage --min=70
  
- name: Upload coverage report
  uses: codecov/codecov-action@v3
  with:
    file: ./coverage.xml
```

## 🎯 Objetivos de Cobertura Recomendados

| Tipo de Código | Cobertura Mínima |
|----------------|------------------|
| Controllers    | 80%              |
| Models         | 70%              |
| Services       | 85%              |
| Helpers        | 90%              |
| Policies       | 80%              |

---

**Última actualización:** Octubre 2025
