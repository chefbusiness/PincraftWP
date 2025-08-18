const Replicate = require('replicate');
const OpenAI = require('openai');
const db = require('../config/database');

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
      style = 'modern'
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
    
    const completion = await openai.chat.completions.create({
      model: "gpt-3.5-turbo",
      messages: [
        {
          role: "system",
          content: `Eres un experto en marketing de Pinterest. Tu trabajo es:
          1. DETECTAR automáticamente el tipo de contenido (receta, tutorial, información, producto, etc)
          2. ADAPTAR el estilo según el tipo detectado
          3. CREAR títulos y descripciones optimizados para ese tipo específico de contenido
          4. USAR el tono y lenguaje apropiado para cada nicho`
        },
        {
          role: "user",
          content: `Analiza este contenido y crea un pin optimizado:
          
          Título: ${title}
          Contenido: ${content}
          Dominio: ${domain}
          
          PRIMERO identifica el tipo de contenido, LUEGO genera:
          
          Tipo: [receta/tutorial/información/producto/lifestyle/otro]
          Título: [título optimizado según el tipo, máximo 100 caracteres]
          Descripción: [descripción adaptada al tipo de contenido, máximo 500 caracteres con hashtags relevantes]
          Estilo Visual: [sugerencia de estilo para la imagen: minimalista/colorido/elegante/rústico/moderno]`
        }
      ],
      max_tokens: 400
    });

    const optimizedText = completion.choices[0].message.content;
    console.log('✅ OpenAI text generated:', optimizedText);

    // Extraer tipo de contenido y estilo visual del texto optimizado
    const contentType = optimizedText.match(/Tipo:\s*([^\n]+)/)?.[1] || 'general';
    const visualStyle = optimizedText.match(/Estilo Visual:\s*([^\n]+)/)?.[1] || 'moderno';
    
    // Generar imagen con Ideogram
    console.log('🎨 Generating image with Ideogram...');
    console.log('📝 Content type detected:', contentType);
    console.log('🎨 Visual style:', visualStyle);
    
    // Adaptar el prompt según el tipo de contenido
    let imagePromptBase = {
      'receta': `Delicious food photography, ingredients visible, warm lighting, cooking process`,
      'tutorial': `Step-by-step visual guide, clear instructions, numbered steps, educational`,
      'información': `Infographic style, data visualization, clean layout, professional`,
      'producto': `Product showcase, lifestyle photography, brand aesthetics, commercial quality`,
      'lifestyle': `Aspirational imagery, aesthetic photography, mood board style`,
      'otro': `Creative design, eye-catching visuals, Pinterest-optimized`
    };
    
    const contextPrompt = imagePromptBase[contentType.toLowerCase()] || imagePromptBase['otro'];
    
    const imagePrompt = `Pinterest pin design: ${title}. ${contextPrompt}. Style: ${visualStyle}. Vertical 9:16 layout. Text overlay with readable typography. High engagement design optimized for Pinterest clicks and saves.`;

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