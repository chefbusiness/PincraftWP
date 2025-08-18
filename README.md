# PincraftWP - Sistema Completo de Generación de Pines para Pinterest

## 📦 Componentes del Sistema

### 1. **Plugin WordPress** (`/pincraft-wp`)
Plugin que se instala en sitios WordPress para generar pines automáticamente.

### 2. **API Backend** (`/pincraft-api`)
Servidor Node.js que gestiona la generación de imágenes y el modelo SaaS.

## 🚀 Instalación Rápida

### Paso 1: Configurar el Backend API

```bash
# Navegar a la carpeta del API
cd pincraft-api

# Instalar dependencias
npm install

# Crear archivo .env basado en el ejemplo
cp .env.example .env

# Editar .env con tus claves:
# - REPLICATE_API_TOKEN (tu API de Replicate)
# - OPENAI_API_KEY (tu API de OpenAI)
# - DATABASE_URL (PostgreSQL)
# - JWT_SECRET (genera una clave segura)

# Crear base de datos PostgreSQL
psql -U postgres -c "CREATE DATABASE pincraftwp;"

# Ejecutar migraciones
psql -U postgres -d pincraftwp -f database/schema.sql

# Iniciar servidor de desarrollo
npm run dev
```

### Paso 2: Instalar Plugin en WordPress

1. Comprimir la carpeta `pincraft-wp` en un ZIP
2. En WordPress Admin, ir a **Plugins → Añadir nuevo → Subir plugin**
3. Subir el ZIP y activar el plugin
4. Ir a **PincraftWP → Configuración**
5. Registrarte en el backend para obtener API Key
6. Ingresar la API Key en la configuración

## 🔧 Configuración del Backend

### Variables de Entorno Importantes

```env
# TUS APIs (costos van a tu cuenta)
REPLICATE_API_TOKEN=r8_xxxxxxxxxxxxx
OPENAI_API_KEY=sk-xxxxxxxxxxxxx

# Base de datos PostgreSQL
DATABASE_URL=postgresql://usuario:password@localhost:5432/pincraftwp

# Seguridad
JWT_SECRET=tu_clave_super_secreta_aqui

# Configuración de servidor
PORT=3000
NODE_ENV=development
```

### Crear Usuario de Prueba (API)

```bash
# Con curl
curl -X POST http://localhost:3000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123",
    "plan": "pro"
  }'

# Respuesta incluirá tu API Key
```

## 📱 Uso del Plugin

### Generar Pines

1. Ir a **PincraftWP → Generar Pines**
2. Seleccionar un artículo del blog
3. Ajustar cantidad de pines (1-10)
4. Elegir estilo visual
5. Click en "Generar Pines"
6. Los pines se guardan en la Biblioteca de Medios

### Ver Historial

- **PincraftWP → Historial** muestra todas las generaciones
- Cada pin incluye título y descripción optimizados para Pinterest

## 💰 Modelo de Negocio

### Planes Disponibles

- **Free**: 10 pines/mes
- **Pro**: 200 pines/mes ($29)
- **Agency**: 1000 pines/mes ($99)

### Costos Operativos (Tu Backend)

- **Replicate (Ideogram)**: ~$0.02 por imagen
- **OpenAI**: ~$0.01 por optimización de texto
- **Total por pin**: ~$0.03

## 🚀 Despliegue en Producción

### Backend API - Opciones Recomendadas

#### Opción 1: Railway
```bash
# Instalar Railway CLI
npm install -g @railway/cli

# Login y deploy
railway login
railway init
railway up
```

#### Opción 2: Render
1. Conectar repo de GitHub
2. Configurar variables de entorno
3. Deploy automático

#### Opción 3: VPS (DigitalOcean/Linode)
```bash
# Configurar servidor Ubuntu
sudo apt update
sudo apt install nodejs npm postgresql nginx

# Clonar repo y configurar
git clone tu-repo.git
cd pincraft-api
npm install
npm run build

# Usar PM2 para producción
npm install -g pm2
pm2 start server.js
```

### Configurar Dominio

1. Apuntar `api.pincraftwp.com` a tu servidor
2. Configurar SSL con Let's Encrypt
3. Actualizar `api_endpoint` en el plugin si es necesario

## 📊 Monitoreo y Analytics

### Métricas Importantes

- Uso de créditos por usuario
- Tasa de conversión free → pago
- Tiempo de generación promedio
- Errores de API

### Consultas SQL Útiles

```sql
-- Ver usuarios activos
SELECT plan_type, COUNT(*) 
FROM users 
WHERE is_active = true 
GROUP BY plan_type;

-- Uso de créditos este mes
SELECT SUM(credits_used) as total_credits
FROM users
WHERE reset_date > NOW() - INTERVAL '30 days';

-- Pines generados hoy
SELECT COUNT(*) 
FROM pin_generations 
WHERE created_at > CURRENT_DATE;
```

## 🔒 Seguridad

### Checklist de Seguridad

- [ ] Cambiar JWT_SECRET en producción
- [ ] Configurar CORS correctamente
- [ ] Habilitar rate limiting
- [ ] Usar HTTPS siempre
- [ ] Backup regular de base de datos
- [ ] Monitorear uso anormal de API

## 🐛 Troubleshooting

### Plugin no conecta con API
- Verificar que el backend está corriendo
- Revisar configuración de CORS
- Verificar API Key válida

### Imágenes no se generan
- Verificar créditos de Replicate
- Revisar logs del servidor
- Verificar formato de prompts

### Base de datos
- Verificar conexión PostgreSQL
- Ejecutar migraciones pendientes
- Revisar permisos de usuario

## 📝 Notas de Desarrollo

### Estructura de Costos
- **Replicate**: $0.02/imagen × 1000 = $20
- **OpenAI**: $0.01/texto × 1000 = $10
- **Servidor**: ~$20/mes
- **Base de datos**: ~$15/mes
- **Total mensual**: ~$65 para 1000 pines

### ROI Esperado
- Plan Agency ($99) - Costos ($30) = **$69 profit/usuario**
- Break-even: ~1 usuario agency o 3 usuarios pro

## 🎯 Próximos Pasos

1. **Configurar Stripe** para pagos automáticos
2. **Landing page** en pincraftwp.com
3. **Dashboard web** para usuarios
4. **Webhooks** de Pinterest para publicación directa
5. **A/B Testing** de estilos de imagen

## 📞 Soporte

- Documentación: docs.pincraftwp.com
- Email: support@pincraftwp.com
- GitHub Issues: github.com/tuusuario/pincraftwp

---

**¿Listo para monetizar?** 🚀

1. Configura el backend con tus APIs
2. Despliega en producción
3. Crea landing page
4. ¡Empieza a vender!

El sistema está diseñado para escalar. Con 10 usuarios Pro ya cubres costos operativos.