const Replicate = require('replicate');
const OpenAI = require('openai');
const db = require('../config/database');
const { SECTORS } = require('../config/sectors');

// Inicializar APIs
const replicate = new Replicate({
  auth: process.env.REPLICATE_API_TOKEN,
});

const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

// Generar pines para Pinterest
exports.generatePins = async (req, res) => {
  try {
    console.log('🎨 Starting pin generation...');
    console.log('📝 Request body:', req.body);

    const { 
      title, 
      content = '', 
      domain, 
      count = 1,
      style = 'modern',
      sector = null,
      show_domain = true,
      with_text = true
    } = req.body;

    const userId = req.user.id;

    // Validar parámetros básicos
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

    console.log('✅ Basic validation passed');
    console.log(`🔢 Generating ${count} pins`);

    // Verificar créditos disponibles
    const userResult = await db.query(
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

    // Array para almacenar los pines generados
    const generatedPins = [];
    const sectorConfig = sector && SECTORS[sector] ? SECTORS[sector] : null;

    console.log('🎯 Selected sector:', sector);
    console.log('⚙️ Sector config:', sectorConfig ? 'Found' : 'Using generic');

    // Generar cada pin
    for (let i = 0; i < count; i++) {
      try {
        console.log(`🎨 Generating pin ${i + 1}/${count}...`);

        // Generar texto optimizado para Pinterest
        let systemPrompt, userPrompt;
        
        if (sectorConfig) {
          // Usar prompts específicos del sector
          systemPrompt = `${sectorConfig.openai.system} 
                         Crea contenido con tono: ${sectorConfig.openai.tone}.
                         Usa estas palabras clave cuando sea relevante: ${sectorConfig.openai.keywords.join(', ')}.
                         Variación ${i + 1} de ${count} - haz cada pin único y diferente.`;
          
          userPrompt = `Crea un pin optimizado para el sector "${sectorConfig.name}" (VARIACIÓN ${i + 1}):
                       
                       Título del post: ${title}
                       Contenido: ${content}
                       Dominio: ${show_domain ? domain : '[sin dominio]'}
                       
                       Genera:
                       Título Pinterest: [máximo 100 caracteres, único para variación ${i + 1}]
                       Descripción: [máximo 500 caracteres, incluye hashtags: ${sectorConfig.hashtags.join(' ')}]
                       Call-to-action: [frase motivadora relacionada con ${sectorConfig.name}]`;
        } else {
          // Prompts genéricos con variaciones
          systemPrompt = `Eres un experto en marketing de Pinterest. Tu trabajo es:
                         1. DETECTAR automáticamente el tipo de contenido
                         2. ADAPTAR el estilo según el tipo detectado
                         3. CREAR títulos y descripciones optimizados
                         4. GENERAR variación ${i + 1} de ${count} - cada pin debe ser único`;
          
          userPrompt = `Analiza este contenido y crea un pin optimizado (VARIACIÓN ${i + 1}):
                       
                       Título: ${title}
                       Contenido: ${content}
                       Dominio: ${show_domain ? domain : '[sin dominio]'}
                       
                       Genera:
                       Tipo detectado: [tipo de contenido]
                       Título Pinterest: [máximo 100 caracteres, único para variación ${i + 1}]
                       Descripción: [máximo 500 caracteres con hashtags únicos]`;
        }
        
        const completion = await openai.chat.completions.create({
          model: "gpt-4o-mini",
          messages: [
            { 
              role: "system", 
              content: `${systemPrompt}
              
              FORMATO DE RESPUESTA OBLIGATORIO:
              Título Pinterest: [título optimizado de máximo 100 caracteres]
              Descripción: [descripción optimizada de máximo 500 caracteres con hashtags relevantes]
              Call-to-action: [frase motivadora de máximo 80 caracteres]
              
              REGLAS IMPORTANTES:
              - El título debe ser llamativo y clickeable
              - La descripción debe incluir hashtags relevantes (#palabra)  
              - Usar emojis estratégicamente para aumentar engagement
              - El contenido debe generar curiosidad y ganas de hacer clic
              - Adaptar el tono al sector/nicho específico`
            },
            { role: "user", content: userPrompt }
          ],
          max_tokens: 600,
          temperature: 0.7 + (i * 0.1) // Aumentar variabilidad en cada pin
        });

        const optimizedText = completion.choices[0].message.content;
        console.log(`✅ Pin ${i + 1} text generated`);

        // Generar imagen con Ideogram
        console.log(`🎨 Generating image ${i + 1} with Ideogram...`);
        
        let imagePrompt;
        
        if (sectorConfig) {
          // Usar configuración específica del sector con variaciones
          const variations = [
            'bright and colorful style',
            'elegant and minimalist approach', 
            'bold and energetic design',
            'soft and inspiring aesthetic',
            'modern and professional look'
          ];
          
          const currentVariation = variations[i % variations.length];
          
          const textOverlay = with_text ? 
            `Text overlay with "${title}" - variation ${i + 1}. Readable typography, clear hierarchy.` : 
            'NO text overlay, purely visual design, no words or letters.';
          
          const domainWatermark = show_domain && with_text ? 
            `Small watermark with "${domain}" at bottom.` : 
            'No watermark or domain visible.';
          
          imagePrompt = `Pinterest pin for ${sectorConfig.name} - Variation ${i + 1}. 
                        ${sectorConfig.ideogram.base}. 
                        ${currentVariation}.
                        ${sectorConfig.ideogram.elements}.
                        ${textOverlay}
                        ${domainWatermark}
                        Vertical 9:16 format optimized for Pinterest.`;
        } else {
          // Prompt genérico adaptativo con variaciones
          const variations = [
            'vibrant and engaging',
            'clean and professional', 
            'creative and artistic',
            'warm and inviting',
            'bold and striking'
          ];
          
          const currentVariation = variations[i % variations.length];
          
          const textOverlay = with_text ? 
            `Text overlay with title "${title}". Clear, readable typography - ${currentVariation} style.` : 
            'NO text, purely visual imagery, no words.';
          
          const domainWatermark = show_domain && with_text ? 
            `Include small "${domain}" watermark.` : 
            '';
          
          imagePrompt = `Beautiful Pinterest pin design - Variation ${i + 1}. 
                        ${textOverlay}
                        ${domainWatermark}
                        ${currentVariation}, vertical 9:16 layout. 
                        Professional design optimized for Pinterest engagement.`;
        }

        const output = await replicate.run(
          "ideogram-ai/ideogram-v3-turbo:32a9584617b239dd119c773c8c18298d310068863d26499e6199538e9c29a586",
          {
            input: {
              prompt: imagePrompt,
              aspect_ratio: "9:16",
              style_type: "None",
              magic_prompt_option: "Auto"
            }
          }
        );

        const imageUrl = Array.isArray(output) ? output[0] : output;
        console.log(`✅ Pin ${i + 1} image generated:`, imageUrl);

        generatedPins.push({
          image_url: imageUrl,
          optimized_text: optimizedText,
          original_title: title,
          domain: domain,
          variation: i + 1
        });

      } catch (pinError) {
        console.error(`❌ Error generating pin ${i + 1}:`, pinError);
        // Continuar con los demás pines si uno falla
      }
    }

    // Actualizar créditos usados
    await db.query(
      'UPDATE users SET credits_used = credits_used + $1 WHERE id = $2',
      [generatedPins.length, userId]
    );

    // Respuesta final
    const response = {
      success: true,
      data: {
        pins: generatedPins,
        credits_used: generatedPins.length,
        message: `¡${generatedPins.length} ${generatedPins.length === 1 ? 'pin generado' : 'pines generados'} exitosamente!`
      }
    };

    console.log(`🎉 Pin generation completed! Generated ${generatedPins.length}/${count} pins`);
    res.json(response);

  } catch (error) {
    console.error('❌ Error in pin generation:', error);
    res.status(500).json({ 
      error: 'Failed to generate pins',
      details: error.message 
    });
  }
};

// Obtener historial de generaciones (simplificado)
exports.getGenerationHistory = async (req, res) => {
  try {
    const userId = req.user.id;
    
    // Por ahora retornamos historial vacío
    // TODO: Implementar historial real en base de datos
    res.json({
      success: true,
      data: {
        history: [],
        total: 0
      }
    });

  } catch (error) {
    console.error('Get history error:', error);
    res.status(500).json({ 
      error: 'Failed to fetch generation history' 
    });
  }
};

// Obtener detalles de una generación específica (simplificado)
exports.getGenerationDetails = async (req, res) => {
  try {
    res.json({
      success: true,
      data: {
        details: {}
      }
    });

  } catch (error) {
    console.error('Get generation details error:', error);
    res.status(500).json({ 
      error: 'Failed to fetch generation details' 
    });
  }
};