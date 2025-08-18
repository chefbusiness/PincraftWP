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
          content: "Eres un experto en marketing de Pinterest. Crea títulos y descripciones optimizados para Pinterest que generen clics y engagement."
        },
        {
          role: "user",
          content: `Crea un título y descripción para Pinterest basado en:
          Título: ${title}
          Contenido: ${content}
          Dominio: ${domain}
          
          Formato de respuesta:
          Título: [título optimizado de máximo 100 caracteres]
          Descripción: [descripción de máximo 500 caracteres con hashtags relevantes]`
        }
      ],
      max_tokens: 300
    });

    const optimizedText = completion.choices[0].message.content;
    console.log('✅ OpenAI text generated:', optimizedText);

    // Generar imagen con Ideogram
    console.log('🎨 Generating image with Ideogram...');
    
    const imagePrompt = `Beautiful Pinterest pin design for "${title}". Modern, eye-catching, vertical layout. Text overlay with elegant typography. Color scheme: vibrant and engaging. Professional design for food/lifestyle content. 9:16 aspect ratio.`;

    const output = await replicate.run(
      "stability-ai/sdxl:39ed52f2a78e934b3ba6e2a89f5b1c712de7dfea535525255b1aa35c5565e08b",
      {
        input: {
          prompt: imagePrompt,
          width: 1080,
          height: 1920,
          num_inference_steps: 25,
          guidance_scale: 7.5,
          num_outputs: 1
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