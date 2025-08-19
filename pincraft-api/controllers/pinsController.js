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
    style: 'COLOR TONES ONLY: warm golden, terracotta, burgundy lighting. NO changes to food content.',
    typography: 'Bold serif font in warm golden (#D4A574) or burgundy (#C44536), elegant autumn feel'
  },
  fresh: {
    name: 'Fresh', 
    colors: ['#32CD32', '#98FB98', '#50C878', '#228B22'],
    description: 'Vibrant lime, mint, emerald, avocado greens',
    style: 'COLOR TONES ONLY: vibrant green, lime, mint lighting. NO changes to food content.',
    typography: 'Clean modern sans-serif in vibrant green (#32CD32) or white, fresh organic energy'
  },
  jungle: {
    name: 'Jungle',
    colors: ['#355E3B', '#808000', '#6B8E23', '#228B22'], 
    description: 'Deep jungle, olive, moss, pine greens',
    style: 'COLOR TONES ONLY: deep green, olive, moss lighting. NO changes to food content.',
    typography: 'Bold sans-serif in deep forest green (#355E3B) or cream white, natural rustic feel'
  },
  magic: {
    name: 'Magic',
    colors: ['#8A2BE2', '#E6E6FA', '#191970', '#FF69B4'],
    description: 'Mystical purple, lavender, deep blue, rose',
    style: 'COLOR TONES ONLY: purple, lavender, pink lighting. NO changes to food content.',
    typography: 'Elegant serif font in mystical purple (#8A2BE2) or white, dreamy magical atmosphere'
  },
  melon: {
    name: 'Melon',
    colors: ['#FF6B6B', '#FFA07A', '#32CD32', '#FFA500'],
    description: 'Coral pink, peach, lime green, orange color tones',
    style: 'COLOR TONES ONLY: coral pink, peach, orange lighting. NO fruit additions, NO melon ingredients, NO content changes.',
    typography: 'Playful rounded sans-serif in coral pink (#FF6B6B) or white, summery fresh vibe'
  },
  mosaic: {
    name: 'Mosaic', 
    colors: ['#FF1493', '#4169E1', '#40E0D0', '#FF8C00'],
    description: 'Artistic fuchsia, royal blue, turquoise, orange',
    style: 'COLOR TONES ONLY: fuchsia, blue, turquoise lighting. NO changes to food content.',
    typography: 'Creative display font in royal blue (#4169E1) or white, artistic vibrant energy'
  },
  pastel: {
    name: 'Pastel',
    colors: ['#FFB6C1', '#87CEEB', '#F5F5DC', '#DDA0DD'],
    description: 'Soft pink, sky blue, cream, light purple',
    style: 'COLOR TONES ONLY: soft pink, cream, light blue lighting. NO changes to food content.',
    typography: 'Soft script font in light purple (#DDA0DD) or charcoal, dreamy ethereal aesthetic'
  },
  ultramarine: {
    name: 'Ultramarine',
    colors: ['#000080', '#4169E1', '#87CEEB', '#48D1CC'],
    description: 'Navy, royal blue, sky blue, aquamarine',
    style: 'COLOR TONES ONLY: navy blue, royal blue, aqua lighting. NO changes to food content.',
    typography: 'Professional sans-serif in navy blue (#000080) or white, sophisticated ocean elegance'
  },
  // PALETAS TEMÁTICAS DE COMIDA
  pizza: {
    name: 'Pizza',
    colors: ['#C72C41', '#4CAF50', '#FFF8DC', '#8B4513'],
    description: 'Tomato red, basil green, mozzarella white, crust brown',
    style: 'COLOR TONES ONLY: Italian restaurant lighting - tomato red, basil green, creamy white, golden brown. NO changes to food content.',
    typography: 'Classic Italian serif font in tomato red (#C72C41) or cream white, authentic trattoria feel'
  },
  fastfood: {
    name: 'Fast Food',
    colors: ['#FFC107', '#DC143C', '#FFFFFF', '#8B4513'],
    description: 'McDonald\'s inspired - golden yellow, red, white, brown',
    style: 'COLOR TONES ONLY: Fast food restaurant lighting - bright yellow, vibrant red, clean white. High contrast, appetizing. NO changes to food content.',
    typography: 'Bold rounded sans-serif in golden yellow (#FFC107) or red (#DC143C), fun fast-food energy'
  },
  streetfood: {
    name: 'Street Food',
    colors: ['#FF6B35', '#004E64', '#25A18E', '#F7B801'],
    description: 'Urban vibrant - orange, teal, turquoise, golden',
    style: 'COLOR TONES ONLY: Street market lighting - warm orange, urban teal, golden highlights. Vibrant casual atmosphere. NO changes to food content.',
    typography: 'Urban graffiti-style font in bright orange (#FF6B35) or white, street market vibe'
  },
  sushi: {
    name: 'Sushi',
    colors: ['#2C3E50', '#E74C3C', '#ECEFF1', '#27AE60'],
    description: 'Japanese aesthetic - dark slate, salmon pink, rice white, wasabi green',
    style: 'COLOR TONES ONLY: Japanese restaurant lighting - minimalist dark slate, salmon pink, pure white, wasabi green accents. NO changes to food content.',
    typography: 'Clean minimalist sans-serif in dark slate (#2C3E50) or white, zen Japanese aesthetic'
  },
  cafe: {
    name: 'Café',
    colors: ['#6F4E37', '#C8AD88', '#FFF8DC', '#2F1B14'],
    description: 'Coffee shop - espresso brown, latte beige, cream, dark roast',
    style: 'COLOR TONES ONLY: Cozy cafe lighting - coffee browns, creamy beiges, warm atmosphere. NO changes to food content.',
    typography: 'Cozy script font in espresso brown (#6F4E37) or cream, warm coffee shop atmosphere'
  },
  healthy: {
    name: 'Healthy',
    colors: ['#8BC34A', '#4CAF50', '#CDDC39', '#FFC107'],
    description: 'Fresh & organic - light green, green, lime, honey',
    style: 'COLOR TONES ONLY: Health food lighting - fresh greens, organic tones, natural brightness. NO changes to food content.',
    typography: 'Natural sans-serif in fresh green (#4CAF50) or white, organic health-focused feel'
  },
  gourmet: {
    name: 'Gourmet',
    colors: ['#2C3E50', '#BDC3C7', '#D4AF37', '#FFFFFF'],
    description: 'Fine dining - charcoal, silver, gold, white',
    style: 'COLOR TONES ONLY: Fine dining lighting - elegant charcoal, metallic silver, gold accents, pristine white. Luxurious atmosphere. NO changes to food content.',
    typography: 'Luxury serif font in gold (#D4AF37) or charcoal (#2C3E50), high-end restaurant elegance'
  },
  bbq: {
    name: 'BBQ',
    colors: ['#8B2500', '#FF4500', '#FFD700', '#4B0000'],
    description: 'Grill & smoke - burnt sienna, fire orange, golden, dark red',
    style: 'COLOR TONES ONLY: BBQ grill lighting - smoky reds, fire orange, golden char marks. Rustic outdoor atmosphere. NO changes to food content.',
    typography: 'Rustic western font in fire orange (#FF4500) or cream, smoky BBQ character'
  },
  // PALETAS PROFESIONALES
  consultant: {
    name: 'Consultant',
    colors: ['#1E3A5F', '#4A90E2', '#F5F5F5', '#333333'],
    description: 'Professional consulting - navy, corporate blue, light gray, charcoal',
    style: 'COLOR TONES ONLY: Professional corporate lighting - trustworthy navy, clean blues, neutral grays. Business atmosphere. NO changes to content.',
    typography: 'Corporate sans-serif in navy blue (#1E3A5F) or white, professional business credibility'
  },
  tech: {
    name: 'Tech',
    colors: ['#00D4FF', '#7B2CBF', '#FF006E', '#1A1A2E'],
    description: 'Tech startup - electric blue, purple, magenta, dark',
    style: 'COLOR TONES ONLY: Tech modern lighting - electric blue, innovative purple, digital magenta, dark backgrounds. NO changes to content.',
    typography: 'Modern tech font in electric blue (#00D4FF) or white, futuristic innovation vibe'
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
        
        console.log(`🤖 Calling OpenAI for pin ${i + 1}...`);
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
        console.log(`✅ Pin ${i + 1} text generated:`, optimizedText.substring(0, 100) + '...');

        // EXTRAER TÍTULO ESPECÍFICO GENERADO POR OPENAI
        let pinterestTitle = title; // fallback
        const titleMatch = optimizedText.match(/Título Pinterest:\s*(.+)/i);
        if (titleMatch) {
          pinterestTitle = titleMatch[1].trim();
        }

        // Generar imagen con Ideogram
        console.log(`🎨 Generating image ${i + 1} with Ideogram...`);
        
        let imagePrompt;
        
        // USAR TÍTULO COMPLETO SIN EMOJIS PARA MEJOR FLUIDEZ
        let optimizedTitle = pinterestTitle;
        if (with_text && pinterestTitle) {
          // Eliminar solo emojis pero mantener texto completo
          optimizedTitle = pinterestTitle.replace(/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu, '').trim();
          
          // Limpiar espacios extra y signos redundantes
          optimizedTitle = optimizedTitle.replace(/\s+/g, ' ').replace(/[¡!]{2,}/g, '!').replace(/[¿?]{2,}/g, '?');
          
          // Solo limitar si es extremadamente largo (más de 80 caracteres)
          if (optimizedTitle.length > 80) {
            optimizedTitle = optimizedTitle.substring(0, 75).trim();
            const lastSpace = optimizedTitle.lastIndexOf(' ');
            if (lastSpace > 60) {
              optimizedTitle = optimizedTitle.substring(0, lastSpace);
            }
            // Agregar puntos suspensivos si se cortó
            if (optimizedTitle.length < pinterestTitle.replace(/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu, '').trim().length) {
              optimizedTitle += '...';
            }
          }
        }
        
        console.log(`🎯 Pin ${i + 1} title optimization: "${pinterestTitle}" → "${optimizedTitle}"`);
        
        // Declarar variables comunes una sola vez
        const textOverlay = with_text ? 
          `Typography overlay with ONLY these exact words: "${optimizedTitle}". ${typographyStyle}. Large readable text, perfect contrast, no additional text, no paragraphs, no descriptions` : 
          'NO text overlay, purely visual composition, no words or letters visible';
        
        const domainWatermark = show_domain && with_text ? 
          `Subtle elegant watermark "${domain}" at bottom corner, minimalist design` : 
          'No watermark or domain text visible';
        
        // Aplicar paleta de colores y tipografía
        const selectedPalette = COLOR_PALETTES[color_palette] || COLOR_PALETTES.auto;
        const colorStyle = selectedPalette.style;
        const typographyStyle = selectedPalette.typography || 'Clean readable font in contrasting color';
        
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
          
          // Variables ya declaradas arriba
          
          imagePrompt = `Pinterest vertical pin 1000x2000px for ${sectorConfig.name}.
                        ${currentTemplate}.
                        ${colorStyle}.
                        ${sectorConfig.ideogram.base}.
                        ${sectorConfig.ideogram.elements}.
                        ${textOverlay}
                        ${domainWatermark}
                        CRITICAL: Text overlay must be EXACTLY as specified, no additional words, no creative variations, no extra descriptions.
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
          
          // Variables ya declaradas arriba
          
          imagePrompt = `Pinterest vertical pin 1000x2000px, professional quality.
                        ${currentVariation}.
                        ${colorStyle}.
                        ${textOverlay}
                        ${domainWatermark}
                        CRITICAL: If text overlay requested, use ONLY the exact words specified, no additional text, no creative additions.
                        Pinterest-trending composition, viral potential, scroll-stopping appeal.`;
        }

        console.log(`🎨 Calling Ideogram for pin ${i + 1} with prompt:`, imagePrompt.substring(0, 200) + '...');
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
        console.log(`🎨 Ideogram responded for pin ${i + 1}:`, output);

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
        console.error(`❌ Error stack:`, pinError.stack);
        console.error(`❌ Error details:`, JSON.stringify(pinError, null, 2));
        // Continuar con los demás pines si uno falla pero mostrar error detallado
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