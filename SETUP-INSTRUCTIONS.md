# 🚀 INSTRUCCIONES DE CONFIGURACIÓN - PincraftWP

## ⚠️ IMPORTANTE: Configura tus API Keys

### Paso 1: Editar archivo .env

Edita el archivo `pincraft-api/.env` y reemplaza con TUS claves:

```bash
# Reemplazar con TU API de Replicate
REPLICATE_API_TOKEN=r8_TU_CLAVE_DE_REPLICATE_AQUI

# Reemplazar con TU API de OpenAI  
OPENAI_API_KEY=sk-TU_CLAVE_DE_OPENAI_AQUI

# JWT Secret generado (ya está listo)
JWT_SECRET=d44c3d12eff69fc7e9457dcda249219a333380d51d081f37aca43c1e1deb6e623d12cd22d1d462ddeec1295f2dd8a1e17db73171c8657865cbdf856a01f5f560

# Base de datos local (crear después)
DATABASE_URL=postgresql://postgres:password@localhost:5432/pincraftwp
```

### Paso 2: Obtener tus claves

#### Replicate API (para Ideogram)
1. Ve a: https://replicate.com/account/api-tokens
2. Crea un token
3. Copia la clave que empieza con `r8_`

#### OpenAI API (para optimización de textos)
1. Ve a: https://platform.openai.com/api-keys
2. Crea una nueva clave
3. Copia la clave que empieza con `sk-`

### Paso 3: Configurar Base de Datos Local

```bash
# Instalar PostgreSQL (macOS)
brew install postgresql
brew services start postgresql

# Crear base de datos
psql postgres -c "CREATE DATABASE pincraftwp;"
```

### Paso 4: Instalar y probar

```bash
cd pincraft-api
npm install
npm run dev
```

## 🌐 PREPARADO PARA DESPLIEGUE

Una vez que funcione localmente, podemos desplegarlo en:
- Railway (recomendado, fácil)
- Render (gratis con límites)
- VPS propio

## 💡 COSTOS ESTIMADOS

### Por 1000 pines generados:
- Replicate: ~$20 (Ideogram)
- OpenAI: ~$5 (textos)
- Total: ~$25 en APIs

### Precios a usuarios:
- Free: 10 pines/mes gratis
- Pro: $29/mes (200 pines) 
- Agency: $99/mes (1000 pines)

### ROI por usuario Agency:
- Ingresos: $99
- Costos API: ~$25
- Profit: ~$74 por usuario/mes

**¡El negocio es MUY rentable!** 💰

## 📞 Siguiente Paso

¿Ya tienes las claves de Replicate y OpenAI? 
Dime cuando las tengas para ayudarte con el setup local.