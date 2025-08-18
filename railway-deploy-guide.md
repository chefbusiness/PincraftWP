# 🚀 Guía de Deploy en Railway - PincraftWP

## ✅ PRE-REQUISITOS LISTOS
- [x] Código en GitHub: https://github.com/chefbusiness/PincraftWP
- [x] API Keys configuradas ✅
- [x] Estructura de proyecto correcta

## 📋 PASOS PARA RAILWAY

### 1. Crear Proyecto en Railway
- Login con GitHub (recomendado)
- "New Project" → "Deploy from GitHub repo"
- Seleccionar: `chefbusiness/PincraftWP`
- Railway detectará automáticamente el `package.json`

### 2. Configurar Root Directory
⚠️ **IMPORTANTE**: Railway necesita saber que la API está en `/pincraft-api`

En Project Settings:
- **Root Directory**: `pincraft-api`
- **Build Command**: `npm install`
- **Start Command**: `npm start`

### 3. Agregar PostgreSQL
- En tu proyecto: "Add Service" → "Database" → "PostgreSQL"
- Railway creará automáticamente la variable `DATABASE_URL`

### 4. Variables de Entorno
En Settings → Variables, agregar:

```
NODE_ENV=production
PORT=3000
REPLICATE_API_TOKEN=r8_2is1rxOqBrM8ytgMGpre7kKNnQ84LqY2SxDbt
OPENAI_API_KEY=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
JWT_SECRET=d44c3d12eff69fc7e9457dcda249219a333380d51d081f37aca43c1e1deb6e623d12cd22d1d462ddeec1295f2dd8a1e17db73171c8657865cbdf856a01f5f560
RATE_LIMIT_WINDOW_MS=900000
RATE_LIMIT_MAX_REQUESTS=100
IDEOGRAM_MODEL=ideogram-ai/ideogram-v3-turbo
MAX_PINS_PER_REQUEST=10
IMAGE_WIDTH=1080
IMAGE_HEIGHT=1920
PLAN_FREE_LIMIT=10
PLAN_PRO_LIMIT=200
PLAN_AGENCY_LIMIT=1000
```

⚠️ **NO agregar DATABASE_URL** - Railway lo crea automáticamente

### 5. Deploy Automático
- Railway hará deploy automático cuando detecte el repo
- Revisar logs en la pestaña "Deployments"
- URL estará disponible en ~3-5 minutos

### 6. Configurar Base de Datos
Una vez deployed:
- Ir a la base de datos PostgreSQL
- "Connect" → copiar connection string
- Ejecutar las migraciones (te ayudo cuando esté listo)

### 7. Obtener URL de Producción
- Tu API estará disponible en algo como:
- `https://tu-proyecto.railway.app`
- Usaremos esta URL en el plugin WordPress

## 💰 COSTOS ESTIMADOS

### Railway Pricing:
- **Hobby Plan**: $5/mes de crédito incluido
- **Pro Plan**: $20/mes (recomendado para producción)

### Uso estimado:
- API Backend: ~$8-15/mes
- PostgreSQL: ~$5-10/mes
- **Total**: ~$13-25/mes

## 🔄 AUTO-DEPLOY
Cada vez que hagas `git push` a GitHub, Railway desplegará automáticamente.

## 🐛 TROUBLESHOOTING

### Si el deploy falla:
1. Verificar Root Directory = `pincraft-api`
2. Revisar logs en Railway dashboard
3. Verificar que todas las variables están configuradas

### Si la DB falla:
1. Esperar ~2-3 minutos para que PostgreSQL esté listo
2. Ejecutar migraciones manualmente

## ✅ CUANDO ESTÉ LISTO
Una vez deployed, actualizaremos:
1. URL del endpoint en el plugin WordPress
2. Ejecutar migraciones de base de datos
3. Crear primer usuario de prueba
4. ¡Probar generación de pines!

---
**¡Avísame cuando tengas la cuenta lista y empezamos! 🚀**