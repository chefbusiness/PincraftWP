const Replicate = require('replicate');
const OpenAI = require('openai');
const sharp = require('sharp');
const db = require('../config/database');

// Inicializar APIs
const replicate = new Replicate({
  auth: process.env.REPLICATE_API_TOKEN,
});

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

// Generar pines para Pinterest
exports.generatePins = async (req, res) => {
  const client = await db.getClient();
  
  try {
    const { 
      title, 
      content, 
      domain, 
      count = 4, 
      style = 'modern',
      excerpt = '' 
    } = req.body;

    const userId = req.user.id;

    // Validar parámetros
    if (!title || !domain) {
      return res.status(400).json({ 
        error: 'Title and domain are required' 
      });
    }

    if (count < 1 || count > 10) {
      return res.status(400).json({ 
        error: 'Pin count must be between 1 and 10' 
      });
    }

    // Verificar créditos disponibles
    const userResult = await client.query(
      'SELECT monthly_credits, credits_used, plan_type FROM users WHERE id = $1',
      [userId]
    );

    const user = userResult.rows[0];
    const remainingCredits = user.monthly_credits - user.credits_used;

    if (remainingCredits < count) {
      return res.status(403).json({ 
        error: 'Insufficient credits',
        remaining_credits: remainingCredits,
        required_credits: count
      });
    }

    // Comenzar transacción
    await client.query('BEGIN');

    // Crear registro de generación
    const generationResult = await client.query(
      `INSERT INTO pin_generations 
       (user_id, post_title, post_content, domain, pin_count, style, status)
       VALUES ($1, $2, $3, $4, $5, $6, 'processing')
       RETURNING id`,
      [userId, title, content || excerpt, domain, count, style]
    );

    const generationId = generationResult.rows[0].id;

    // Generar textos optimizados con OpenAI
    const pinterestTexts = await generatePinterestTexts(title, content || excerpt, count);

    // Array para almacenar los pines generados
    const generatedPins = [];

    // Generar cada pin
    for (let i = 0; i < count; i++) {
      try {
        // Crear prompt para Ideogram
        const imagePrompt = createImagePrompt(
          pinterestTexts[i].title, 
          domain, 
          style
        );

        // Generar imagen con Replicate/Ideogram
        const output = await replicate.run(
          process.env.IDEOGRAM_MODEL || "ideogram-ai/ideogram-v3-turbo",
          {
            input: {
              prompt: imagePrompt,
              aspect_ratio: "9:16",
              width: parseInt(process.env.IMAGE_WIDTH) || 1080,
              height: parseInt(process.env.IMAGE_HEIGHT) || 1920,
              style_type: mapStyleToIdeogram(style),
              magic_prompt_option: "ON"
            }
          }
        );

        // La respuesta de Ideogram viene como array
        const imageUrl = Array.isArray(output) ? output[0] : output;

        // Guardar pin en la base de datos
        const pinResult = await client.query(
          `INSERT INTO pins 
           (generation_id, user_id, image_url, pin_title, pin_description, hashtags)
           VALUES ($1, $2, $3, $4, $5, $6)
           RETURNING *`,
          [
            generationId,
            userId,
            imageUrl,
            pinterestTexts[i].title,
            pinterestTexts[i].description,
            pinterestTexts[i].hashtags
          ]
        );

        generatedPins.push({
          id: pinResult.rows[0].id,
          image_url: imageUrl,
          title: pinterestTexts[i].title,
          description: pinterestTexts[i].description,
          hashtags: pinterestTexts[i].hashtags
        });

      } catch (pinError) {
        console.error(`Error generating pin ${i + 1}:`, pinError);
        // Continuar con los demás pines si uno falla
      }
    }

    // Actualizar créditos usados
    await client.query(
      'UPDATE users SET credits_used = credits_used + $1 WHERE id = $2',
      [generatedPins.length, userId]
    );

    // Actualizar estado de la generación
    await client.query(
      `UPDATE pin_generations 
       SET status = 'completed', 
           generated_pins = $1,
           credits_used = $2,
           completed_at = CURRENT_TIMESTAMP
       WHERE id = $3`,
      [JSON.stringify(generatedPins), generatedPins.length, generationId]
    );

    // Confirmar transacción
    await client.query('COMMIT');

    // Registrar uso de API
    await logApiUsage(userId, '/pins/generate', 'POST', 200, generatedPins.length);

    res.json({
      success: true,
      generation_id: generationId,
      pins_generated: generatedPins.length,
      credits_used: generatedPins.length,
      remaining_credits: remainingCredits - generatedPins.length,
      pins: generatedPins
    });

  } catch (error) {
    await client.query('ROLLBACK');
    console.error('Generate pins error:', error);
    
    res.status(500).json({ 
      error: 'Failed to generate pins',
      message: error.message 
    });
  } finally {
    client.release();
  }
};

// Función auxiliar para generar textos optimizados con OpenAI
async function generatePinterestTexts(title, content, count) {
  try {
    const completion = await openai.chat.completions.create({
      model: "gpt-4-turbo-preview",
      messages: [
        {
          role: "system",
          content: `You are a Pinterest marketing expert. Generate ${count} different variations of titles, descriptions, and hashtags for Pinterest pins based on the given blog post. 
          
          Requirements:
          - Titles: Maximum 100 characters, catchy and engaging
          - Descriptions: Maximum 450 characters, include call-to-action
          - Hashtags: 5-10 relevant hashtags without # symbol
          - Each variation should be unique and optimized for Pinterest SEO
          
          Return as JSON array with format:
          [{"title": "...", "description": "...", "hashtags": ["hashtag1", "hashtag2", ...]}]`
        },
        {
          role: "user",
          content: `Blog Title: ${title}\n\nContent excerpt: ${content || title}`
        }
      ],
      temperature: 0.8,
      max_tokens: 2000,
      response_format: { type: "json_object" }
    });

    const response = JSON.parse(completion.choices[0].message.content);
    return response.variations || response;

  } catch (error) {
    console.error('OpenAI error:', error);
    // Fallback: generar variaciones simples sin AI
    return Array(count).fill(null).map((_, i) => ({
      title: `${title} ${i > 0 ? `- Part ${i + 1}` : ''}`.substring(0, 100),
      description: `Discover amazing insights about ${title}. Click to read more and transform your knowledge! 📌`.substring(0, 450),
      hashtags: ['pinterest', 'pinterestideas', 'pinterestinspired', 'blog', 'tips']
    }));
  }
}

// Función auxiliar para crear prompt de imagen
function createImagePrompt(title, domain, style) {
  const stylePrompts = {
    modern: "modern, clean, minimalist design with bold typography",
    vibrant: "vibrant colors, energetic, eye-catching design",
    elegant: "elegant, sophisticated, luxury aesthetic",
    playful: "fun, playful, colorful with creative elements",
    professional: "professional, corporate, business-oriented design"
  };

  return `Pinterest pin design for "${title}" | ${domain} | ${stylePrompts[style] || stylePrompts.modern} | 
          Vertical 9:16 format | Text overlay with readable typography | High quality | Pinterest optimized`;
}

// Mapear estilos a opciones de Ideogram
function mapStyleToIdeogram(style) {
  const styleMap = {
    modern: "REALISTIC",
    vibrant: "DESIGN",
    elegant: "REALISTIC", 
    playful: "ANIME",
    professional: "REALISTIC"
  };
  return styleMap[style] || "REALISTIC";
}

// Obtener historial de generaciones
exports.getGenerationHistory = async (req, res) => {
  try {
    const userId = req.user.id;
    const { limit = 20, offset = 0 } = req.query;

    const result = await db.query(
      `SELECT id, post_title, domain, pin_count, style, status, 
              credits_used, created_at, completed_at
       FROM pin_generations
       WHERE user_id = $1
       ORDER BY created_at DESC
       LIMIT $2 OFFSET $3`,
      [userId, limit, offset]
    );

    const countResult = await db.query(
      'SELECT COUNT(*) FROM pin_generations WHERE user_id = $1',
      [userId]
    );

    res.json({
      success: true,
      data: result.rows,
      total: parseInt(countResult.rows[0].count),
      limit: parseInt(limit),
      offset: parseInt(offset)
    });

  } catch (error) {
    console.error('Get history error:', error);
    res.status(500).json({ 
      error: 'Failed to fetch generation history' 
    });
  }
};

// Obtener detalles de una generación específica
exports.getGenerationDetails = async (req, res) => {
  try {
    const { id } = req.params;
    const userId = req.user.id;

    const result = await db.query(
      `SELECT pg.*, 
              json_agg(
                json_build_object(
                  'id', p.id,
                  'image_url', p.image_url,
                  'title', p.pin_title,
                  'description', p.pin_description,
                  'hashtags', p.hashtags
                )
              ) as pins
       FROM pin_generations pg
       LEFT JOIN pins p ON p.generation_id = pg.id
       WHERE pg.id = $1 AND pg.user_id = $2
       GROUP BY pg.id`,
      [id, userId]
    );

    if (result.rows.length === 0) {
      return res.status(404).json({ 
        error: 'Generation not found' 
      });
    }

    res.json({
      success: true,
      data: result.rows[0]
    });

  } catch (error) {
    console.error('Get generation details error:', error);
    res.status(500).json({ 
      error: 'Failed to fetch generation details' 
    });
  }
};

// Función auxiliar para registrar uso de API
async function logApiUsage(userId, endpoint, method, statusCode, creditsConsumed = 0) {
  try {
    await db.query(
      `INSERT INTO api_usage 
       (user_id, endpoint, method, status_code, credits_consumed)
       VALUES ($1, $2, $3, $4, $5)`,
      [userId, endpoint, method, statusCode, creditsConsumed]
    );
  } catch (error) {
    console.error('Error logging API usage:', error);
  }
}