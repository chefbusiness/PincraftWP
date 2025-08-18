# PRD: PincraftWP - Plugin de WordPress para Generación Automática de Pines de Pinterest

## 📋 Resumen Ejecutivo

**Nombre del Producto**: PincraftWP  
**Versión**: 1.0 (MVP)  
**Tipo**: Plugin de WordPress  
**Público Objetivo**: Bloggers, marketers digitales, propietarios de sitios web WordPress  
**Propósito**: Automatizar la creación de pines optimizados para Pinterest a partir del contenido de blog existente

## 🎯 Visión del Producto

Convertir PincraftWP en la herramienta líder para la automatización de marketing en Pinterest para sitios WordPress, generando pines visualmente atractivos y optimizados que incrementen el tráfico orgánico y las conversiones.

## 🚀 Objetivos del Producto

### Objetivos Primarios (MVP)
- ✅ Generar 1-10 pines automáticos por artículo (configurable por usuario)
- ✅ Integración con API de Ideogram 3 vía Replicate
- ✅ Dashboard intuitivo con slider para selección de cantidad
- ✅ Optimización automática para formato 9:16 (Pinterest)
- ✅ Textos overlay con dominio y branding

### Objetivos Secundarios (Escalabilidad)
- 🔄 Publicación automática en Pinterest
- 🔄 Plantillas personalizables de diseño
- 🔄 Analytics de rendimiento de pines
- 🔄 Integración con múltiples cuentas de Pinterest
- 🔄 Sistema de suscripción SaaS

## 👥 Usuarios Objetivo

### Segmento Primario
- **Bloggers profesionales** que publican 3-10 artículos/semana
- **Agencias de marketing digital** gestionando múltiples clientes
- **Propietarios de e-commerce** con blogs de contenido

### Segmento Secundario  
- **Influencers** con sitios web WordPress
- **Empresas SaaS** con blogs técnicos
- **Sitios de recetas y lifestyle**

## ⭐ Características Clave

### MVP (Versión 1.0)

#### 🎛️ Dashboard de Administración
- **Selector de artículos**: Dropdown + buscador con autocompletado
- **Vista previa del contenido**: Título, extracto, imagen destacada
- **Configuración de generación**: 
  - **Selector de cantidad de pines**: Slider horizontal interactivo (1-10 pines)
  - **Indicador numérico**: Muestra valor seleccionado en tiempo real
  - **Valor predeterminado**: 4 pines (configurable en ajustes)
  - **Estilos de diseño**: Selección de plantilla visual
- **Historial de generaciones**: Lista de pines creados por artículo

#### 🎨 Generación de Pines
- **Formato optimizado**: 1080x1920px (9:16)
- **Overlay inteligente**: Título del artículo + dominio del sitio
- **Variaciones automáticas**: 1-10 diseños diferentes por artículo (configurable por usuario)
- **Calidad premium**: Integración con Ideogram 3 (mejor modelo para texto)
- **Generación en lote**: Procesamiento paralelo para múltiples pines

#### 📝 Optimización de Contenido
- **Títulos Pinterest-optimizados**: Máximo 100 caracteres
- **Descripciones SEO**: Máximo 450 caracteres con palabras clave
- **Hashtags automáticos**: Generación basada en contenido del artículo
- **Call-to-actions**: Frases de engagement personalizables

#### 🔧 Funcionalidades Técnicas
- **API de Replicate**: Integración robusta con manejo de errores
- **Gestión de imágenes**: Almacenamiento en biblioteca de medios de WordPress
- **Metadata completa**: Alt text, descripciones, tags para SEO
- **Caché inteligente**: Evitar regeneraciones innecesarias

### Características Futuras (Producto Completo)

#### 📊 Analytics y Reporting
- Dashboard de rendimiento de pines
- Métricas de engagement de Pinterest
- ROI tracking y conversiones
- A/B testing de diseños

#### 🤖 Automatización Avanzada
- Publicación automática en Pinterest
- Scheduling inteligente basado en audiencia
- Integración con Buffer/Hootsuite
- Cross-posting a redes sociales

#### 🎨 Personalización Premium
- Editor de plantillas drag & drop
- Biblioteca de elementos gráficos
- Paletas de colores personalizadas
- Fuentes tipográficas premium

#### 💰 Monetización SaaS
- Planes de suscripción por uso
- API para desarrolladores
- White-label para agencias
- Integración con plataformas e-commerce

## 🎮 Experiencia de Usuario en WordPress Admin

### Ubicación del Plugin en WordPress
- **Menú Principal**: "PincraftWP" en el sidebar izquierdo del admin de WordPress
- **Icono**: Pin personalizado de Pinterest
- **Submenús**:
  - **Generar Pines**: Dashboard principal para crear pines
  - **Historial**: Lista de todos los pines generados
  - **Configuración**: API Key y preferencias
  - **Ayuda**: Documentación y soporte

### Flujo de Primer Uso
1. **Activación del Plugin**:
   - Usuario instala y activa el plugin
   - Aparece notificación: "Configure su API Key para comenzar"
   
2. **Configuración Inicial**:
   - Click en PincraftWP → Configuración
   - Enlace a "Obtener API Key Gratis" → Redirige a pincraftwp.com
   - Usuario crea cuenta y copia API Key
   - Regresa a WordPress y pega la API Key
   - Plugin valida y muestra: "✅ API Key válida - Plan: [Free/Pro/Agency]"

3. **Primera Generación**:
   - Va a PincraftWP → Generar Pines
   - Selecciona un artículo del dropdown
   - Ajusta slider de cantidad (1-10 pines)
   - Click en "Generar Pines"
   - Ve progreso en tiempo real
   - Descarga o publica directamente

### Dashboard Principal (Generar Pines)
```
┌─────────────────────────────────────────┐
│  🎯 Generar Pines para Pinterest        │
├─────────────────────────────────────────┤
│                                         │
│  Seleccionar Artículo:                 │
│  [Dropdown con buscador ▼]             │
│                                         │
│  Vista Previa:                         │
│  ┌─────────────────────────┐          │
│  │ [Imagen destacada]       │          │
│  │ Título del artículo      │          │
│  │ Extracto...              │          │
│  └─────────────────────────┘          │
│                                         │
│  Cantidad de Pines: [4]                │
│  [========|================] 1-10      │
│                                         │
│  Estilo Visual:                        │
│  ○ Moderno  ● Minimalista  ○ Vibrante │
│                                         │
│  [Generar Pines] [Configuración ⚙️]    │
│                                         │
│  Créditos: 45/200 este mes            │
└─────────────────────────────────────────┘
```

## 🏗️ Arquitectura Técnica

### Stack Tecnológico
- **Backend**: PHP 8.0+ (WordPress standards)
- **Frontend**: JavaScript (ES6+), CSS3, HTML5
- **API Externa**: Replicate API (Ideogram 3)
- **Base de datos**: WordPress MySQL (custom tables)
- **Almacenamiento**: WordPress Media Library

### Estructura del Plugin
```
pincraft-wp/
├── pincraft-wp.php                 # Plugin principal
├── includes/
│   ├── class-pincraft-core.php     # Lógica principal
│   ├── class-pincraft-admin.php    # Dashboard admin
│   ├── class-pincraft-api.php      # Integración Replicate
│   ├── class-pincraft-generator.php # Generación de pines
│   └── class-pincraft-settings.php # Configuraciones
├── admin/
│   ├── css/
│   ├── js/
│   └── views/
├── assets/
│   ├── templates/                  # Plantillas de diseño
│   └── fonts/                     # Tipografías
└── languages/                     # Internacionalización
```

### Integraciones Clave

#### APIs Internas de WordPress (Acceso Nativo)
- **WordPress REST API**: Acceso directo a posts/páginas sin autenticación adicional
- **WordPress Media Library**: Gestión nativa de imágenes generadas
- **WordPress Options API**: Almacenamiento de configuraciones del plugin
- **WordPress Database API**: Acceso a tablas personalizadas para historial

#### API Externa de PincraftWP (Modelo SaaS)
- **Endpoint Principal**: `https://api.pincraftwp.com/v1/`
- **Autenticación**: API Key única por usuario
- **Servicios Backend** (gestionados por PincraftWP):
  - **Replicate API**: `ideogram-ai/ideogram-v3-turbo` (INTERNO - No expuesto al usuario)
  - **OpenAI API**: Optimización de prompts (INTERNO - No expuesto al usuario)

#### Flujo de Autenticación
1. Usuario instala plugin en WordPress
2. Crea cuenta en pincraftwp.com
3. Obtiene API Key personal
4. Introduce API Key en Settings del plugin
5. Plugin valida key con api.pincraftwp.com
6. Acceso completo a funcionalidades según plan

## 📊 Especificaciones de Producto

### Arquitectura de API SaaS

#### Endpoints de PincraftWP API
```
POST /v1/auth/validate
  Body: { "api_key": "user_api_key" }
  Response: { "valid": true, "plan": "pro", "remaining_credits": 150 }

POST /v1/pins/generate
  Headers: { "X-API-Key": "user_api_key" }
  Body: {
    "title": "Post title",
    "content": "Post content",
    "domain": "user-site.com",
    "count": 4,
    "style": "modern"
  }
  Response: { "pins": [...], "credits_used": 4 }

GET /v1/account/usage
  Headers: { "X-API-Key": "user_api_key" }
  Response: { "total_used": 50, "limit": 200, "reset_date": "2024-02-01" }
```

### Parámetros de Generación de Imágenes (Backend)
```json
{
  "model": "ideogram-ai/ideogram-v3-turbo",
  "input": {
    "width": 1080,
    "height": 1920,
    "prompt": "[título del artículo] | [dominio]",
    "style": "photography|realistic|minimalist",
    "aspect_ratio": "9:16",
    "color_palette": "vibrant|professional|elegant"
  }
}
```

### Límites y Restricciones MVP
- **Generaciones por día**: 50 (límite API Replicate)
- **Pines por artículo**: 1-10 (configurable por usuario)
- **Tamaño máximo de imagen**: 5MB por pin
- **Formatos soportados**: PNG, JPG
- **Compatibilidad WordPress**: 5.0+
- **Compatibilidad PHP**: 7.4+

## 💰 Modelo de Monetización

### Fase MVP (Modelo SaaS)
- **Plan Gratuito**: 10 generaciones/mes (requiere API Key gratuita)
- **Plan Pro**: $29/mes - 200 generaciones/mes
- **Plan Agency**: $99/mes - 1000 generaciones/mes
- **Autenticación**: Todos los planes requieren API Key de pincraftwp.com

### Fase Escalada (SaaS)
- **Plan Starter**: $19/mes - 100 pines/mes
- **Plan Growth**: $49/mes - 500 pines/mes  
- **Plan Scale**: $149/mes - 2000 pines/mes
- **Plan Enterprise**: Custom pricing

### Monetización Adicional
- **Plantillas Premium**: $5-15 por paquete
- **Servicios de Setup**: $199 implementación
- **White-label License**: $499/año
- **API Access**: $0.10 por generación

## 📈 Roadmap de Desarrollo

### Sprint 1 (Semanas 1-2): Fundación
- ✅ Setup básico del plugin WordPress
- ✅ Estructura de archivos y clases principales
- ✅ Dashboard administrativo básico
- ✅ Integración inicial con Replicate API

### Sprint 2 (Semanas 3-4): Core Features  
- ✅ Selector de artículos con buscador
- ✅ Generación de prompts optimizados
- ✅ Procesamiento de imágenes con Ideogram 3
- ✅ Gestión en WordPress Media Library

### Sprint 3 (Semanas 5-6): Optimización
- ✅ Interfaz de usuario pulida
- ✅ Manejo de errores robusto
- ✅ Sistema de configuraciones
- ✅ Testing y debugging

### Sprint 4 (Semanas 7-8): Lanzamiento MVP
- ✅ Documentación completa
- ✅ Testing de usuario
- ✅ Preparación para WordPress.org
- ✅ Landing page y materiales de marketing

## 🔄 Métricas de Éxito

### KPIs Técnicos
- **Tiempo de generación**: < 30 segundos por pin
- **Tasa de éxito API**: > 95%
- **Compatibilidad**: 99% sitios WordPress
- **Performance**: < 2 segundos carga dashboard

### KPIs de Negocio
- **Instalaciones activas**: 1,000+ en 3 meses
- **Conversión freemium**: 15% a plan pagado
- **Retención mensual**: 80%+ usuarios activos
- **NPS Score**: > 50

### KPIs de Usuario
- **Engagement en Pinterest**: +40% vs pines manuales  
- **Tráfico referido**: +25% desde Pinterest
- **Tiempo ahorrado**: 15 minutos por artículo
- **Satisfacción**: 4.5+ estrellas en reviews

## 🎯 Casos de Uso Principales

### Caso de Uso 1: Blogger de Lifestyle
**Contexto**: María tiene un blog de lifestyle con 2-3 artículos por semana
**Flujo**: 
1. Selecciona artículo "10 Outfits para Otoño"
2. Genera 4 pines con diferentes estilos visuales
3. Descarga pines para subir a Pinterest manualmente
4. Incrementa tráfico en 30%

### Caso de Uso 2: Agencia de Marketing
**Contexto**: Agencia gestiona 10 clientes con blogs WordPress  
**Flujo**:
1. Genera pines para todos los artículos nuevos semanalmente
2. Usa diferentes configuraciones de branding por cliente
3. Genera reportes de performance
4. Escala servicios de Pinterest marketing

### Caso de Uso 3: E-commerce con Blog
**Contexto**: Tienda online con blog de 15 artículos/mes
**Flujo**:
1. Artículos sobre productos incluyen CTAs de compra
2. Pines generados incluyen enlaces a productos
3. Pinterest se convierte en canal de ventas clave
4. ROI tracking desde pins hasta ventas

## 🔐 Consideraciones de Seguridad y Privacidad

### Seguridad
- ✅ Sanitización de todos los inputs del usuario
- ✅ Validación de API keys de Replicate
- ✅ Rate limiting para evitar abuso
- ✅ Logs de seguridad y auditoria

### Privacidad
- ✅ No almacenamiento de contenido del usuario en servidores externos
- ✅ Política de privacidad clara sobre uso de APIs
- ✅ Opt-in para analytics y tracking
- ✅ Cumplimiento GDPR para usuarios europeos

## 📋 Requisitos Técnicos Detallados

### Servidor/Hosting
- **PHP**: 7.4+ (recomendado 8.0+)
- **WordPress**: 5.0+ (recomendado 6.0+)
- **MySQL**: 5.6+ o MariaDB 10.0+
- **Memoria RAM**: 256MB+ disponible para PHP
- **Espacio disco**: 50MB+ para plugin y caché

### APIs y Servicios
- **PincraftWP API**: API Key válida de pincraftwp.com
- **WordPress REST API**: Habilitada (acceso nativo del plugin)
- **PHP cURL**: Para llamadas HTTP a api.pincraftwp.com
- **GD Library**: Procesamiento de imágenes
- **JSON**: Soporte nativo PHP

### Funcionalidades WordPress
- **Media Library**: Gestión de archivos
- **Custom Post Types**: Opcional para historial
- **Options API**: Configuraciones
- **Transients API**: Caché temporal
- **WP Cron**: Tareas programadas

## 🚀 Estrategia de Lanzamiento

### Pre-lanzamiento (Mes 1)
- Desarrollo MVP completo
- Testing beta con 20 usuarios
- Creación de documentación
- Setup landing page

### Lanzamiento Soft (Mes 2)  
- Publicación en WordPress.org
- Outreach a bloggers influyentes
- Content marketing (tutoriales)
- Collecting feedback inicial

### Lanzamiento Completo (Mes 3)
- Campaña marketing pagada
- Partnership con temas/plugins complementarios
- Programa de afiliados
- Expansión a mercados internacionales

### Post-lanzamiento (Mes 4+)
- Iteraciones basadas en feedback
- Desarrollo de características premium
- Escalamiento SaaS
- Expansión a otras redes sociales

## 💡 Innovaciones Clave

### Diferenciadores Competitivos
- **IA de última generación**: Ideogram 3 para textos perfectos
- **Integración nativa WordPress**: Sin dependencias externas
- **Optimización Pinterest**: Algoritmo específico para esta red
- **Branding automático**: Dominio siempre presente

### Ventajas Técnicas
- **Zero-config**: Funciona out-of-the-box
- **Responsive design**: Dashboard adaptativo
- **Multilingüe**: Soporte i18n completo
- **Extensible**: Hooks para developers

---

## 📞 Próximos Pasos

1. **Validación del PRD**: Review y refinamiento de especificaciones
2. **Setup del entorno**: Configuración de desarrollo WordPress local
3. **Arquitectura detallada**: Diseño de clases y base de datos
4. **Desarrollo iterativo**: Sprints de 2 semanas
5. **Testing continuo**: QA en cada sprint

**¿Listo para empezar el desarrollo del MVP?** 🚀