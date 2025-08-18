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

// SISTEMA DE PALETAS TRENDING (inspirado en Ideogram)
const COLOR_PALETTES = {
  ember: {
    name: 'Ember',
    colors: ['#C44536', '#D4A574', '#8B4513', '#CD853F'],
    description: 'Warm coral, terracotta, golden, burgundy tones',
    style: 'warm golden hour lighting, ember and terracotta color palette, cozy autumn vibes'
  },
  fresh: {
    name: 'Fresh', 
    colors: ['#32CD32', '#98FB98', '#50C878', '#228B22'],
    description: 'Vibrant lime, mint, emerald, avocado greens',
    style: 'fresh vibrant green palette, spring energy, natural organic aesthetic'
  },
  jungle: {
    name: 'Jungle',
    colors: ['#355E3B', '#808000', '#6B8E23', '#228B22'], 
    description: 'Deep jungle, olive, moss, pine greens',
    style: 'deep jungle green palette, lush forest aesthetic, rich earth tones'
  },
  magic: {
    name: 'Magic',
    colors: ['#8A2BE2', '#E6E6FA', '#191970', '#FF69B4'],
    description: 'Mystical purple, lavender, deep blue, rose',
    style: 'mystical purple and lavender palette, magical dreamy atmosphere'
  },
  melon: {
    name: 'Melon',
    colors: ['#FF6B6B', '#FFA07A', '#32CD32', '#FFA500'],
    description: 'Watermelon, peach, lime, orange fruits',
    style: 'fresh fruit color palette, summery melon and citrus tones'
  },
  mosaic: {
    name: 'Mosaic', 
    colors: ['#FF1493', '#4169E1', '#40E0D0', '#FF8C00'],
    description: 'Artistic fuchsia, royal blue, turquoise, orange',
    style: 'bold artistic color palette, vibrant mosaic-inspired tones'
  },
  pastel: {
    name: 'Pastel',
    colors: ['#FFB6C1', '#87CEEB', '#F5F5DC', '#DDA0DD'],
    description: 'Soft pink, sky blue, cream, light purple',
    style: 'soft pastel color palette, dreamy ethereal aesthetic'
  },
  ultramarine: {
    name: 'Ultramarine',
    colors: ['#000080', '#4169E1', '#87CEEB', '#48D1CC'],
    description: 'Navy, royal blue, sky blue, aquamarine',
    style: 'sophisticated blue color palette, ocean-inspired tones'
  },
  auto: {
    name: 'Auto',
    colors: [],
    description: 'AI-detected colors based on content',
    style: 'color palette automatically selected based on content analysis'
  }
};

// Generar pines para Pinterest
exports.generatePins = async (req, res) => {
  try {
    console.log('🎨 Starting pin generation...');
    console.log('📝 Request body:', req.body);

    const { 
      title, 
      content = '', 
      domain,
      color_palette = 'auto', 
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
          // PROMPTS PROFESIONALES basados en análisis Pinterest
          const professionalTemplates = {
            'Recetas y Comida': [
              'Ultra-realistic Pinterest food photography, cinematic lighting, shallow depth of field, rustic wooden surface with warm golden hour lighting',
              'Mouthwatering food collage, magazine-style layout, multiple angles, rich saturated colors, restaurant-quality presentation',
              'Artistic food photography, moody editorial style, dramatic shadows, minimalist composition with negative space'
            ],
            'Decoración del Hogar y DIY': [
              'Clean modern Pinterest home inspiration, bright airy photography, natural lighting, Scandinavian-minimalist aesthetic',
              'Pinterest DIY transformation, dramatic before/after visual, eye-catching textures, Instagram-worthy styling',
              'Cozy home aesthetic, warm lighting, layered textures, lived-in comfort with design sophistication'
            ],
            'Moda Femenina': [
              'High-fashion Pinterest style, editorial photography, perfect lighting, minimalist background, model confidence',
              'Street style inspiration, urban backdrop, natural poses, trendy outfit coordination, lifestyle context',
              'Wardrobe essentials flatlay, organized styling, neutral background, magazine-quality composition'
            ]
          };
          
          const templates = professionalTemplates[sectorConfig.name] || [
            'Professional Pinterest aesthetic, high-quality photography, perfect lighting and composition',
            'Editorial style visual, clean modern design, sophisticated color palette',
            'Inspiring lifestyle photography, aspirational yet achievable, premium quality'
          ];
          
          const currentTemplate = templates[i % templates.length];
          
          const textOverlay = with_text ? 
            `Bold typography overlay "${title}", elegant serif or modern sans-serif font, golden/cream color palette, perfect readability` : 
            'NO text overlay, purely visual composition, no words or letters visible';
          
          const domainWatermark = show_domain && with_text ? 
            `Subtle elegant watermark "${domain}" at bottom corner, minimalist design` : 
            'No watermark or domain text visible';
          
          // Aplicar paleta de colores
          const selectedPalette = COLOR_PALETTES[color_palette] || COLOR_PALETTES.auto;
          const colorStyle = selectedPalette.style;
          
          imagePrompt = `Pinterest vertical pin 1000x2000px for ${sectorConfig.name}.
                        ${currentTemplate}.
                        ${colorStyle}.
                        ${sectorConfig.ideogram.base}.
                        ${sectorConfig.ideogram.elements}.
                        ${textOverlay}
                        ${domainWatermark}
                        Pinterest-optimized composition, trending aesthetic, scroll-stopping quality.`;
        } else {
          // PROMPTS GENÉRICOS PROFESIONALES
          const professionalVariations = [
            'Ultra-professional Pinterest aesthetic, high-end photography, cinematic lighting, magazine-quality composition',
            'Clean modern editorial style, minimalist sophistication, premium visual appeal, trending Pinterest aesthetic', 
            'Creative artistic approach, dramatic lighting, sophisticated color palette, Instagram-worthy composition',
            'Warm inviting lifestyle photography, golden hour lighting, aspirational yet approachable aesthetic',
            'Bold striking visual impact, high contrast, attention-grabbing composition, viral Pinterest potential'
          ];
          
          const currentVariation = professionalVariations[i % professionalVariations.length];
          
          const textOverlay = with_text ? 
            `Professional typography overlay "${title}", trending Pinterest font styles, perfect hierarchy and readability` : 
            'NO text overlay, purely visual composition, no words or letters visible';
          
          const domainWatermark = show_domain && with_text ? 
            `Subtle elegant watermark "${domain}" at bottom corner` : 
            'No watermark visible';
          
          // Aplicar paleta de colores
          const selectedPalette = COLOR_PALETTES[color_palette] || COLOR_PALETTES.auto;
          const colorStyle = selectedPalette.style;
          
          imagePrompt = `Pinterest vertical pin 1000x2000px, professional quality.
                        ${currentVariation}.
                        ${colorStyle}.
                        ${textOverlay}
                        ${domainWatermark}
                        Pinterest-trending composition, viral potential, scroll-stopping appeal.`;
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