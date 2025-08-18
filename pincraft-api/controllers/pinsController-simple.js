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

// Versión simplificada para testing
exports.generatePins = async (req, res) => {
  try {
    console.log('🎨 Starting pin generation...');
    console.log('📝 Request body:', req.body);
    console.log('👤 User:', req.user);

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

    console.log('✅ Basic validation passed');

    // Verificar créditos disponibles (simplificado)
    console.log('🔍 Checking user credits...');
    
    // Generar texto optimizado para Pinterest
    console.log('📝 Generating Pinterest text...');
    console.log('🎯 Selected sector:', sector);
    
    // Usar configuración del sector si está especificado
    const sectorConfig = sector && SECTORS[sector] ? SECTORS[sector] : null;
    
    let systemPrompt, userPrompt;
    
    if (sectorConfig) {
      // Usar prompts específicos del sector
      systemPrompt = `${sectorConfig.openai.system} 
                     Crea contenido con tono: ${sectorConfig.openai.tone}.
                     Usa estas palabras clave cuando sea relevante: ${sectorConfig.openai.keywords.join(', ')}.`;
      
      userPrompt = `Crea un pin optimizado para el sector "${sectorConfig.name}":
                   
                   Título del post: ${title}
                   Contenido: ${content}
                   Dominio: ${show_domain ? domain : '[sin dominio]'}
                   
                   Genera:
                   Título Pinterest: [máximo 100 caracteres, optimizado para ${sectorConfig.name}]
                   Descripción: [máximo 500 caracteres, incluye estos hashtags: ${sectorConfig.hashtags.join(' ')}]
                   Call-to-action: [frase motivadora relacionada con ${sectorConfig.name}]`;
    } else {
      // Prompts genéricos con detección automática
      systemPrompt = `Eres un experto en marketing de Pinterest. Tu trabajo es:
                     1. DETECTAR automáticamente el tipo de contenido
                     2. ADAPTAR el estilo según el tipo detectado
                     3. CREAR títulos y descripciones optimizados`;
      
      userPrompt = `Analiza este contenido y crea un pin optimizado:
                   
                   Título: ${title}
                   Contenido: ${content}
                   Dominio: ${show_domain ? domain : '[sin dominio]'}
                   
                   Genera:
                   Tipo detectado: [tipo de contenido]
                   Título Pinterest: [máximo 100 caracteres]
                   Descripción: [máximo 500 caracteres con hashtags]`;
    }
    
    const completion = await openai.chat.completions.create({
      model: "gpt-3.5-turbo",
      messages: [
        { role: "system", content: systemPrompt },
        { role: "user", content: userPrompt }
      ],
      max_tokens: 400
    });

    const optimizedText = completion.choices[0].message.content;
    console.log('✅ OpenAI text generated:', optimizedText);

    // Generar imagen con Ideogram
    console.log('🎨 Generating image with Ideogram...');
    console.log('🔤 With text overlay:', with_text);
    console.log('🌐 Show domain:', show_domain);
    
    let imagePrompt;
    
    if (sectorConfig) {
      // Usar configuración específica del sector
      const textOverlay = with_text ? 
        `Text overlay with "${title}". Readable typography, clear hierarchy.` : 
        'NO text overlay, purely visual design, no words or letters.';
      
      const domainWatermark = show_domain && with_text ? 
        `Small watermark with "${domain}" at bottom.` : 
        'No watermark or domain visible.';
      
      imagePrompt = `Pinterest pin for ${sectorConfig.name}. 
                    ${sectorConfig.ideogram.base}. 
                    ${sectorConfig.ideogram.style}. 
                    ${sectorConfig.ideogram.elements}.
                    ${textOverlay}
                    ${domainWatermark}
                    Vertical 9:16 format optimized for Pinterest.`;
    } else {
      // Prompt genérico adaptativo
      const textOverlay = with_text ? 
        `Text overlay with title "${title}". Clear, readable typography.` : 
        'NO text, purely visual imagery, no words.';
      
      const domainWatermark = show_domain && with_text ? 
        `Include small "${domain}" watermark.` : 
        '';
      
      imagePrompt = `Beautiful Pinterest pin design. 
                    ${textOverlay}
                    ${domainWatermark}
                    Modern, eye-catching, vertical 9:16 layout. 
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
    console.log('✅ Image generated:', imageUrl);

    // Respuesta simplificada
    const response = {
      success: true,
      data: {
        pins: [{
          image_url: imageUrl,
          optimized_text: optimizedText,
          original_title: title,
          domain: domain
        }],
        credits_used: 1,
        message: `¡Pin generado exitosamente! Imagen: ${imageUrl ? 'generada' : 'error'}`
      }
    };

    console.log('🎉 Pin generation completed successfully!');
    res.json(response);

  } catch (error) {
    console.error('❌ Error in pin generation:', error);
    res.status(500).json({ 
      error: 'Failed to generate pin',
      details: error.message 
    });
  }
};

// Funciones auxiliares simples
exports.getGenerationHistory = async (req, res) => {
  res.json({ success: true, data: { history: [] } });
};

exports.getGenerationDetails = async (req, res) => {
  res.json({ success: true, data: { details: {} } });
};